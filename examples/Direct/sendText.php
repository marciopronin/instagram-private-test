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
    $clientContext = \InstagramAPI\Utils::generateClientContext();
    $sessionId = \InstagramAPI\Signatures::generateUUID();
    $ig->event->sendNavigation('on_launch_direct_inbox', 'feed_timeline', 'direct_inbox');

    // This is as replacement of search, repeating this request with organic typing would be better.
    $rankedRecipients = $ig->direct->getRankedRecipients('raven', true, $query)->getRankedRecipients();

    foreach ($rankedRecipients as $key => $value) {
        if ($value->getUser() !== null && $value->getUser()->getUsername() === $query) {
            $position = $key;
            $userId = $value->getUser()->getPk();
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
    $ig->event->sendEnterDirectThread(null, $sessionId);

    $recipients = [
        'users' => [
            $userId,
        ],
    ];

    $ig->direct->getThreadByParticipants([$userId]);
    $ig->direct->sendText($recipients, $text, ['client_context' => $clientContext]);
    $ig->event->sendDirectMessageIntentOrAttempt('send_intent', $clientContext, 'text');
    $ig->event->sendTextDirectMessage();
    $ig->event->sendDirectMessageIntentOrAttempt('send_attempt', $clientContext, 'text');
    $ig->event->sendDirectMessageIntentOrAttempt('sent', $clientContext, 'text');

    //$ig->event->sendNavigation('back', 'direct_thread', 'direct_inbox');
    //$ig->event->sendNavigation('back', 'direct_inbox', 'feed_timeline');
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
