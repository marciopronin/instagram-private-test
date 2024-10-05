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

$requestId = InstagramAPI\Signatures::generateUUID();

try {
    $maxId = null;
    $mediaDepth = 0;
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
                            $ig->event->sendOrganicMediaImpression(
                                $item->getMediaOrAd(),
                                'feed_timeline',
                                [
                                    'feed_request_id'   => ($maxId === null) ? null : $requestId,
                                ]
                            );
                        } else {
                            $ig->event->sendOrganicViewedImpression(
                                $item->getMediaOrAd(),
                                'feed_timeline',
                                null,
                                null,
                                null,
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
    } while ($maxId !== null);
    $ig->event->forceSendBatch();
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
