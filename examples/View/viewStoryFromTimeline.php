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
    $trays = $ig->story->getReelsTrayFeed()->getTray();
    $ig->event->sendNavigation('button', 'feed_timeline', 'reel_feed_timeline');

    $viewerSession = \InstagramAPI\Signatures::generateUUID();
    $traySession = \InstagramAPI\Signatures::generateUUID();
    $rankToken = \InstagramAPI\Signatures::generateUUID();

    foreach ($trays as $tray) {
        if ($tray->getItems() === null) {
            continue;
        }

        $ig->event->sendReelPlaybackEntry($tray->getUser()->getPk(), $viewerSession, $traySession, 'reel_feed_timeline');

        $reelsize = count($tray->getItems());
        $cnt = 0;
    
        $photosConsumed = 0;
        $videosConsumed = 0;
    
        foreach ($tray->getItems() as $storyItem) {
    
            if($storyItem->getMediaType() == 2) {
                $videosConsumed++;
            } else {
                $photosConsumed++;
            }
    
            $ig->event->sendOrganicMediaSubImpression($storyItem,
                [
                    'tray_session_id'   => $traySession, 
                    'viewer_session_id' => $viewerSession,
                    'following'         => true,
                    'reel_size'         => $reelsize,
                    'reel_position'     => $cnt,
                ], 
                'reel_feed_timeline'
            );
    
            $ig->event->sendOrganicViewedSubImpression($storyItem, $viewerSession, $traySession,
                [
                    'tray_session_id'   => $traySession, 
                    'viewer_session_id' => $viewerSession,
                    'following'         => true,
                    'reel_size'         => $reelsize,
                    'reel_position'     => $cnt,
                ],
                'reel_feed_timeline'
            );
    
            $ig->event->sendOrganicTimespent($storyItem, true, mt_rand(1000, 2000), 'reel_feed_timeline', [],
                 [
                    'tray_session_id'   => $traySession, 
                    'viewer_session_id' => $viewerSession,
                    'following'         => true,
                    'reel_size'         => $reelsize,
                    'reel_position'     => $cnt,
                 ]
            );
    
            $ig->event->sendOrganicVpvdImpression($storyItem,
                 [
                    'tray_session_id'       => $traySession, 
                    'viewer_session_id'     => $viewerSession,
                    'following'             => true,
                    'reel_size'             => $reelsize,
                    'reel_position'         => $cnt,
                    'client_sub_impression' => 1,
                 ],
                 'reel_feed_timeline'
            );
    
            $ig->event->sendOrganicReelImpression($storyItem, $viewerSession, $traySession, $rankToken, true, 'reel_feed_timeline');
            $ig->event->sendOrganicMediaImpression($storyItem, 'reel_feed_timeline', 
                [
                    'story_ranking_token'   => $rankToken, 
                    'tray_session_id'       => $traySession, 
                    'viewer_session_id'     => $viewerSession
                ]
            );
            $ig->event->sendOrganicViewedImpression($storyItem, 'reel_feed_timeline', $viewerSession, $traySession, $rankToken);
    
            $cnt++;
        }

        sleep(mt_rand(1, 3));
    
        $ig->story->markMediaSeen($tray->getItems());
        $ig->event->sendReelPlaybackNavigation(end($tray->getItems()), $viewerSession, $traySession, $rankToken);
        $ig->event->sendReelSessionSummary($storyItem, $viewerSession, $traySession, 'reel_feed_timeline',
            [
                'tray_session_id'               => $traySession, 
                'viewer_session_id'             => $viewerSession,
                'following'                     => true,
                'reel_size'                     => $reelsize,
                'reel_position'                 => count($tray->getItems()) - 1,
                'is_last_reel'                  => 1,
                'photos_consumed'               => $photosConsumed,
                'videos_consumed'               => $videosConsumed,
                'viewer_session_media_consumed' => count($tray->getItems()),
            ]
        );
    }

    $ig->event->sendNavigation('back', 'reel_feed_timeline', 'feed_timeline');
    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
