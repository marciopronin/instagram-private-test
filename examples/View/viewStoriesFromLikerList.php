<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////

//////////////////////
$queryUser = 'selenagomez'; // :)
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

    // Time spent to search.
    $timeToSearch = mt_rand(2000, 3500);
    sleep($timeToSearch / 1000);

    // Search query and parse results.
    $searchResponse = $ig->discover->search($queryUser);
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
            $resultTypeList[] = 'HASHTAG';
        } elseif ($searchResult->getUser() !== null) {
            $resultList[] = $searchResult->getUser()->getPk();
            // We will save the data when the result matches our query.
            // Hashtag ID is required in the next steps for Graph API and
            // like().
            if ($searchResult->getUser()->getUsername() === $queryUser) {
                $userId = $searchResult->getUser()->getPk();
                // This request tells Instagram that we have clicked in this specific user.
                $ig->discover->registerRecentSearchClick('user', $userId);
                // When this flag is set to true, position won't increment
                // anymore. We are using this to track the result position.
                $found = true;
            }
            $resultTypeList[] = 'USER';
        } else {
            $resultList[] = $searchResult->getPlace()->getLocation()->getPk();
            $resultTypeList[] = 'PLACE';
        }
        if ($found !== true) {
            $position++;
        }
    }

    // Send restults from search.
    $ig->event->sendSearchResults($queryUser, $resultList, $resultTypeList, $rankToken, $searchSession, 'blended_search');
    // Send selected result from results.
    $ig->event->sendSearchResultsPage($queryUser, $userId, $resultList, $resultTypeList, $rankToken, $searchSession, $position, 'USER', 'blended_search');

    // When we clicked the user, we are navigating from 'blended_search' to 'profile'.
    $ig->event->sendNavigation('button', 'blended_search', 'profile', null, null,
        [
            'rank_token'            => null,
            'query_text'            => $queryUser,
            'search_session_id'     => $searchSession,
            'selected_type'         => 'user',
            'position'              => 0,
            'username'              => $queryUser,
            'user_id'               => $userId,
        ]
    );
    $ig->event->sendProfileView($userId);

    $ig->people->getFriendship($userId);
    $ig->highlight->getUserFeed($userId);
    $ig->people->getInfoById($userId, 'blended_search');
    $ig->story->getUserStoryFeed($userId);
    $userFeed = $ig->timeline->getUserFeed($userId);
    $items = $userFeed->getItems();

    foreach ($items as $item) {
        $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $item, 'profile');
    }
    $ig->event->sendThumbnailImpression('instagram_thumbnail_click', $items[0], 'profile');
    $ig->event->sendProfileAction('tap_grid_post', $userId,
        [
            [
                'module'        => 'blended_search',
                'click_point'   => 'search_result',
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
    ]);

    $ig->event->sendNavigation('button', 'profile', 'feed_contextual_profile');
    $ig->event->sendOrganicMediaImpression($items[0], 'feed_contextual_profile');
    $commentInfos = $ig->media->getCommentInfos($items[0]->getId())->getCommentInfos()->getData();

    $ig->event->sendOrganicNumberOfLikes($items[0], 'feed_contextual_profile');

    foreach ($commentInfos as $key => $value) {
        $previewComments = $value->getPreviewComments();
        if ($previewComments !== null) {
            foreach ($previewComments as $comment) {
                $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
            }
        }
    }

    $ig->event->sendNavigation('media_likes', 'feed_contextual_profile', 'likers');

    // Only latest 1000 likers are returned and it is not possible to paginate.
    $likersResponse = $ig->media->getLikers($items[0]->getId());
    $userList = $likersResponse->getUsers();

    for ($i = 0; $i < 15; $i++) {
        $storyFeed = $ig->story->getUserStoryFeed($userList[$i]->getPk());
        if ($storyFeed->getReel() === null) {
            // User has no active stories
            continue;
        }

        $storyItems = $storyFeed->getReel()->getItems();
        $following = $storyFeed->getReel()->getUser()->getFriendshipStatus()->getFollowing();
        $ig->event->sendNavigation('button', 'likers', 'reel_liker_list');

        $viewerSession = \InstagramAPI\Signatures::generateUUID();
        $traySession = \InstagramAPI\Signatures::generateUUID();
        $rankToken = \InstagramAPI\Signatures::generateUUID();

        $ig->event->sendReelPlaybackEntry($userId, $viewerSession, $traySession, 'reel_liker_list');

        $reelsize = count($storyItems);
        $cnt = 0;
    
        $photosConsumed = 0;
        $videosConsumed = 0;
    
        foreach ($storyItems as $storyItem) {
    
            if($storyItem->getMediaType() == 2) {
                $videosConsumed++;
            } else {
                $photosConsumed++;
            }
    
            $ig->event->sendOrganicMediaSubImpression($storyItem,
                [
                    'tray_session_id'   => $traySession, 
                    'viewer_session_id' => $viewerSession,
                    'following'         => $following,
                    'reel_size'         => $reelsize,
                    'reel_position'     => $cnt,
                ], 
                'reel_liker_list'
            );
    
            $ig->event->sendOrganicViewedSubImpression($storyItem, $viewerSession, $traySession,
                [
                    'tray_session_id'   => $traySession, 
                    'viewer_session_id' => $viewerSession,
                    'following'         => $following,
                    'reel_size'         => $reelsize,
                    'reel_position'     => $cnt,
                ],
                'reel_liker_list'
            );
    
            $ig->event->sendOrganicTimespent($storyItem, $following, mt_rand(1000, 2000), 'reel_liker_list', [],
                 [
                    'tray_session_id'   => $traySession, 
                    'viewer_session_id' => $viewerSession,
                    'following'         => $following,
                    'reel_size'         => $reelsize,
                    'reel_position'     => $cnt,
                 ]
            );
    
            $ig->event->sendOrganicVpvdImpression($storyItem,
                 [
                    'tray_session_id'       => $traySession, 
                    'viewer_session_id'     => $viewerSession,
                    'following'             => $following,
                    'reel_size'             => $reelsize,
                    'reel_position'         => $cnt,
                    'client_sub_impression' => 1,
                 ],
                 'reel_liker_list'
            );
    
            $ig->event->sendOrganicReelImpression($storyItem, $viewerSession, $traySession, $rankToken, true, 'reel_liker_list');
            $ig->event->sendOrganicMediaImpression($storyItem, 'reel_liker_list', 
                [
                    'story_ranking_token'   => $rankToken, 
                    'tray_session_id'       => $traySession, 
                    'viewer_session_id'     => $viewerSession
                ]
            );
            $ig->event->sendOrganicViewedImpression($storyItem, 'reel_liker_list', $viewerSession, $traySession, $rankToken);
    
            $cnt++;
        }

        sleep(mt_rand(1, 3));

        $ig->story->markMediaSeen($storyItems);
        $ig->event->sendReelPlaybackNavigation(end($storyItems), $viewerSession, $traySession, $rankToken, 'reel_liker_list');
        $ig->event->sendReelSessionSummary($item, $viewerSession, $traySession, 'reel_liker_list',
            [
                'tray_session_id'               => $traySession, 
                'viewer_session_id'             => $viewerSession,
                'following'                     => $following,
                'reel_size'                     => $reelsize,
                'reel_position'                 => count($storyItems) - 1,
                'is_last_reel'                  => 1,
                'photos_consumed'               => $photosConsumed,
                'videos_consumed'               => $videosConsumed,
                'viewer_session_media_consumed' => count($storyItems),
            ]
        );
        $ig->event->sendNavigation('back', 'reel_liker_list', 'likers');

        sleep(2);
    }

    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
