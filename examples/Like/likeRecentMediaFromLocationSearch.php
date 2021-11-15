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
    $ig->discover->getSuggestedSearches('blended');
    $ig->event->sendNavigation('button', 'explore_popular', 'search');
    $ig->event->sendNavigation('button', 'search', 'blended_search');
    $ig->event->sendNavigation('button', 'blended_search', 'search_places');

    $locationItems = $ig->location->findPlaces($queryLocation)->getItems();
    $rankToken = \InstagramAPI\Signatures::generateUUID();

    $resultList = [];
    $resultTypeList = [];
    $position = 0;
    $found = false;
    $locationId = null;
    foreach ($locationItems as $locationItem) {
        $resultList[] = $locationItem->getLocation()->getFacebookPlacesId();
        $resultTypeList[] = 'PLACE';

        if ($locationItem->getTitle() === $queryLocation) {
            $found = true;
            $locationId = $locationItem->getLocation()->getFacebookPlacesId();
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

    $ig->event->sendNavigation('search_result', 'search_places', 'feed_location', null, null,
        [
            'rank_token'        => $rankToken,
            'query_text'        => $queryLocation,
            'search_session_id' => $searchSession,
            'search_tab'        => 'search_places',
            'selected_type'     => 'place',
            'position'          => $position,
            'entity_page_name'  => $queryLocation,
            'entity_page_id'    => $locationId,
        ]
    );

    // Generate a random rank token.
    $rankToken = \InstagramAPI\Signatures::generateUUID();
    // Get sections and items.
    $sectionResponse = $ig->location->getFeed($locationId, $rankToken);
    $sections = $sectionResponse->getSections();
    // These requests are also sent to emulate app behaviour.
    $ig->location->getInfo($locationId);
    $ig->location->getStoryFeed($locationId);

    foreach ($sections as $section) {
        if ($section->getLayoutType() === 'media_grid') {
            foreach ($section->getLayoutContent()->getMedias() as $media) {
                if ($media->getMedia()->getMediaType() === 1) {
                    $candidates = $media->getMedia()->getImageVersions2()->getCandidates();
                    $smallCandidate = end($candidates);

                    $imageResponse = $ig->request($smallCandidate->getUrl());

                    if (isset($imageResponse->getHttpResponse()->getHeaders()['x-encoded-content-length'])) {
                        $imageSize = $imageResponse->getHttpResponse()->getHeaders()['x-encoded-content-length'][0];
                    } elseif (isset($imageResponse->getHttpResponse()->getHeaders()['Content-Length'])) {
                        $imageSize = $imageResponse->getHttpResponse()->getHeaders()['Content-Length'][0];
                    } elseif (isset($imageResponse->getHttpResponse()->getHeaders()['content-length'])) {
                        $imageSize = $imageResponse->getHttpResponse()->getHeaders()['content-length'][0];
                    } else {
                        continue;
                    }

                    $ig->event->sendPerfPercentPhotosRendered('feed_location', $media->getMedia()->getId(), [
                        'is_grid_view'                      => true,
                        'image_heigth'                      => $smallCandidate->getHeight(),
                        'image_width'                       => $smallCandidate->getWidth(),
                        'load_time'                         => $ig->client->bandwidthM,
                        'image_size_kb'                     => $imageSize,
                        'estimated_bandwidth'               => $ig->client->bandwidthB,
                        'estimated_bandwidth_totalBytes_b'  => $ig->client->totalBytes,
                        'estimated_bandwidth_totalTime_ms'  => $ig->client->totalTime,
                    ]);
                    $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $media->getMedia(), 'feed_location');
                }
            }
        }
    }

    // Get sections and items (RECENT).
    $sectionResponse = $ig->location->getFeed($locationId, $rankToken, 'recent');
    $sections = $sectionResponse->getSections();
    // These requests are also sent to emulate app behaviour.
    $ig->location->getInfo($locationId);
    $ig->location->getStoryFeed($locationId);

    $items = [];
    foreach ($sections as $section) {
        if ($section->getLayoutType() === 'media_grid') {
            foreach ($section->getLayoutContent()->getMedias() as $media) {
                if ($media->getMedia()->getMediaType() === 1) {
                    $candidates = $media->getMedia()->getImageVersions2()->getCandidates();
                    $smallCandidate = end($candidates);

                    $imageResponse = $ig->request($smallCandidate->getUrl());

                    if (isset($imageResponse->getHttpResponse()->getHeaders()['x-encoded-content-length'])) {
                        $imageSize = $imageResponse->getHttpResponse()->getHeaders()['x-encoded-content-length'][0];
                    } elseif (isset($imageResponse->getHttpResponse()->getHeaders()['Content-Length'])) {
                        $imageSize = $imageResponse->getHttpResponse()->getHeaders()['Content-Length'][0];
                    } elseif (isset($imageResponse->getHttpResponse()->getHeaders()['content-length'])) {
                        $imageSize = $imageResponse->getHttpResponse()->getHeaders()['content-length'][0];
                    } else {
                        continue;
                    }

                    $ig->event->sendPerfPercentPhotosRendered('feed_location', $media->getMedia()->getId(), [
                        'is_grid_view'                      => true,
                        'image_heigth'                      => $smallCandidate->getHeight(),
                        'image_width'                       => $smallCandidate->getWidth(),
                        'load_time'                         => $ig->client->bandwidthM,
                        'image_size_kb'                     => $imageSize,
                        'estimated_bandwidth'               => $ig->client->bandwidthB,
                        'estimated_bandwidth_totalBytes_b'  => $ig->client->totalBytes,
                        'estimated_bandwidth_totalTime_ms'  => $ig->client->totalTime,
                    ]);
                    $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $media->getMedia(), 'feed_location');
                }
                $items[] = $media->getMedia();
            }
        }
    }

    $itemCounter = 0;
    for ($itemCounter; $itemCounter < 5; $itemCounter++) {
        // Send thumbnail click impression (clickling on the selected media).
        $ig->event->sendThumbnailImpression('instagram_thumbnail_click', $items[$itemCounter], 'feed_location');
        $ig->event->sendNavigation('button', 'feed_location', 'feed_contextual_location');

        $commentInfos = $ig->media->getCommentInfos($items[$itemCounter]->getId())->getCommentInfos()->getData();

        $ig->event->sendOrganicNumberOfLikes($items[$itemCounter], 'feed_contextual_location');

        foreach ($commentInfos as $key => $value) {
            $previewComments = $value->getPreviewComments();
            if ($previewComments !== null) {
                foreach ($previewComments as $comment) {
                    $ig->event->sendCommentImpression($items[$itemCounter], $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
                }
            }
        }

        // Perform like.
        $ig->media->like($items[$itemCounter]->getId(), 0, 'feed_contextual_location', false, ['entity_page_name' => $queryLocation, 'entity_page_id' => $locationId]);
        // Send organic like.
        $ig->event->sendOrganicLike($items[$itemCounter], 'feed_contextual_location', null, null, $ig->session_id);
        $ig->event->sendNavigation('back', 'feed_contextual_location', 'feed_location', null, null,
            ['entity_page_name' => $queryLocation,
            'entity_page_id'    => $locationId,
            'search_session_id' => $rankToken,
            'query_text'        => $queryLocation,
            'selected_type'     => 'place',
            'position'          => $position, ]
        );
    }

    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
