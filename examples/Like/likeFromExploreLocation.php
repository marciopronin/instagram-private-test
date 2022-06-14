<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../../vendor/autoload.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////

//////////////////////
$queryLocation = 'Paris'; // :)
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    // Search/explore session, will be used for the Graph API events.
    $searchSession = \InstagramAPI\Signatures::generateUUID();

    $topicData =
    [
        'topic_cluster_title'       => 'For You',
        'topic_cluster_id'          => 'explore_all:0',
        'topic_cluster_type'        => 'explore_all',
        'topic_cluster_session_id'  => $searchSession,
        'topic_nav_order'           => 0,
    ];

    // Send navigation from 'feed_timeline' to 'explore_popular'.
    $ig->event->sendNavigation('main_search', 'feed_timeline', 'explore_popular', null, null, $topicData);

    // Send navigation from 'explore_popular' to 'explore_popular'.
    $ig->event->sendNavigation('explore_topic_load', 'explore_popular', 'explore_popular', null, null, $topicData);

    // Get explore feed sections and items.
    $sectionalItems = $ig->discover->getExploreFeed('explore_all:0', $searchSession)->getSectionalItems();

    $ig->event->prepareAndSendExploreImpression('explore_all:0', $searchSession, $sectionalItems);

    // Get suggested searches and recommendations from Instagram.
    $ig->event->sendNavigation('button', 'explore_popular', 'blended_search');

    $ig->discover->getNullStateDynamicSections();

    $hashtagId = null;

    // Search query and parse results.
    $searchResponse = $ig->discover->search($queryLocation);
    $ig->event->sendNavigation('button', 'blended_search', 'search_places');

    $searchResults = $searchResponse->getList();

    $rankToken = $searchResponse->getRankToken();
    $resultList = [];
    $resultTypeList = [];
    $position = 0;
    $found = false;

    // We are now classifying each result into a hashtag or user result.
    foreach ($searchResults as $searchResult) {
        if ($searchResult->getHashtag() !== null) {
            $resultList[] = $searchResult->getHashtag()->getId();
            // We will save the data when the result matches our query.
            // Hashtag ID is required in the next steps for Graph API and
            // like().
            if ($searchResult->getHashtag()->getName() === $queryLocation) {
                $hashtagId = $searchResult->getHashtag()->getId();
                // This request tells Instagram that we have clicked in this specific hashtag.
                $ig->discover->registerRecentSearchClick('hashtag', $hashtagId);
                // When this flag is set to true, position won't increment
                // anymore. We are using this to track the result position.
                $found = true;
            }
            $resultTypeList[] = 'HASHTAG';
        } elseif ($searchResult->getPlace() !== null) {
            $resultList[] = $searchResult->getPlace()->getPk();
            // We will save the data when the result matches our query.
            // Hashtag ID is required in the next steps for Graph API and
            // like().
            if ($searchResult->getPlace()->getName() === $queryLocation) {
                $locationId = $searchResult->getPlace()->getPk();
                // This request tells Instagram that we have clicked in this specific hashtag.
                $ig->discover->registerRecentSearchClick('place', $locationId);
                // When this flag is set to true, position won't increment
                // anymore. We are using this to track the result position.
                $found = true;
            }
            $resultTypeList[] = 'PLACE';
        } else {
            $resultList[] = $searchResult->getUser()->getPk();
            $resultTypeList[] = 'USER';
        }
        if ($found !== true) {
            $position++;
        }
    }

    // Send restults from search.
    $ig->event->sendSearchResults($queryLocation, $resultList, $resultTypeList, $rankToken, $searchSession, 'blended_search');
    $ig->event->sendSearchResults($queryLocation, $resultList, $resultTypeList, $rankToken, $searchSession, 'search_places');
    // Send selected result from results.
    $ig->event->sendSearchResultsPage($queryLocation, $locationId, $resultList, $resultTypeList, $rankToken, $searchSession, $position, 'PLACE', 'search_places');

    $ig->discover->registerRecentSearchClick('place', $locationId);

    $ig->event->sendNavigation('search_result', 'search_places', 'feed_location', $locationId, $queryLocation,
        [
            'query_text'        => $queryLocation,
            'search_session_id' => $searchSession,
        ]
    );

    // Generate a random rank token.
    $rankToken = \InstagramAPI\Signatures::generateUUID();
    // Get sections and items.
    $sectionResponse = $ig->location->getFeed($queryLocation, $rankToken);
    $persistentSections = $sectionResponse->getPersistentSections();

    foreach ($persistentSections as $persistentSection) {
        if ($persistentSection->getFeedType() === 'suggested_places') {
            $relatedPlaces = $persistentSection->getLayoutContent()->getRelated();
            foreach ($relatedPlaces as $relatedPlace) {
                // TODO: SEND RELATED PLACES EVENT
            }
            break;
        }
    }

    $sections = $sectionResponse->getSections();
    // These requests are also sent to emulate app behaviour.
    $ig->location->getInfo($queryLocation);
    $ig->location->getStory($queryLocation);

    $item = null;
    // For each item in the place feed, we will send a thumbnail impression.
    foreach ($sections as $section) {
        if ($section->getLayoutType() === 'media_grid') {
            if ($item === null) {
                // We are going to like the first item of the hashtag feed, so we just save this one.
                $item = $section->getLayoutContent()->getMedias()[0]->getMedia();
            }
            foreach ($section->getLayoutContent()->getMedias() as $media) {
                if ($media->getMedia()->getMediaType() === 1) {
                    $candidates = $media->getMedia()->getImageVersions2()->getCandidates();
                    $smallCandidate = end($candidates);
                    $ig->request($smallCandidate->getUrl())->getRawResponse();
                    $ig->event->sendPerfPercentPhotosRendered('feed_location', $media->getMedia()->getId(), [
                        'is_grid_view'                      => true,
                        'image_heigth'                      => $smallCandidate->getHeight(),
                        'image_width'                       => $smallCandidate->getWidth(),
                        'load_time'                         => $ig->client->bandwidthM,
                        'estimated_bandwidth'               => $ig->client->bandwidthB,
                        'estimated_bandwidth_totalBytes_b'  => $ig->client->totalBytes,
                        'estimated_bandwidth_totalTime_ms'  => $ig->client->totalTime,
                    ]);
                    $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $media->getMedia(), 'feed_location', $locationId, $queryLocation);
                }
            }
        }
    }

    // Send thumbnail click impression (clickling on the selected media).
    $ig->event->sendThumbnailImpression('instagram_thumbnail_click', $item, 'feed_location', $locationId, $queryLocation);
    // When we clicked the item, we are navigating from 'feed_hashtag' to 'feed_contextual_hashtag'.
    $ig->event->sendNavigation('button', 'feed_location', 'feed_contextual_location', $locationId, $queryLocation);

    $commentInfos = $ig->media->getCommentInfos($item->getId())->getCommentInfos()->getData();

    $ig->event->sendOrganicNumberOfLikes($item, 'feed_contextual_location');

    foreach ($commentInfos as $key => $value) {
        $previewComments = $value->getPreviewComments();
        if ($previewComments !== null) {
            foreach ($previewComments as $comment) {
                $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
            }
        }
    }

    // Perform like.
    $ig->media->like($item->getId(), 0, 'feed_contextual_location', false, ['entity_page_id' => $locationId, 'entity_page_name' => $queryLocation]);
    // Send organic like.
    $ig->event->sendOrganicLike($item, 'feed_contextual_location', $locationId, $queryLocation, $ig->session_id);
    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
