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

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $ig->event->sendNavigationTabClicked('main_home', 'main_profile', 'feed_timeline');
    $ig->event->sendNavigation('main_profile', 'feed_timeline', 'self_profile');

    $traySession = \InstagramAPI\Signatures::generateUUID();

    $ig->highlight->getSelfUserFeed();
    $ig->people->getSelfInfo();
    $userFeed = $ig->timeline->getSelfUserFeed();

    $ig->story->getArchiveBadgeCount();
    $items = $userFeed->getItems();

    $items = array_slice($items, 0, 6);
    $ig->event->preparePerfWithImpressions($items, 'self_profile');

    $navstack =
        [
            [
                'module'        => 'self_profile',
                'click_point'   => 'inferred_source',
            ],
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_profile',
            ],
            [
                'module'        => 'login',
                'click_point'   => 'cold start',
            ],
        ];

    $ig->event->sendNavigation('button', 'self_profile', 'account_discovery');

    $discoveryAccountsCategories = $ig->discover->getDiscoveryAccounts()->getCategories();
    foreach ($discoveryAccountsCategories as $category) {
        foreach ($category->getSuggestionCards() as $suggestionCard) {
            $suggestedUserId = $suggestionCard->getUserCard()->getUser()->getPk();
            $ig->people->follow($suggestedUserId);
            usleep(mt_rand(500000, 1500000));
        }
    }

    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
