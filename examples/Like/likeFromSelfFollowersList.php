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

    $ig->event->sendNavigation('button', 'self_profile', 'self_followers', null, null,
    [
        'source_tab'    => 'followers',
        'dest_tab'      => 'followers',
    ]);

    $ig->event->sendProfileAction('tap_followers', $ig->account_id, $navstack, ['module' => 'self']);

    $ig->event->sendNavigation('followers', 'self_unified_follow_lists', 'self_unified_follow_lists', null, null,
        [
            'source_tab'    => 'followers',
            'dest_tab'      => 'followers',
        ]
    );

    $rankToken = \InstagramAPI\Signatures::generateUUID();
    $followers = $ig->people->getSelfFollowers($rankToken)->getUsers();

    $followerList = [];
    foreach ($followers->getUsers() as $follower) {
        $followerList[] = $follower->getPk();
    }

    $ig->people->getFriendships($followerList);
    $ig->discover->markSuSeen();
    $ig->discover->getAyml();
    $ig->people->getFriendships($followerList);

    $cc = 0;
    $randomFollowers = array_rand($followerList, 3);
    foreach ($randomFollowers as $follower) {
        $ig->event->sendNavigation('button', 'unified_follow_lists', 'profile');

        $ig->event->sendProfileView($follower);

        $ig->people->getFriendship($follower);
        $ig->highlight->getUserFeed($follower);
        $ig->people->getInfoById($follower);
        $ig->story->getUserStoryFeed($follower);
        $userFeed = $ig->timeline->getUserFeed($follower);
        $items = $userFeed->getItems();

        $items = array_slice($items, 0, 6);
        $ig->event->preparePerfWithImpressions($items, 'profile');

        $ig->event->sendNavigation('button', 'profile', 'feed_contextual_profile');

        $cc = 0;
        foreach ($items as $item) {
            if ($cc === 3) {
                break;
            }
            $ig->event->sendOrganicMediaImpression($item, 'feed_contextual_profile');
            $commentInfos = $ig->media->getCommentInfos($item->getId())->getCommentInfos()->getData();
            $ig->event->sendOrganicNumberOfLikes($item, 'feed_contextual_profile');

            foreach ($commentInfos as $key => $value) {
                $previewComments = $value->getPreviewComments();
                if ($previewComments !== null) {
                    foreach ($previewComments as $comment) {
                        $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
                    }
                }
            }

            // Since we are going to like the first item of the media, the position in
            // the feed is 0. If you want to like the second item, it would position 1, and so on.
            $ig->media->like($item->getId(), 0);
            $ig->event->sendOrganicLike($item, 'feed_contextual_profile', null, null, $ig->session_id);
        }

        $ig->event->sendNavigation('back', 'feed_contextual_profile', 'profile');
        $ig->event->sendNavigation('back', 'profile', 'unified_follow_lists');

        usleep(mt_rand(1000000, 6000000));
        $cc++;
    }

    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
