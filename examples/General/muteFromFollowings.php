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
    // Send navigation from 'feed_timeline' to 'explore_popular'.
    $ig->event->sendNavigation('main_profile', 'feed_timeline', 'self_profile');

    $ig->event->sendProfileView($this->account_id);

    $ig->people->getFriendship($this->account_id);
    $ig->highlight->getUserFeed($this->account_id);
    $ig->people->getInfoById($this->account_id, 'self_profile');
    $ig->story->getUserStoryFeed($this->account_id);
    $userFeed = $ig->timeline->getUserFeed($this->account_id);
    $items = $userFeed->getItems();

    $c = 0;
    foreach ($items as $item) {
        if ($c === 5) {
            break;
        }
        $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $item, 'self_profile');
        $c++;
    }
    $ig->event->sendProfileAction('tap_follow_details', $this->account_id,
        [
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_profile',
            ],
    ], ['module' => 'self_profile', 'follow_status' => 'self']);

    $ig->event->sendNavigation('button', 'self_profile', 'self_unified_follow_lists');
    $ig->event->sendProfileAction('tap_followers', $this->account_id,
        [
            [
                'module'        => 'self_profile',
                'click_point'   => 'button',
            ],
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_profile',
            ],
    ], ['module' => 'self_profile', 'follow_status' => 'self']);

    $ig->event->sendNavigation('following', 'self_unified_follow_lists', 'self_unified_follow_lists', null, null,
        [
            'source_tab'    => 'following',
            'dest_tab'      => 'following',
        ]
    );

    $ig->discover->surfaceWithSu($this->account_id);

    $rankToken = \InstagramAPI\Signatures::generateUUID();
    $followers = $ig->people->getFollowings($this->account_id, $rankToken);
    $userId = $followers->getUsers()[0]->getPk();

    $ig->event->sendNavigation('button', 'self_unified_follow_lists', 'media_mute_sheet');

    $ig->people->muteUserMedia($userId, 'post');
    $ig->event->sendMuteMedia('post', true, false, $userId, $followers->getUsers()->getIsPrivate());
    $ig->people->muteUserMedia($userId, 'story');
    $ig->event->sendMuteMedia('ig_mute_stories', true, false, $userId, $followers->getUsers()[0]->getIsPrivate());

    $ig->event->sendNavigation('button', 'media_mute_sheet', 'self_unified_follow_lists');


    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
