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
    $sessionId = \InstagramAPI\Signatures::generateUUID();
    $ig->event->sendNavigation('on_launch_direct_inbox', 'feed_timeline', 'direct_inbox');
    $ig->event->sendNavigationTabImpression(1);

    // Get Direct Inbox with Limit = 20. That limit is set by Instagram and tells the server
    // how many threads to return.
    $threads = $ig->direct->getInbox(null, null, 20)->getInbox()->getThreads();
    $ig->event->sendDirectInboxTabImpression();

    // In this example we will be iterating each thread to load all the items of each.
    foreach ($threads as $pos=>$thread) {
        $oldestCursor = null;
        $previous = null;

        $ig->event->sendDirectEnterThread($thread->getThreadId(), $thread->getUsers()[0]->getPk(), $pos);
        $ig->event->sendNavigation('inbox', 'direct_inbox', 'direct_thread');
        do {
            $previous = $oldestCursor;
            if ($oldestCursor !== null) {
                $ig->event->sendDirectFetchPagination('attempt', $oldestCursor);
            }

            // Oldest cursor is used to paginate. In the first iteration $oldestCursor is null, next
            // iteration it will take the oldest cursor from the response.
            $threadInbox = $ig->direct->getThread($thread->getThreadId(), $oldestCursor)->getThread();

            if ($oldestCursor !== null) {
                $ig->event->sendDirectFetchPagination('success', $oldestCursor);
            }

            $oldestCursor = $thread->getOldestCursor();
            // Get the items from the thread
            $threadItems = $threadInbox->getItems();

            foreach ($threadItems as $threadItem) {
                $ig->event->sendDirectThreadItemSeen($clientContext, $thread->getThreadId(), $threadItem, 'send_attempt');
                $ig->event->sendDirectThreadItemSeen($clientContext, $thread->getThreadId(), $threadItem, 'sent');
                // As an example we are only echoing some values of each item.
                echo 'Item ID: '.$threadItem->getItemId()."\n";
                echo 'Item Type: '.$threadItem->getItemType()."\n\n";
            }
            // We will stop the do-while loop when oldesct cursor is null or is the same as the previous one.
        } while ($oldestCursor !== null && $previous !== $oldestCursor);
        $ig->event->sendNavigation('back', 'direct_thread', 'direct_inbox');
    }
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
