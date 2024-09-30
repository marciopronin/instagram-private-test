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

    try {
        $ig->people->getNewsInboxSeen();
    } catch (Exception $e) {
        // ignore
    }
    $ig->event->sendNavigation('button', 'feed_timeline', 'newsfeed_you');
    $ig->people->getRecentActivityInbox(false, false);
    $ig->event->sendNavigation('button', 'feed_timeline', 'newsfeed_you');
    $ig->event->sendNavigation('button', 'newsfeed_you', 'discover_people');

    $recommendedAccounts = $ig->discover->getRecommendedAccounts()->getCategories()[0]->getSuggestions()->getGroups()[0]->getItems();
    $ig->people->unlinkAddressBook(false);
    foreach ($recommendedAccounts as $key => $user) {
        $ig->people->follow($user->getUser()->getPk());
        $ig->event->sendRecommendedFollowButtonTapped($user->getUser()->getPk(), 'discover_people', $key, true, 'fullscreen', $user->getSocialContext());
        $ig->event->sendFollowButtonTapped($user->getUser()->getPk(), 'discover_people');
    }
    $ig->event->forceSendBatch();
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}
