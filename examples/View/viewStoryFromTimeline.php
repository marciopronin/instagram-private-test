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
    $storyItems = [];

    foreach ($trays as $tray) {
        if ($tray->getItems() === null) {
            continue;
        }
        foreach ($tray->getItems() as $item) {
            $storyItems[] = $item;
        }
    }

    if (empty($storyItems)) {
        echo 'No timeline stories';
        exit();
    }

    $ig->event->sendNavigation('button', 'feed_timeline', 'reel_feed_timeline');

    $viewerSession = \InstagramAPI\Signatures::generateUUID();
    $traySession = \InstagramAPI\Signatures::generateUUID();
    $rankToken = \InstagramAPI\Signatures::generateUUID();

    foreach ($storyItems as $storyItem) {
        $ig->event->sendOrganicReelImpression($storyItem, $viewerSession, $traySession, $rankToken, true, 'reel_feed_timeline');
        $ig->event->sendOrganicMediaImpression($storyItem, 'reel_feed_timeline', ['story_ranking_token' => $rankToken, 'tray_session_id' => $traySession, 'viewer_session_id' => $viewerSession]);

        sleep(mt_rand(1, 3));

        $ig->story->markMediaSeen([$storyItem]);
        $ig->event->sendOrganicViewedImpression($storyItem, 'reel_feed_timeline', $viewerSession, $traySession, $rankToken);
    }

    $ig->event->sendReelPlaybackNavigation(end($storyItems), $viewerSession, $traySession, $rankToken);

    $ig->event->sendNavigation('back', 'reel_feed_timeline', 'feed_timeline');
    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
