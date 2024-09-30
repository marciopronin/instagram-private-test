<?php

/*
 * IMPORTANT!
 * You need https://github.com/Seldaek/monolog to run this example:
 * $ composer require monolog/monolog
 *
 * Also, if you have a 32-bit PHP build, you have to enable the GMP extension:
 * http://php.net/manual/en/book.gmp.php
 */

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../../vendor/autoload.php';

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

$loop = React\EventLoop\Factory::create();
if ($debug) {
    $logger = new Monolog\Logger('rtc');
    $logger->pushHandler(new Monolog\Handler\StreamHandler('php://stdout', Monolog\Logger::INFO));
} else {
    $logger = null;
}

$inboxResponse = $ig->direct->getInbox();

$rtc = new InstagramAPI\Realtime($ig, $loop, $logger);

$rtc->on('connect', function () use ($rtc, $inboxResponse) {
    $rtc->receiveOfflineMessages(
        $inboxResponse->getSeqId(),
        $inboxResponse->getSnapshotAtMs()
    );

    $threads = $inboxResponse->getThreads();
    $ig->event->sendDirectInboxTabImpression();

    // In this example we will be iterating each thread to load all the items of each.
    $clientContext = InstagramAPI\Utils::generateClientContext();
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
            $rtc->sendTextToDirect($thread->getThreadId(), $autoMessage);
            $ig->event->sendDirectMessageIntentOrAttempt('send_intent', $clientContext, 'text', [$thread->getUsers()[0]->getPk()]);
            $ig->event->sendTextDirectMessage();
            $ig->event->sendDirectMessageIntentOrAttempt('send_attempt', $clientContext, 'text', [$thread->getUsers()[0]->getPk()]);
            $ig->event->sendDirectMessageIntentOrAttempt('sent', $clientContext, 'text', [$thread->getUsers()[0]->getPk()]);
        }
        $ig->event->sendNavigation('back', 'direct_thread', 'direct_inbox');
        sleep(mt_rand(1, 3));
    }
    $ig->event->sendNavigation('back', 'direct_inbox', 'feed_timeline');
});

$rtc->on('error', function (Exception $e) use ($rtc, $loop) {
    printf('[!!!] Got fatal error from Realtime: %s%s', $e->getMessage(), PHP_EOL);
    $rtc->stop();
    $loop->stop();
});
$rtc->start();

$loop->run();
