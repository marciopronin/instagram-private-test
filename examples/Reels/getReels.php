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
    // Explore and search session, will be used for the Graph API events.
    $searchSession = InstagramAPI\Signatures::generateUUID();

    $topicData =
    [
        'topic_cluster_title'       => 'For You',
        'topic_cluster_id'          => 'explore_all:0',
        'topic_cluster_type'        => 'explore_all',
        'topic_cluster_session_id'  => $searchSession,
        'topic_nav_order'           => 0,
    ];

    // Send navigation from 'feed_timeline' to 'explore_popular'.
    $ig->event->sendNavigation('main_search', 'feed_timeline', 'explore_popular', null, null, $topicData);

    // Send navigation from 'explore_popular' to 'explore_popular'.
    $ig->event->sendNavigation('explore_topic_load', 'explore_popular', 'explore_popular', null, null, $topicData);

    // Get explore feed sections and items.
    $sectionalItems = $ig->discover->getExploreFeed('explore_all:0', $searchSession)->getSectionalItems();
    $ig->event->prepareAndSendExploreImpression('explore_all:0', $searchSession, $sectionalItems);

    $reelSession = InstagramAPI\Signatures::generateUUID();

    $items = $ig->reel->discover()->getItems();
    $seenReels = [];
    $mediaInfo = [];
    $itemIds = [];
    $chainingMedia = $items[0]->getMedia()->getId();
    foreach ($items as $item) {
        $itemIds[] = $item->getMedia()->getPk();
        $seenReels[] = ['id' => $item->getMedia()->getPk()];
        $mediaInfo[$item->getMedia()->getId()] = [
            'total_watch_time_ms'   => [
                'value'                 => mt_rand(2000, 4000),
                'latest_play_end_ts'    => time(),
            ],
            'num_loops'             => [
                'value'                 => 0,
                'last_loop_end_ts'      => 0,
            ],
        ];
    }

    $sessionInfo = [
        'session_id'    => $reelSession,
        'media_info'    => $mediaInfo,
    ];

    // Send seen state.
    try {
        $ig->reel->sendSeenState($itemIds);
    } catch (Exception $e) {
        // pass
    }

    // Fetch new reels.
    $ig->reel->discover($chainingMedia, $seenReels, $sessionInfo);

    $ig->event->forceSendBatch();
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
