<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $ig->event->sendNavigation('main_inbox', 'feed_timeline', 'newsfeed_you');

    $suggedtedUsers = $ig->people->getRecentActivityInbox()->getSuggestedUsers()->getSuggestionCards();

    $users = [];
    foreach ($suggedtedUsers as $suggestedUser) {
        $users[] = $suggestedUser->getUserCard()->getUser();
    }

    $userId = $users[0]->getPk();

    $ig->event->sendNavigation('button', 'newsfeed_you', 'profile');

    $ig->people->getFriendship($userId);
    $ig->highlight->getUserFeed($userId);
    $ig->people->getInfoById($userId);
    $ig->story->getUserStoryFeed($userId);
    $ig->event->sendProfileView($userId);
    $navstack = [
        [
            'module'        => 'newsfeed_you',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'feed_timeline',
            'click_point'   => 'main_inbox',
        ],
    ];
    $userFeed = $ig->timeline->getUserFeed($userId);
    $items = $userFeed->getItems();

    $items = array_slice($array, 0, 6);
    $ig->event->preparePerfWithImpressions($items, 'profile');

    $ig->event->sendFollowButtonTapped($userId, 'profile', $navstack);
    $ig->people->follow($userId);
    $ig->event->sendProfileAction('follow', $userId, $navstack);
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
