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

    $ig->highlight->getUserFeed($ig->account_id);
    $ig->people->getInfoById($ig->account_id);
    $ig->story->getUserStoryFeed($ig->account_id);
    $userFeed = $ig->timeline->getUserFeed($ig->account_id);
    $items = $userFeed->getItems();

    $c = 0;
    foreach ($items as $item) {
        if ($c === 5) {
            break;
        }
        $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $item, 'self_profile');
        $c++;
    }
    $ig->event->sendProfileAction('tap_follow_details', $ig->account_id,
        [
            [
                'module'        => 'self_profile',
                'click_point'   => 'button',
            ],
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_profile',
            ],
    ], ['module' => 'self_profile']);

    $ig->event->sendNavigation('button', 'self_profile', 'unified_follow_lists');
    $ig->event->sendProfileAction('tap_followers', $ig->account_id,
        [
            [
                'module'        => 'self_profile',
                'click_point'   => 'button',
            ],
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_profile',
            ],
    ], ['module' => 'self_profile']);

    $ig->event->sendNavigation('followers', 'unified_follow_lists', 'unified_follow_lists', null, null,
        [
            'source_tab'    => 'followers',
            'dest_tab'      => 'followers',
        ]
    );

    $rankToken = \InstagramAPI\Signatures::generateUUID();
    $followers = $ig->people->getFollowers($ig->account_id, $rankToken);
    $userId = $followers->getUsers()[0]->getPk();

    $followersList = [];
    foreach ($followers->getUsers() as $follower) {
        $followersList[] = $follower->getPk();
    }

    $ig->people->getFriendships($followersList);
    $ig->discover->markSuSeen();
    $ig->discover->getAyml();
    $ig->people->getFriendships($followersList);

    $i = 0;

    for ($i; $i < 5; $i++) {
        $ig->event->sendNavigation('button', 'unified_follow_lists', 'profile');
        $ig->event->sendProfileView($followersList[$i]);

        $ig->people->getFriendship($followersList[$i]);
        $ig->highlight->getUserFeed($followersList[$i]);
        $ig->people->getInfoById($followersList[$i]);
        $storyFeed = $ig->story->getUserStoryFeed($followersList[$i]);
        $userFeed = $ig->timeline->getUserFeed($followersList[$i]);
        $items = $userFeed->getItems();

        $c = 0;
        foreach ($items as $item) {
            if ($c === 5) {
                break;
            }
            $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $item, 'profile');
            $c++;
        }
        if ($storyFeed->getReel() === null) {
            echo 'User has no active stories';
            exit();
        }

        $storyItems = $storyFeed->getReel()->getItems();
        $following = $storyFeed->getReel()->getUser()->getFriendshipStatus()->getFollowing();
        $ig->event->sendNavigation('button', 'profile', 'reel_profile');

        $viewerSession = \InstagramAPI\Signatures::generateUUID();
        $traySession = \InstagramAPI\Signatures::generateUUID();
        $rankToken = \InstagramAPI\Signatures::generateUUID();

        $ig->event->sendReelPlaybackEntry($userId, $viewerSession, $traySession, 'reel_liker_list');

        $reelsize = count($storyItems);
        $cnt = 0;

        $photosConsumed = 0;
        $videosConsumed = 0;

        // Send impressions to all stories at once
        foreach ($storyItems as $storyItem) {
            if ($storyItem->getMediaType() == 2) {
                $videosConsumed++;
            } else {
                $photosConsumed++;
            }

            $ig->event->sendOrganicMediaSubImpression($storyItem,
                [
                    'tray_session_id'   => $traySession,
                    'viewer_session_id' => $viewerSession,
                    'following'         => $following,
                    'reel_size'         => $reelsize,
                    'reel_position'     => $cnt,
                ],
                'reel_profile'
            );

            $ig->event->sendOrganicViewedSubImpression($storyItem, $viewerSession, $traySession,
                [
                    'tray_session_id'   => $traySession,
                    'viewer_session_id' => $viewerSession,
                    'following'         => $following,
                    'reel_size'         => $reelsize,
                    'reel_position'     => $cnt,
                ],
                'reel_profile'
            );

            $ig->event->sendOrganicTimespent($storyItem, $following, mt_rand(1000, 2000), 'reel_profile', [],
                 [
                    'tray_session_id'   => $traySession,
                    'viewer_session_id' => $viewerSession,
                    'following'         => $following,
                    'reel_size'         => $reelsize,
                    'reel_position'     => $cnt,
                 ]
            );

            $ig->event->sendOrganicVpvdImpression($storyItem,
                 [
                    'tray_session_id'       => $traySession,
                    'viewer_session_id'     => $viewerSession,
                    'following'             => $following,
                    'reel_size'             => $reelsize,
                    'reel_position'         => $cnt,
                    'client_sub_impression' => 1,
                 ],
                 'reel_profile'
            );

            $ig->event->sendOrganicReelImpression($storyItem, $viewerSession, $traySession, $rankToken, true, 'reel_profile');
            $ig->event->sendOrganicMediaImpression($storyItem, 'reel_profile',
                [
                    'story_ranking_token'   => $rankToken,
                    'tray_session_id'       => $traySession,
                    'viewer_session_id'     => $viewerSession,
                ]
            );
            $ig->event->sendOrganicViewedImpression($storyItem, 'reel_profile', $viewerSession, $traySession, $rankToken);

            $cnt++;
        }
        sleep(mt_rand(1, 3));

        $ig->story->markMediaSeen($storyItems);
        $ig->event->sendReelPlaybackNavigation(end($storyItems), $viewerSession, $traySession, $rankToken, 'reel_liker_list');
        $ig->event->sendReelSessionSummary($item, $viewerSession, $traySession, 'reel_liker_list',
            [
                'tray_session_id'               => $traySession,
                'viewer_session_id'             => $viewerSession,
                'following'                     => $following,
                'reel_size'                     => $reelsize,
                'reel_position'                 => count($storyItems) - 1,
                'is_last_reel'                  => 1,
                'photos_consumed'               => $photosConsumed,
                'videos_consumed'               => $videosConsumed,
                'viewer_session_media_consumed' => count($storyItems),
            ]
        );
        $ig->event->sendNavigation('back', 'profile', 'unified_follow_lists');

        sleep(2);
    }

    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
