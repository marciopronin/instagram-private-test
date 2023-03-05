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
    $extraData['double_tap'] = 1;
    $extraData['logging_info_token'] = $item->getLoggingInfoToken();
    $ig->media->like($mediaId, 0, 'feed_short_url', false, $extraData);
    $ig->event->sendOrganicLike($item, 'feed_short_url', null, null, $ig->session_id);
    $ig->event->updateAppState('background');
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
