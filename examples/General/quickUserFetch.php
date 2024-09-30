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

// ///// USER TO CHECK ////////
$userToCheck = '';
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
    $ig->request(sprintf('https://instagram.com/%s', urlencode($userToCheck)));

    $ig->event->sendNavigation('button', 'feed_short_url', 'profile');
    $userInfo = $ig->people->getInfoByName($userToCheck);
    $userId = $userInfo->getUser()->getPk();

    $userFeed = $ig->timeline->getUserFeed($userId);
    $items = $userFeed->getItems();

    $ig->event->updateAppState('background');
    $ig->event->forceSendBatch();

    echo $items->asJson(); // Feed items
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
