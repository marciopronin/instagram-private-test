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
$usersToFollow = ['selenagomez'];
//////////////////////

// YOU SHOULD ONLY USE THIS TEMPLATE IN YOU ADD MORE ROUTINES IN BETWEEN

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
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}

$firstSearch = false;

try {
    foreach($usersToFollow as $usernameToFollow) 
    {
     
        if ($firstSearch === false) {
            usleep(mt_rand(1000000, 4000000));
        }

        $searchResponse = $ig->discover->search($usernameToFollow);

        if ($firstSearch === false) {
            $ig->event->sendNavigation('button', 'explore_popular', 'search');
            $ig->event->sendNavigation('button', 'search', 'blended_search');
        } else {
            $firstSearch = true;
        }

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

        $c = 0;
        foreach ($items as $item) {
            if ($c === 5) {
                break;
            }

            if ($item->getMediaType() === 1) {
                $imageResponse = $ig->request($item->getImageVersions2()->getCandidates()[0]->getUrl());

                if (isset($imageResponse->getHttpResponse()->getHeaders()['x-encoded-content-length'])) {
                    $imageSize = $imageResponse->getHttpResponse()->getHeaders()['x-encoded-content-length'][0];
                } elseif (isset($imageResponse->getHttpResponse()->getHeaders()['Content-Length'])) {
                    $imageSize = $imageResponse->getHttpResponse()->getHeaders()['Content-Length'][0];
                }  elseif (isset($imageResponse->getHttpResponse()->getHeaders()['content-length'])) {
                    $imageSize = $imageResponse->getHttpResponse()->getHeaders()['content-length'][0];
                } else {
                    continue;
                }

                $options = [
                    'is_grid_view'                      => true,
                    'rendered'                          => true,
                    'did_fallback_render'               => false,
                    'is_carousel'                       => false,
                    'image_size_kb'                     => $imageSize,
                    'estimated_bandwidth'               => mt_rand(1000, 4000),
                    'estimated_bandwidth_totalBytes_b'  => $ig->client->totalBytes,
                    'estimated_bandwidth_totalTime_ms'  => $ig->client->totalTime,
                ];

                $ig->event->sendPerfPercentPhotosRendered('profile', $item->getId(), $options);
                $c++;
            }
            $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $item, 'profile');
        }
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

        usleep(mt_rand(1000000, 2000000));
        $ig->event->updateAppState('profile', 'background');
        //
        // Other routines can be added here to increase entropy
        // The more different actions you do, the better for defeating
        // AI classifier.
        //
        $ig->event->forceSendBatch();
        usleep(mt_rand(1000000, 4000000));
        $ig->event->updateAppState('profile', 'foreground');
        $ig->event->sendNavigation('back', 'profile', 'blended_search');
    }
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
