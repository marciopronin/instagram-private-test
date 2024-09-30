<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../../vendor/autoload.php';

// ///// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
// ////////////////////

$ig = new InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $ig->event->sendNavigationTabClicked('main_home', 'main_profile', 'feed_timeline');
    $ig->event->sendNavigation('main_profile', 'feed_timeline', 'self_profile');

    $traySession = InstagramAPI\Signatures::generateUUID();

    $ig->highlight->getSelfUserFeed();
    $ig->people->getSelfInfo();
    $userFeed = $ig->timeline->getSelfUserFeed();
    $ig->story->getArchiveBadgeCount();
    $items = $userFeed->getItems();

    $items = array_slice($items, 0, 6);
    $ig->event->preparePerfWithImpressions($items, 'self_profile');

    $navstack =
        [
            [
                'module'        => 'self_profile',
                'click_point'   => 'inferred_source',
            ],
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_profile',
            ],
            [
                'module'        => 'login',
                'click_point'   => 'cold start',
            ],
        ];

    $ig->event->sendProfileAction('tap_follow_details', $ig->account_id, $navstack, ['module' => 'self']);
    $ig->event->sendProfileAction('tap_followers', $ig->account_id, $navstack, ['module' => 'self']);

    $ig->event->sendNavigation(
        'button',
        'self_profile',
        'self_following',
        null,
        null,
        [
            'source_tab'    => 'following',
            'dest_tab'      => 'following',
        ]
    );

    $ig->event->sendProfileAction('tap_followers', $ig->account_id, $navstack, ['module' => 'self']);

    $ig->event->sendNavigation(
        'following',
        'self_unified_follow_lists',
        'self_unified_follow_lists',
        null,
        null,
        [
            'source_tab'    => 'following',
            'dest_tab'      => 'following',
        ]
    );

    $ig->event->sendNavigation('button', 'self_unified_follow_lists', 'profile');

    $rankToken = InstagramAPI\Signatures::generateUUID();
    $followings = $ig->people->getSelfFollowing($rankToken)->getUsers();
    $userId = $followings[0]->getPk();

    $traySession = InstagramAPI\Signatures::generateUUID();
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

    $blockRequestId = InstagramAPI\Signatures::generateUUID();
    $ig->event->sendUserReport($userId, 'open_user_overflow');
    $navstack =
    [
        [
            'module'        => 'profile',
            'click_point'   => 'inferred_source',
        ],
        [
            'module'        => 'self_unified_follow_lists',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'self_profile',
            'click_point'   => 'inferred_source',
        ],
        [
            'module'        => 'feed_timeline',
            'click_point'   => 'main_profile',
        ],
        [
            'module'        => 'login',
            'click_point'   => 'cold start',
        ],
    ];
    $ig->event->sendProfileAction('block_tap', $userId, $navstack, ['request_id' => $blockRequestId]);
    $ig->people->block($userId);
    $ig->event->sendProfileAction('block_confirm', $userId, $navstack, ['request_id' => $blockRequestId]);
    $ig->event->sendUserReport($userId, 'block_or_unblock_user');

    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
