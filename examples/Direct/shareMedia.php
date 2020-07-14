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

//////////////////////
$query = '';
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $clientContext = \InstagramAPI\Utils::generateClientContext();
    $ig->event->sendNavigation('main_profile', 'feed_timeline', 'self_profile');

    $items = $ig->timeline->getSelfUserFeed()->getItems();
    $c = 0;
    foreach ($items as $item) {
        $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $item, 'self_profile');
        $c++;
        if ($c === 6) {
            break;
        }
    }
    $ig->event->sendThumbnailImpression('instagram_thumbnail_click', $items[0], 'self_profile');
    $ig->event->sendNavigation('button', 'self_profile', 'feed_contextual_self_profile');

    $ig->event->sendOrganicMediaImpression($items[0], 'feed_contextual_self_profile');
    $previewComments = $items[0]->getPreviewComments();
    if ($previewComments !== null) {
        foreach ($previewComments as $comment) {
            $ig->event->sendCommentImpression($items[0], $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
        }
    }

    $ig->event->sendOrganicShareButton($items[0], false, 'feed_contextual_self_profile');
    $ig->event->sendExternalShareOption($items[0]->getPk());
    $users = $ig->direct->getRankedRecipients('reshare', true, $query)->getRankedRecipients();

    foreach ($users as $key => $value) {
        $value = $value->getUser();
        if ($value->getUsername() === $query) {
            $position = $key;
            $userId = $value->getPk();
            break;
        }
    }

    $ig->event->sendDirectUserSearchPicker($query);
    $ig->event->sendDirectUserSearchPicker($query);
    $ig->event->sendDirectUserSearchPicker($query);

    $ig->event->sendDirectUserSearchSelection($userId, $position, null, $query, 'direct_reshare_sheet');

    $recipients = [
        'users' => [
            $userId,
        ],
    ];
    if ($items[0]->getMediaType() === 1) {
        $mediaType = 'photo';
    } else {
        $mediaType = 'video';
    }

    $ig->direct->sendPost($recipients, $items[0]->getId(), ['client_context' => $clientContext, 'media_type' => $mediaType]);
    $ig->event->sendDirectShareMedia($userId);
    $ig->event->sendDirectMessageIntentOrAttempt('send_intent', $clientContext, 'share_media');
    $ig->event->sendDirectMessageIntentOrAttempt('send_attempt', $clientContext, 'share_media');
    $ig->event->sendDirectMessageIntentOrAttempt('sent', $clientContext, 'share_media');

    //$ig->event->sendNavigation('back', 'direct_thread', 'direct_inbox');
    //$ig->event->sendNavigation('back', 'direct_inbox', 'feed_timeline');
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
