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

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $ig->event->sendNavigationTabClicked('main_home', 'main_profile', 'feed_timeline');
    $ig->event->sendNavigation('main_profile', 'feed_timeline', 'self_profile');

    $traySession = \InstagramAPI\Signatures::generateUUID();

    $ig->highlight->getSelfUserFeed();
    $ig->people->getSelfInfo();
    $userFeed = $ig->timeline->getSelfUserFeed();
    $ig->discover->profileSuBadge();
    $ig->story->getArchiveBadgeCount();
    $items = $userFeed->getItems();

    $c = 0;
    foreach ($items as $item) {
        if ($c === 6) {
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

            $ig->event->sendPerfPercentPhotosRendered('self_profile', $item->getId(), $options);
            $c++;
        }
        $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $item, 'self_profile');
    }

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

    $ig->event->sendNavigation('button', 'self_profile', 'self_following', null, null,
    [
        'source_tab'    => 'following',
        'dest_tab'      => 'following',
    ]);

    $ig->event->sendProfileAction('tap_followers', $ig->account_id, $navstack, ['module' => 'self']);

    $ig->event->sendNavigation('following', 'self_unified_follow_lists', 'self_unified_follow_lists', null, null,
        [
            'source_tab'    => 'following',
            'dest_tab'      => 'following',
        ]
    );

    $rankToken = \InstagramAPI\Signatures::generateUUID();
    $followings = $ig->people->getSelfFollowing($rankToken)->getUsers();

    $ig->event->sendFollowButtonTapped($followings[0]->getPk(), 'self_following', $navstack, null, true);

    $ig->people->unfollow($followings[0]->getPk());
    $ig->event->sendProfileAction('unfollow', $followings[0]->getPk(), $navstack, ['click_point' => 'following_list']);

    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
