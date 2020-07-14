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
    $ig->event->sendNavigation('main_profile', 'feed_timeline', 'self_profile');

    $ig->highlight->getSelfUserFeed();
    $ig->people->getSelfInfo();
    $ig->discover->profileSuBadge();
    $ig->story->getArchiveBadgeCount();

    $ig->event->sendProfileAction('tap_follow_details', $ig->account_id,
        [
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_profile',
            ],
    ], ['module' => 'self']);

    $ig->event->sendNavigation('button', 'self_profile', 'self_unified_follow_lists');

    $ig->event->sendProfileAction('tap_followers', $ig->account_id,
        [
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_profile',
            ],
    ], ['module' => 'self']);

    $ig->event->sendNavigation('following', 'self_unified_follow_lists', 'self_unified_follow_lists', null, null,
        [
            'source_tab'    => 'following',
            'dest_tab'      => 'following',
        ]
    );

    $rankToken = \InstagramAPI\Signatures::generateUUID();
    $followings = $ig->people->getSelfFollowing($rankToken)->getUsers();

    $ig->event->sendFollowButtonTapped($followings[0]->getPk(), 'self_following',
        [
            [
                'module'        => 'self_unified_follow_lists',
                'click_point'   => 'following',
            ],
            [
                'module'        => 'self_unified_follow_lists',
                'click_point'   => 'following',
            ],
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_profile',
            ],
        ], null, true);

    $ig->people->unfollow($followings[0]->getPk());
    $ig->event->sendProfileAction('unfollow', $followings[0]->getPk(),
        [
            [
                'module'        => 'self_unified_follow_lists',
                'click_point'   => 'following',
            ],
            [
                'module'        => 'self_unified_follow_lists',
                'click_point'   => 'following',
            ],
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_profile',
            ],
        ], ['click_point' => 'following_list']
    );

    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
