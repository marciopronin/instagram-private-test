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
    $ig->event->sendNavigationTabClicked('main_inbox', 'feed_timeline', 'newsfeed_you');
    $ig->event->sendNavigation('main_inbox', 'feed_timeline', 'newsfeed_you');

    $suggestions = $ig->people->discoverPeople()->getSuggestedUsers()->getSuggestions();

    for ($i = 0; $i < 5; $i++) {
        $ig->people->follow($suggestions[$i]->getUser()->getPk());
        // Pending event.
        $ig->event->sendProfileAction(
            'follow',
            $suggestions[$i]->getUser()->getPk(),
            [
                [
                    'module'        => 'feed_timeline',
                    'click_point'   => 'main_inbox',
                ],
            ]
        );
    }

    $ig->event->forceSendBatch();
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
