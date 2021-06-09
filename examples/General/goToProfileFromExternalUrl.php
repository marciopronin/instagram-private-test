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
$user = '';
////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

$ig->event->updateAppState('background');
$ig->event->forceSendBatch();
usleep(mt_rand(4000000, 7000000)); // Can be increased for better emulation.

try {
    $ig->event->updateAppState('foreground');
    $ig->event->qeExposure($ig->account_id, 'ig_android_qr_code_nametag', 'deploy');
    $ig->event->sendNavigation('inferred_source', 'feed_timeline', 'profile');

    try {
        $ig->internal->getQPFetch();
    } catch (Exception $e) {
        // pass
    }

    $userInfo = $ig->people->getInfoByName($user, 'deep_link_util');
    $userId = $userInfo->getUser()->getPk();
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
                'click_point'   => 'inferred_source',
            ],
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'inferred_source',
            ],
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'inferred_source',
            ],
            [
                'module'        => 'login',
                'click_point'   => 'cold start',
            ],
        ];

    $ig->event->updateAppState('background');
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
