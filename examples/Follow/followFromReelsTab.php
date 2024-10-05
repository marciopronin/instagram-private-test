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
    $ig->event->sendNavigation('main_clips', 'feed_timeline', 'clips_viewer_clips_tab');

    $sessionId = InstagramAPI\Signatures::generateUUID();

    $discoverStream = $ig->reel->discover();
    $streamClips = $discoverStream->getItems();

    $seenReels = [];
    $seenReels[] = $streamClips[0]->getMedia()->getId();
    $seenReels[] = $streamClips[1]->getMedia()->getId();

    $mediaInfo = [];
    foreach ($seenReels as $idx => $seenReel) {
        $ig->event->sendOrganicMediaImpression($streamClips[$idx]->getMedia(), 'clips_viewer_clips_tab');
        $watchtime = ($streamClips[$idx]->getMedia()->getVideoDuration() * 1000) - mt_rand(1000, 3000);
        $mediaInfo[] = [
            $seenReel   => [
                'total_watch_time_ms'   => $watchtime,
                'latest_play_end_ts'    => time() - $watchtime,
            ],
        ];
        $ig->event->sendOrganicViewedSubImpression($streamClips[$idx]->getMedia(), null, null, ['following' => false], 'clips_viewer_clips_tab');
        $ig->event->sendOrganicMediaImpression($streamClips[$idx]->getMedia(), 'clips_viewer_clips_tab');
        $ig->event->sendOrganicViewedImpression($streamClips[$idx]->getMedia(), 'clips_viewer_clips_tab', null, null, null, ['following' => false]);
        $ig->event->sendOrganicVpvdImpression($streamClips[$idx]->getMedia(), ['following' => false], 'clips_viewer_clips_tab');
    }

    $sessionInfo = [
        'session_id'    => $sessionId,
        'media_info'    => $mediaInfo,
    ];

    $clips = $ig->reel->discover(null, $seenReels, $sessionInfo, $discoverStream->getPagingInfo()->getMaxId());

    $userId = $clips->getItems()[0]->getMedia()->getPk();

    $ig->event->sendFollowButtonTapped($userId, 'clips_viewer_clips_tab');
    $ig->people->follow($userId);

    $ig->event->sendProfileAction(
        'follow',
        $userId,
        [
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_clips',
            ],
        ]
    );
    $ig->event->forceSendBatch();
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
