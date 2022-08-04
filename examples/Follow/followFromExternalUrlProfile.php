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

/////// USER ////////
$urlProfile = '';
////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
\InstagramAPI\Instagram::$skipLoginFlowAtMyOwnRisk = true;

try {
    // Account should be already logged in!
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $ig->request($urlProfile)->getDecodedResponse(false);

    $re = '/https:\/\/(www)?instagram\.com\/(.*)\?/m';
    preg_match_all($re, $urlProfile, $matches, PREG_SET_ORDER, 0);

    if (empty($matches)) {
        exit();
    }
    $user = $matches[0][1];

    $ig->event->sendNavigation('cold start', 'login', 'feed_timeline');
    $ig->event->updateAppState('foreground');
    $ig->event->sendNavigation('warm_start', 'feed_timeline', 'profile');

    // If already known the user ID, it is better to set $userID directly and avoid the followin request!
    $userId = $ig->people->getInfoByName($user, 'deep_link_util')->getUser()->getPk();

    $ig->people->getFriendship($userId);
    $ig->event->qeExposure($ig->account_id, 'ig_android_qr_code_nametag', 'deploy');
    //$ig->event->sendNavigation('inferred_source', 'feed_timeline', 'profile', null, null, ['username' => $user, 'user_id' => $userId]);

    try {
        $ig->internal->getQPFetch();
    } catch (Exception $e) {
        // pass
    }
    $ig->event->sendBadgingEvent('impression', 'photos_of_you', 0, 'profile_menu', 'dot_badge');

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

    $navstack =
        [
            [
                'module'        => 'profile',
                'click_point'   => 'back',
            ],
            [
                'module'        => 'feed_contextual_chain',
                'click_point'   => 'back',
            ],
            [
                'module'        => 'explore_popular', // I think this clickpoints needs to be added in events navigation path check
                'click_point'   => 'explore_topic_load',
            ],
            [
                'module'        => 'explore_popular',
                'click_point'   => 'button',
            ],
            [
                'module'        => 'blended_search',
                'click_point'   => 'button',
            ],
            [
                'module'        => 'blended_search',
                'click_point'   => 'back',
            ],
            [
                'module'        => 'explore_popular',
                'click_point'   => 'explore_topic_load',
            ],
            [
                'module'        => 'explore_popular',
                'click_point'   => 'main_home',
            ],
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'warm_start',
            ],
            [
                'module'        => 'profile',
                'click_point'   => 'button',
            ],
        ];

    $navstack = array_reverse($navstack);

    $ig->event->sendFollowButtonTapped($userId, 'profile');
    $ig->people->follow($userId);
    $ig->event->sendProfileAction('follow', $userId, $navstack);

    $ig->event->sendProfileAction('notifications_entry_point_impression', $userId, $navstack);

    $rankToken = \InstagramAPI\Signatures::generateUUID();
    $chainingUsers = $ig->discover->getChainingUsers($userId, 'profile')->getUsers();

    foreach ($chainingUsers as $user) {
        $ig->event->sendSimilarUserImpression($userId, $user->getPk());
    }
    foreach ($chainingUsers as $user) {
        $imageResponse = $ig->request($user->getProfilePicUrl());

        if (isset($imageResponse->getHttpResponse()->getHeaders()['x-encoded-content-length'])) {
            $imageSize = $imageResponse->getHttpResponse()->getHeaders()['x-encoded-content-length'][0];
        } elseif (isset($imageResponse->getHttpResponse()->getHeaders()['Content-Length'])) {
            $imageSize = $imageResponse->getHttpResponse()->getHeaders()['Content-Length'][0];
        } elseif (isset($imageResponse->getHttpResponse()->getHeaders()['content-length'])) {
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

        $ig->event->sendPerfPercentPhotosRendered('profile', $user->getProfilePicId(), $options);
    }

    $ig->event->updateAppState('background');
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
