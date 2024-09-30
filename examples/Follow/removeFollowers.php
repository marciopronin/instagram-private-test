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
    $ig->event->sendNavigation('main_profile', 'feed_timeline', 'self_profile');

    $ig->highlight->getSelfUserFeed();
    $ig->people->getSelfInfo();
    $ig->story->getArchiveBadgeCount();

    $ig->event->sendProfileAction(
        'tap_follow_details',
        $ig->account_id,
        [
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_profile',
            ],
        ],
        ['module' => 'self']
    );

    $ig->event->sendNavigation('button', 'self_profile', 'self_unified_follow_lists');

    $ig->event->sendProfileAction(
        'tap_followers',
        $ig->account_id,
        [
            [
                'module'        => 'self_profile',
                'click_point'   => 'button',
            ],
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_profile',
            ],
        ],
        ['module' => 'self']
    );

    $ig->event->sendNavigation(
        'followers',
        'self_unified_follow_lists',
        'self_unified_follow_lists',
        null,
        null,
        [
            'source_tab'    => 'followers',
            'dest_tab'      => 'followers',
        ]
    );

    $rankToken = InstagramAPI\Signatures::generateUUID();
    $followers = $ig->people->getSelfFollowers()->getUsers();

    $ig->people->removeFollower($followers[0]->getPk());
    $ig->event->sendRemoveFollowerConfirmed($followers[0]->getPk());

    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
