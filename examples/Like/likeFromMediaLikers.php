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

    $items = array_slice($items, 0, 6);
    $ig->event->preparePerfWithImpressions($items, 'profile');

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

    $ig->event->sendNavigation('button', 'likers', 'profile');
    $ig->event->sendProfileView($userList[0]->getPk());

    $ig->people->getFriendship($userList[0]->getPk());
    $ig->highlight->getUserFeed($userList[0]->getPk());
    $ig->people->getInfoById($userList[0]->getPk());
    $ig->story->getUserStoryFeed($userList[0]->getPk());
    $userFeed = $ig->timeline->getUserFeed($userList[0]->getPk());
    $items = $userFeed->getItems();

    $items = array_slice($items, 0, 6);
    $ig->event->preparePerfWithImpressions($items, 'profile');

    $ig->event->sendThumbnailImpression('instagram_thumbnail_click', $items[0], 'profile');
    $ig->event->sendProfileAction('tap_grid_post', $userList[0]->getPk(),
        [
            [
                'module'        => 'likers',
                'click_point'   => 'button',
            ],
            [
                'module'        => 'feed_contextual_profile',
                'click_point'   => 'media_likes',
            ],
            [
                'module'        => 'profile',
                'click_point'   => 'button',
            ],
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

    $c = 0;
    foreach ($items as $item) {
        if ($c === 3) {
            break;
        }
        $ig->event->sendOrganicMediaImpression($item, 'feed_contextual_profile');
        $commentInfos = $ig->media->getCommentInfos($item->getId())->getCommentInfos()->getData();

        $ig->event->sendOrganicNumberOfLikes($item, 'feed_contextual_profile');
        foreach ($commentInfos as $key => $value) {
            $previewComments = $value->getPreviewComments();
            if ($previewComments !== null) {
                foreach ($previewComments as $comment) {
                    $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
                }
            }
        }
        $ig->media->like($item->getId(), $c, 'feed_contextual_profile');
        $ig->event->sendOrganicLike($item, 'feed_contextual_profile', null, null, $ig->session_id);
        $c++;
    }

    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
