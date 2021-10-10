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
$query = 'Madrid, Spain';
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    // Explore and search session, will be used for the Graph API events.
    $searchSession = \InstagramAPI\Signatures::generateUUID();
    $rankToken = \InstagramAPI\Signatures::generateUUID();

    $topicData =
    [
        'topic_cluster_title'       => 'For You',
        'topic_cluster_id'          => 'explore_all:0',
        'topic_cluster_type'        => 'explore_all',
        'topic_cluster_session_id'  => $searchSession,
        'topic_nav_order'           => 0,
    ];

    $ig->event->sendNavigation('main_search', 'feed_timeline', 'explore_popular', null, null, $topicData);
    $ig->discover->getNullStateDynamicSections();
    $ig->discover->getSuggestedSearches('blended');

    $sectionalItems = $ig->discover->getExploreFeed('explore_all:0', $searchSession)->getSectionalItems();
    $ig->event->prepareAndSendExploreImpression('explore_all:0', $searchSession, $sectionalItems);

    $timeToSearch = mt_rand(2000, 3500);
    sleep($timeToSearch / 1000);

    $ig->event->sendNavigation('button', 'explore_popular', 'search');
    $ig->event->sendNavigation('button', 'search', 'blended_search');
    $ig->event->sendNavigation('button', 'blended_search', 'search_places');

    $locationItems = $ig->location->findPlaces($query)->getItems();

    $resultList = [];
    $resultTypeList = [];
    $position = 0;
    $found = false;
    $placeId = null;
    foreach ($locationItems as $locationItem) {
        $resultList[] = $locationItem->getLocation()->getFacebookPlacesId();
        $resultTypeList[] = 'PLACE';

        if ($locationItem->getTitle() === $query) {
            $found = true;
            $placeId = $locationItem->getLocation()->getFacebookPlacesId();
        }

        if ($found !== true) {
            $position++;
        }
    }

    $ig->event->sendSearchResults($query, $resultList, $resultTypeList, $rankToken, $searchSession, 'search_places');
    $ig->event->sendSearchResultsPage($query, $placeId, $resultList, $resultTypeList, $rankToken, $searchSession, $position, 'PLACE', 'search_places');

    $ig->event->sendNavigation('search_result', 'search_places', 'feed_location', null, null,
        [
            'rank_token'        => $rankToken,
            'query_text'        => $query,
            'search_session_id' => $searchSession,
            'search_tab'        => 'search_places',
            'selected_type'     => 'place',
            'position'          => $position,
            'entity_page_name'  => $query,
            'entity_page_id'    => $placeId,
        ]
    );

    $sections = $ig->location->getFeed($placeId, $rankToken)->getSections();
    $ig->location->getStoryFeed($placeId);
    $ig->location->getInfo($placeId);

    $item = null;
    // For each item in the location feed, we will send a thumbnail impression.
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
                        'estimated_bandwidth'               => $ig->client->bandwidthB,
                        'estimated_bandwidth_totalBytes_b'  => $ig->client->totalBytes,
                        'estimated_bandwidth_totalTime_ms'  => $ig->client->totalTime,
                    ]);
                    $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $media->getMedia(), 'feed_location');
                }
            }
        }
    }

    if (empty($sections)) {
        // No sections.
        exit();
    }

    // Send thumbnail click impression (clickling on the selected media).
    $ig->event->sendThumbnailImpression('instagram_thumbnail_click', $item, 'feed_location');
    // When we clicked the item, we are navigating from 'feed_location' to 'feed_contextual_location'.
    $ig->event->sendNavigation('button', 'feed_location', 'feed_contextual_location');

    $ig->event->sendOrganicNumberOfLikes($item, 'feed_contextual_location');
    $previewComments = $item->getPreviewComments();
    if ($previewComments !== null) {
        foreach ($previewComments as $comment) {
            $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
        }
    }

    $userId = $item->getUser()->getPk();
    $ig->event->sendNavigation('media_owner', 'feed_contextual_location', 'profile', null, null,
        [
            'rank_token'        => $rankToken,
            'query_text'        => $query,
            'search_session_id' => $searchSession,
            'search_tab'        => 'search_places',
            'selected_type'     => 'place',
            'position'          => $position,
            'username'          => $item->getUser()->getUsername(),
            'user_id'           => $userId,
        ]
    );

    $traySession = \InstagramAPI\Signatures::generateUUID();
    $ig->highlight->getUserFeed($userId);
    $ig->story->getUserStoryFeed($userId);
    $ig->event->reelTrayRefresh(
        [
            'tray_session_id'   => $traySession,
            'tray_refresh_time' => number_format(mt_rand(100, 500) / 1000, 3),
        ],
        'network'
    );

    try {
        $ig->internal->getQPFetch();
    } catch (Exception $e) {
    }

    sleep(2);
    $ig->event->sendProfileView($userId);
    $ig->event->sendFollowButtonTapped($userId, 'profile',
        [
            [
                'module'        => 'feed_contextual_location',
                'click_point'   => 'media_owner',
            ],
            [
                'module'        => 'feed_location',
                'click_point'   => 'button',
            ],
            [
                'module'        => 'search_places',
                'click_point'   => 'search_result',
            ],
            [
                'module'        => 'blended_search',
                'click_point'   => 'button',
            ],
            [
                'module'        => 'blended_search',
                'click_point'   => 'button',
            ],
            [
                'module'        => 'explore_popular',
                'click_point'   => 'button',
            ],
            [
                'module'        => 'explore_popular',
                'click_point'   => 'explore_topic_load',
            ],
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_search',
            ],
        ]
    );
    $ig->people->follow($userId);

    $ig->event->sendProfileAction('follow', $userId,
        [
            [
                'module'        => 'feed_contextual_location',
                'click_point'   => 'media_owner',
            ],
            [
                'module'        => 'feed_location',
                'click_point'   => 'button',
            ],
            [
                'module'        => 'search_places',
                'click_point'   => 'search_result',
            ],
            [
                'module'        => 'blended_search',
                'click_point'   => 'button',
            ],
            [
                'module'        => 'blended_search',
                'click_point'   => 'button',
            ],
            [
                'module'        => 'explore_popular',
                'click_point'   => 'button',
            ],
            [
                'module'        => 'explore_popular',
                'click_point'   => 'explore_topic_load',
            ],
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_search',
            ],
        ]
    );
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
