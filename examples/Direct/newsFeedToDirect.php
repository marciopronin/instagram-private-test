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
$text = 'Example';
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $item = $ig->timeline->getTimelineFeed()->getFeedItems()[0]->getMediaOrAd();
    $ig->event->sendBadgingEvent();
    $ig->event->sendNavigation('main_inbox', 'feed_timeline', 'newsfeed_you');
    $ig->event->sendOrganicTimespent($item, true, mt_rand(1000, 2000), 'feed_timeline');

    try {
        $ig->people->getNewsInboxSeen();
    } catch (Exception $e) {
        //pass
    }
    $inboxItems = $ig->people->getRecentActivityInbox()->getAymf()->getItems();
    foreach ($inboxItems as $key => $value) {
        if (strpos($value->getSocialContext(), 'Followed by') !== false) {
            $ig->event->sendRecommendedUserImpression($value, $key, 'newsfeed_you');
        }
    }

    $ig->event->sendNavigation('main_home', 'newsfeed_you', 'feed_timeline');
    $ig->event->sendOrganicTimespent($item, true, mt_rand(1000, 2000), 'feed_timeline');
    $clientContext = \InstagramAPI\Utils::generateClientContext();
    $ig->event->sendNavigation('on_launch_direct_inbox', 'feed_timeline', 'direct_inbox');
    $users = $ig->people->search($query, [], null, 'direct_recipient_list_page')->getUsers();

    foreach ($users as $key => $value) {
        if ($value->getUsername() === $query) {
            $position = $key;
            $userId = $value->getPk();
            break;
        }
    }

    $ig->event->sendDirectUserSearchPicker($query);
    $ig->event->sendDirectUserSearchPicker($query);
    $ig->event->sendDirectUserSearchPicker($query);

    $groupSession = \InstagramAPI\Signatures::generateUUID();
    $ig->event->sendDirectUserSearchSelection($userId, $position, $groupSession);
    $ig->event->sendGroupCreation($groupSession);
    $ig->event->sendNavigation('button', 'direct_inbox', 'direct_thread', null, null, ['user_id' => $userId]);
    $ig->event->sendEnterDirectThread(null);

    $recipients = [
        'users' => [
            $userId,
        ],
    ];

    $ig->direct->sendText($recipients, $text, ['client_context' => $clientContext]);
    $ig->event->sendDirectMessageIntentOrAttempt('send_intent', $clientContext, 'text');
    $ig->event->sendTextDirectMessage();
    $ig->event->sendDirectMessageIntentOrAttempt('send_attempt', $clientContext, 'text');
    $ig->event->sendDirectMessageIntentOrAttempt('sent', $clientContext, 'text');
    $ig->event->sendNavigation('back', 'direct_thread', 'direct_inbox');
    $ig->event->sendNavigation('back', 'direct_inbox', 'feed_timeline');
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
