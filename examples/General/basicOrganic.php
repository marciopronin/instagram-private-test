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
            if ($storyItem->getMediaType() == 2) {
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
                    'viewer_session_id'     => $viewerSession,
                ]
            );
            $ig->event->sendOrganicViewedImpression($storyItem, 'reel_feed_timeline', $viewerSession, $traySession, $rankToken);

            $cnt++;
        }

        sleep(mt_rand(1, 3));

        $ig->story->markMediaSeen($tray->getItems());
        $items = $tray->getItems();
        $ig->event->sendReelPlaybackNavigation(end($items), $viewerSession, $traySession, $rankToken);
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

    sleep(mt_rand(10, 40));
    $requestId = \InstagramAPI\Signatures::generateUUID();

    $maxId = null;
    $mediaDepth = 0;
    $tc = 0;
    do {
        $feed = $ig->timeline->getTimelineFeed($maxId);
        if ($maxId !== null) {
            $ig->event->sendEndMainFeedRequest($mediaDepth);
        }
        $maxId = $feed->getNextMaxId();
        $mediaDepth += count($feed->getFeedItems());

        foreach ($feed->getFeedItems() as $item) {
            if ($item->getMediaOrAd() !== null) {
                switch ($item->getMediaOrAd()->getMediaType()) {
                    case 1:
                        $ig->event->sendOrganicMediaImpression($item->getMediaOrAd(), 'feed_timeline');
                        break;
                    case 2:
                        $ig->event->sendOrganicViewedImpression($item->getMediaOrAd(), 'feed_timeline');
                        break;
                    case 8:
                        $carouselItem = $item->getMediaOrAd()->getCarouselMedia()[0]; // First item of the carousel.
                        if ($carouselItem->getMediaType() === 1) {
                            $ig->event->sendOrganicMediaImpression($item->getMediaOrAd(), 'feed_timeline',
                                [
                                    'feed_request_id'   => ($maxId === null) ? null : $requestId,
                                ]
                            );
                        } else {
                            $ig->event->sendOrganicViewedImpression($item->getMediaOrAd(), 'feed_timeline', null, null, null,
                                [
                                    'feed_request_id'   => ($maxId === null) ? null : $requestId,
                                ]
                            );
                        }
                        break;
                }
            }
            $previewComments = ($item->getMediaOrAd() === null) ? [] : $item->getMediaOrAd()->getPreviewComments();

            if ($previewComments !== null) {
                foreach ($previewComments as $comment) {
                    $ig->event->sendCommentImpression($item->getMediaOrAd(), $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount(), 'feed_timeline');
                }
            }
        }

        $ig->event->sendStartMainFeedRequest($mediaDepth);
        $ig->event->sendMainFeedLoadingMore(round(microtime(true) * 1000), $mediaDepth);
        $tc++;
        sleep(mt_rand(10, 30));
    } while ($maxId !== null && $tc < mt_rand(1, 3));

    $ig->event->updateAppState('background', 'feed_timeline');
    sleep(mt_rand(10, 20));
    $ig->event->forceSendBatch();
    sleep(mt_rand(180, 360));
    $ig->event->updateAppState('foreground', 'feed_timeline');

    // Explore and search session, will be used for the Graph API events.
    $searchSession = \InstagramAPI\Signatures::generateUUID();

    $topicData =
    [
        'topic_cluster_title'       => 'For You',
        'topic_cluster_id'          => 'explore_all:0',
        'topic_cluster_type'        => 'explore_all',
        'topic_cluster_session_id'  => $searchSession,
        'topic_nav_order'           => 0,
    ];

    $ig->event->sendNavigationTabClicked('main_home', 'main_search', 'feed_timeline');
    $ig->event->sendNavigation('main_search', 'feed_timeline', 'explore_popular', null, null, $topicData);
    $ig->discover->getNullStateDynamicSections();
    // Get explore feed sections and items.
    $maxId = null;
    $ec = 0;
    do {
        $exploreFeedResponse = $ig->discover->getExploreFeed('explore_all:0', $searchSession);
        $maxId = $exploreFeedResponse->getNextMaxId();
        $sectionalItems = $exploreFeedResponse->getSectionalItems();

        $ig->event->prepareAndSendExploreImpression('explore_all:0', $searchSession, $sectionalItems);
        $ec++;
        sleep(mt_rand(7, 20));
    } while ($maxId !== null && $ec < mt_rand(1, 6));

    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
