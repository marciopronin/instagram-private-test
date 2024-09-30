<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

// ///// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
// ////////////////////

// ///////////////////
$autoMessage = 'Thank you for contacting us! We will get back to you as soon as possible :)';
// //////////////////

$ig = new InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $clientContext = InstagramAPI\Utils::generateClientContext();
    $ig->event->sendNavigation('on_launch_direct_inbox', 'feed_timeline', 'direct_inbox');
    $ig->event->sendNavigationTabImpression(1);

    // Get Direct Inbox with Limit = 20. That limit is set by Instagram and tells the server
    // how many threads to return.
    $threads = $ig->direct->getInbox(null, null, 20)->getInbox()->getThreads();
    $ig->event->sendDirectInboxTabImpression();

    // In this example we will be iterating each thread to load all the items of each.
    foreach ($threads as $pos=>$thread) {
        $ig->event->sendDirectEnterThread($thread->getThreadId(), $thread->getUsers()[0]->getPk(), $pos);
        $ig->event->sendNavigation('inbox', 'direct_inbox', 'direct_thread');

        if ($thread->getUnseenCount() > 0) {
            $recipients = [
                'thread' => [
                    $thread->getThreadId(),
                ],
            ];

            sleep(mt_rand(4, 7));
            $ig->direct->sendText($recipients, $autoMessage, ['client_context' => $clientContext]);
            $ig->event->sendDirectMessageIntentOrAttempt('send_intent', $clientContext, 'text', [$thread->getUsers()[0]->getPk()]);
            $ig->event->sendTextDirectMessage();
            $ig->event->sendDirectMessageIntentOrAttempt('send_attempt', $clientContext, 'text', [$thread->getUsers()[0]->getPk()]);
            $ig->event->sendDirectMessageIntentOrAttempt('sent', $clientContext, 'text', [$thread->getUsers()[0]->getPk()]);
        }
        $ig->event->sendNavigation('back', 'direct_thread', 'direct_inbox');
        sleep(mt_rand(1, 3));
    }
    $ig->event->sendNavigation('back', 'direct_inbox', 'feed_timeline');
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
