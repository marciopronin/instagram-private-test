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
$usernameToFollow = 'selenagomez';
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

    $ig->event->sendNavigationTabClicked('main_home', 'main_search', 'feed_timeline');
    $ig->event->sendNavigation('main_search', 'feed_timeline', 'explore_popular', null, null, $topicData);
    $ig->discover->getNullStateDynamicSections();
    $ig->discover->getSuggestedSearches('blended');
    // Get explore feed sections and items.
    $sectionalItems = $ig->discover->getExploreFeed('explore_all:0', $searchSession)->getSectionalItems();
    $ig->event->prepareAndSendExploreImpression('explore_all:0', $searchSession, $sectionalItems);

    $timeToSearch = mt_rand(2000, 3500);
    sleep($timeToSearch / 1000);

    $searchResponse = $ig->discover->search($usernameToFollow);
    $ig->event->sendNavigation('button', 'explore_popular', 'search');
    $ig->event->sendNavigation('button', 'search', 'blended_search');
    $searchResults = $searchResponse->getList();
    $rankToken = $searchResponse->getRankToken();
    $resultList = [];
    $resultTypeList = [];
    $position = 0;
    $found = false;
    $userId = null;
    foreach ($searchResults as $searchResult) {
        if ($searchResult->getUser() !== null) {
            $resultList[] = $searchResult->getUser()->getPk();
            if ($searchResult->getUser()->getUsername() === $usernameToFollow) {
                $found = true;
                $userId = $searchResult->getUser()->getPk();
            }
            $resultTypeList[] = 'USER';
        } elseif ($searchResult->getHashtag() !== null) {
            $resultList[] = $searchResult->getHashtag()->getId();
            $resultTypeList[] = 'HASHTAG';
        } else {
            $resultList[] = $searchResult->getPlace()->getLocation()->getPk();
            $resultTypeList[] = 'PLACE';
        }
        if ($found !== true) {
            $position++;
        }
    }
    $ig->event->sendSearchResults($usernameToFollow, $resultList, $resultTypeList, $rankToken, $searchSession, 'blended_search');
    $ig->event->sendSearchResultsPage($usernameToFollow, $userId, $resultList, $resultTypeList, $rankToken, $searchSession, $position, 'USER', 'blended_search');
    $ig->discover->registerRecentSearchClick('user', $userId);
    $ig->people->getFriendship($userId);
    $suggestions = $ig->people->getInfoById($userId, 'search_users')->getUser()->getChainingSuggestions();

    if ($suggestions !== null) {
        for ($i = 0; $i < 4; $i++) {
            $ig->event->sendSimilarUserImpression($userId, $suggestions[$i]->getPk());
            $ig->event->sendSimilarEntityImpression($userId, $suggestions[$i]->getPk());
        }
    }
    $ig->event->sendNavigation('button', 'search_users', 'profile', null, null,
        [
            'rank_token'        => $rankToken,
            'query_text'        => $usernameToFollow,
            'search_session_id' => $searchSession,
            'selected_type'     => 'user',
            'position'          => 0,
            'username'          => $usernameToFollow,
            'user_id'           => $userId,
        ]
    );
    $traySession = \InstagramAPI\Signatures::generateUUID();
    $ig->highlight->getUserFeed($userId);
    $ig->story->getUserStoryFeed($userId);
    $userFeed = $ig->timeline->getUserFeed($userId);
    $items = $userFeed->getItems();

    $items = array_slice($items, 0, 6);
    $ig->event->preparePerfWithImpressions($items, 'profile');

    $ig->event->reelTrayRefresh(
        [
            'tray_session_id'   => $traySession,
            'tray_refresh_time' => number_format(mt_rand(100, 500) / 1000, 3),
        ],
        'network'
    );

    usleep(mt_rand(1500000, 2500000));
    $ig->event->sendProfileView($userId);
    $ig->event->sendFollowButtonTapped($userId, 'profile',
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
        ]
    );
    $ig->people->follow($userId);
    $ig->event->sendProfileAction('follow', $userId,
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
        ]
    );

    $rankToken = \InstagramAPI\Signatures::generateUUID();
    $ig->event->sendSearchFollowButtonClicked($userId, 'profile', $rankToken);

    $chainingUsers = $ig->discover->getChainingUsers($userId, 'profile')->getUsers();

    foreach ($chainingUsers as $user) {
        $ig->event->sendSimilarUserImpression($userId, $user->getPk());
    }

    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
