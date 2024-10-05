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

// ///// URL ////////
$url = '';
// /////////////////

$ig = new InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (Exception $e) {
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

    $commentInfos = $ig->media->getCommentInfos($item->getId())->getCommentInfos()->getData();

    foreach ($commentInfos as $key => $value) {
        $previewComments = $value->getPreviewComments();
        if ($previewComments !== null) {
            foreach ($previewComments as $comment) {
                $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
            }
        }
    }

    $ig->event->sendNavigation('button', 'feed_short_url', 'profile');
    $ig->people->getFriendship($userId);
    $ig->highlight->getUserFeed($userId);
    $ig->people->getInfoById($userId);
    $ig->story->getUserStoryFeed($userId);
    $userFeed = $ig->timeline->getUserFeed($userId);
    $items = $userFeed->getItems();

    $items = array_slice($items, 0, 6);
    $ig->event->preparePerfWithImpressions($items, 'profile');

    $traySession = InstagramAPI\Signatures::generateUUID();
    $ig->event->reelTrayRefresh(
        [
            'tray_session_id'   => $traySession,
            'tray_refresh_time' => number_format(mt_rand(100, 500) / 1000, 3),
        ],
        'network'
    );

    $ig->event->sendProfileView($userId);
    $ig->event->sendFollowButtonTapped($userId, 'profile');
    $ig->people->follow($userId);

    $ig->event->sendProfileAction(
        'follow',
        $userId,
        [
            [
                'module'        => 'feed_short_url',
                'click_point'   => 'button',
            ],
        ]
    );

    $rankToken = InstagramAPI\Signatures::generateUUID();
    $ig->event->sendSearchFollowButtonClicked($userId, 'profile', $rankToken);

    try {
        $chainingUsers = $ig->discover->getChainingUsers($userId, 'profile')->getUsers();

        foreach ($chainingUsers as $user) {
            $ig->event->sendSimilarUserImpression($userId, $user->getPk());
        }
    } catch (Exception $e) {
        // pass. No chaining.
    }

    $ig->event->updateAppState('background');
    $ig->event->forceSendBatch();
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
