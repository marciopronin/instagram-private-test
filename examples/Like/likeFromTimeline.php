<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

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
    // WARNING: This SHOULD ONLY be used when you are following the
    //          user already.

    // Loading timeline feed
    $feedItems = $ig->timeline->getTimelineFeed()->getFeedItems();
    $item = $feedItems[0]->getMediaOrAd();
    // In this case we are going to like the first item, if you are going to like any other
    // item, you should also send organic media impressios to all "viewed" medias.
    $ig->event->sendOrganicMediaImpression($item, 'feed_timeline');

    $ig->media->getCommentInfos($item->getId());

    $ig->event->sendOrganicNumberOfLikes($item, 'feed_timeline');
    $previewComments = $item->getPreviewComments();
    if ($previewComments !== null) {
        foreach ($previewComments as $comment) {
            $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
        }
    }

    // Since we are going to like the first item of the media, the position in
    // the feed is 0. If you want to like the second item, it would position 1, and so on.
    $ig->media->like($item->getId(), 0, 'feed_timeline', false, ['logging_info_token' => $item->getLoggingInfoToken()]);
    // Send organic like from the 'feed_timeline' module.
    $ig->event->sendOrganicLike($item, 'feed_timeline', null, null, $ig->session_id);
    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
