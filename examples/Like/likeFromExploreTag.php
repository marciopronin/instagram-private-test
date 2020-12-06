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
$queryHashtag = 'dog'; // :)
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
    $ig->discover->getSuggestedSearches('blended');
    $ig->event->sendNavigation('button', 'explore_popular', 'search');
    $ig->event->sendNavigation('button', 'search', 'blended_search');
    $ig->discover->getNullStateDynamicSections();

    $hashtagId = null;

    // Search query and parse results.
    $searchResponse = $ig->discover->search($queryHashtag);
    $ig->event->sendNavigation('button', 'blended_search', 'search_tags');

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
            if ($searchResult->getHashtag()->getName() === $queryHashtag) {
                $hashtagId = $searchResult->getHashtag()->getId();
                // This request tells Instagram that we have clicked in this specific hashtag.
                $ig->discover->registerRecentSearchClick('hashtag', $hashtagId);
                // When this flag is set to true, position won't increment
                // anymore. We are using this to track the result position.
                $found = true;
            }
            $resultTypeList[] = 'HASHTAG';
        } else {
            $resultList[] = $searchResult->getUser()->getPk();
            $resultTypeList[] = 'USER';
        }
        if ($found !== true) {
            $position++;
        }
    }

    // Send restults from search.
    $ig->event->sendSearchResults($queryHashtag, $resultList, $resultTypeList, $rankToken, $searchSession, 'blended_search');
    $ig->event->sendSearchResults($queryHashtag, $resultList, $resultTypeList, $rankToken, $searchSession, 'search_tags');
    // Send selected result from results.
    $ig->event->sendSearchResultsPage($queryHashtag, $hashtagId, $resultList, $resultTypeList, $rankToken, $searchSession, $position, 'HASHTAG', 'search_tags');

    $ig->discover->registerRecentSearchClick('hashtag', $hashtagId);

    $ig->event->sendNavigation('search_result', 'search_tags', 'feed_hashtag', $hashtagId, $queryHashtag,
        [
            'query_text'        => $queryHashtag,
            'search_session_id' => $searchSession,
        ]
    );

    // Generate a random rank token.
    $rankToken = \InstagramAPI\Signatures::generateUUID();
    // Get sections and items.
    $sectionResponse = $ig->hashtag->getSection($queryHashtag, $rankToken);
    $persistentSections = $sectionResponse->getPersistentSections();

    foreach ($persistentSections as $persistentSection) {
        if ($persistentSection->getFeedType() === 'suggested_hashtags') {
            $relatedHashtags = $persistentSection->getLayoutContent()->getRelated();
            foreach ($relatedHashtags as $relatedHashtag) {
                $ig->event->sendRelatedHashtagItem($relatedHashtag->getName(), $relatedHashtag->getId(), $hashtagId, $queryHashtag);
            }
            break;
        }
    }

    $sections = $sectionResponse->getSections();
    // These requests are also sent to emulate app behaviour.
    $ig->hashtag->getInfo($queryHashtag);
    $ig->hashtag->getStory($queryHashtag);

    $item = null;
    // For each item in the hashtag feed, we will send a thumbnail impression.
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
                    $ig->event->sendPerfPercentPhotosRendered('feed_hashtag', $media->getMedia()->getId(), [
                        'is_grid_view'                      => true,
                        'image_heigth'                      => $smallCandidate->getHeight(),
                        'image_width'                       => $smallCandidate->getWidth(),
                        'load_time'                         => $ig->client->bandwidthM,
                        'estimated_bandwidth'               => $ig->client->bandwidthB,
                        'estimated_bandwidth_totalBytes_b'  => $ig->client->totalBytes,
                        'estimated_bandwidth_totalTime_ms'  => $ig->client->totalTime,
                    ]);
                    $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $media->getMedia(), 'feed_hashtag', $hashtagId, $queryHashtag);
                }
            }
        }
    }

    // Send thumbnail click impression (clickling on the selected media).
    $ig->event->sendThumbnailImpression('instagram_thumbnail_click', $item, 'feed_hashtag', $hashtagId, $queryHashtag);
    // When we clicked the item, we are navigating from 'feed_hashtag' to 'feed_contextual_hashtag'.
    $ig->event->sendNavigation('button', 'feed_hashtag', 'feed_contextual_hashtag', $hashtagId, $queryHashtag);

    $previewComments = $ig->media->getCommentInfos($item->getId())->getCommentInfos()->getData()->getPreviewComments();

    $ig->event->sendOrganicNumberOfLikes($item, 'feed_contextual_hashtag');

    if ($previewComments !== null) {
        foreach ($previewComments as $comment) {
            $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
        }
    }

    // Perform like.
    $ig->media->like($item->getId(), 0, 'feed_contextual_hashtag', false, ['hashtag_id' => $hashtagId, 'hashtag' => $queryHashtag]);
    // Send organic like.
    $ig->event->sendOrganicLike($item, 'feed_contextual_hashtag', $hashtagId, $queryHashtag, $ig->session_id);
    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
