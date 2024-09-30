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
                'module'        => 'feed_timeline',
                'click_point'   => 'main_profile',
            ],
        ],
        ['module' => 'self']
    );

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

    $rankToken = InstagramAPI\Signatures::generateUUID();
    $followings = $ig->people->getSelfFollowing($rankToken)->getUsers();
    $userId = $followings[0]->getPk();

    $ig->discover->surfaceWithSu($ig->account_id);

    $ig->event->sendNavigation('button', 'self_unified_follow_lists', 'media_mute_sheet');

    $ig->people->muteUserMedia($userId, 'post');
    $ig->event->sendMuteMedia('post', true, false, $userId, $followings[0]->getIsPrivate());
    $ig->people->muteUserMedia($userId, 'story');
    $ig->event->sendMuteMedia('ig_mute_stories', true, false, $userId, $followings[0]->getIsPrivate());

    $ig->event->sendNavigation('button', 'media_mute_sheet', 'self_unified_follow_lists');

    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
