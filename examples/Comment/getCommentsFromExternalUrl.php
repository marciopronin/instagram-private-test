<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

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
    $ig->event->updateAppState('foreground');
    $mediaId = $ig->request(sprintf('oembed/?url=%s', urlencode($url)))
                ->getDecodedResponse(false)
                ->media_id;
    $item = $ig->media->getInfo($mediaId)->getItems()[0];
    $extraData = [];
    if ($item->getMediaType() === \InstagramAPI\Response\Model\Item::CAROUSEL) {
        $extraData['carousel_media'] = 1;
        $extraData['carousel_index'] = 0;
    }
    $ig->event->sendOrganicMediaImpression($item, 'feed_short_url', $extraData);
    $ig->media->getCommentInfos($item->getId());

    $previewComments = $item->getPreviewComments();
    if ($previewComments !== null) {
        foreach ($previewComments as $comment) {
            $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
        }
    }

    $ig->event->sendNavigation('button', 'feed_contextual_profile', 'comments_v2', null, null, ['user_id' => $comment->getUserId()]); // navigating to comments v2 feed
    $comments = $ig->media->getComments($item->getId())->getComments(); // getting comments. You can expand this paginating in order to see all comments
    $c = 0;
    foreach ($comments as $comment) {
        $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
        if ($c === 5) { // You can skip this and iterate all of them
            break;
        }
        $c++;
    }

    $ig->event->updateAppState('background');
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
