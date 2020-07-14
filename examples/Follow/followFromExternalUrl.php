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

/////// URL ////////
$url = '';
///////////////////

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
    $userId = $item->getUser()->getPk();
    $ig->event->sendOrganicMediaImpression($item, 'feed_short_url');
    $ig->media->getCommentInfos($item->getId());

    $previewComments = $item->getPreviewComments();
    if ($previewComments !== null) {
        foreach ($previewComments as $comment) {
            $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
        }
    }

    $ig->event->sendNavigation('button', 'feed_short_url', 'profile');
    $ig->event->sendProfileView($userId);
    $ig->people->getFriendship($userId);
    $ig->highlight->getUserFeed($userId);
    $ig->people->getInfoById($userId);
    $ig->story->getUserStoryFeed($userId);
    $userFeed = $ig->timeline->getUserFeed($userId);
    $items = $userFeed->getItems();

    foreach ($items as $item) {
        $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $item, 'profile');
    }

    $ig->event->sendFollowButtonTapped($userId, 'profile',
        [
            [
                'module'        => 'feed_short_url',
                'click_point'   => 'button',
            ],
        ]
    );
    $ig->people->follow($userId);

    $ig->event->sendProfileAction('follow', $userId,
        [
            [
                'module'        => 'feed_short_url',
                'click_point'   => 'button',
            ],
        ]
    );
    $ig->event->updateAppState('background');
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
