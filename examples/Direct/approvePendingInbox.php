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
    $clientContext = \InstagramAPI\Utils::generateClientContext();
    $ig->event->sendNavigation('on_launch_direct_inbox', 'feed_timeline', 'direct_inbox');

    $ig->direct->getInbox(null, null, 20);

    $pendingInboxThreads = $ig->direct->getPendingInbox()->getInbox()->getThreads();

    foreach ($pendingInboxThreads as $pendingInboxThread) {
        // Only approving first pending as an example
        $ig->direct->approvePendingThreads($pendingInboxThread->getThreadId());
        $threadItems = $pendingInboxThread->getItems();

        foreach ($threadItems as $threadItem) {
            $ig->direct->markItemSeen($pendingInboxThread->getThreadId(), $threadItem->getItemId(), $clientContext);
        }
    }
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
