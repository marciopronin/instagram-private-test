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
$queryUser = 'selenagomez'; // :)
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    // Starting at "null" means starting at the first page.
    $maxId = null;
    do {
        $userFeed = $ig->timeline->getTimelineFeed($maxId); // Pagination with maxId
        $items = $userFeed->getItems();

        $c = 0;
        foreach ($items as $item) {
            if ($c === 5) {
                break;
            }
            $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $item, 'profile');
            $c++;
        }

        $ig->event->sendNavigation('button', 'profile', 'feed_contextual_profile'); // Navigating to feed contextual profile (user feed)

        foreach ($item as $items) {
            $ig->event->sendOrganicMediaImpression($item, 'feed_contextual_profile');
            $commentInfos = $ig->media->getCommentInfos($item->getId())->getCommentInfos()->getData(); // comment previews (event)
            $ig->event->sendOrganicNumberOfLikes($item, 'feed_contextual_profile'); // number of likes of media (event)

            foreach ($commentInfos as $key => $value) {
                $previewComments = $value->getPreviewComments();
                if ($previewComments !== null) {
                    foreach ($previewComments as $comment) {
                        $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount()); // sending comment impression (event)
                    }
                }
            }

            $ig->event->sendNavigation('button', 'feed_contextual_profile', 'comments_v2', null, null, ['user_id' => $userId]); // navigating to comments v2 feed
            $comments = $ig->media->getComments($item->getId())->getComments(); // getting comments. You can expand this paginating in order to see all comments
            $c = 0;
            foreach ($comments as $comment) {
                $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
                if ($c === 5) { // You can skip this and iterate all of them
                    break;
                }
                $c++;
            }
        }
        $maxId = $userFeed->getNextMaxId();
    } while ($maxId !== null); // Must use "!==" for comparison instead of "!=".

    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
