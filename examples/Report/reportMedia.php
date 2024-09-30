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
    $feedItems = $ig->timeline->getTimelineFeed()->getFeedItems();
    $item = $feedItems[0]->getMediaOrAd();
    $ig->event->sendOrganicMediaImpression($item, 'feed_timeline');
    $ig->media->report($media->getId(), 'feed_timeline');
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
