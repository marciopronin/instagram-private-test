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
    $ig->event->sendNavigation('on_launch_direct_inbox', 'feed_timeline', 'direct_inbox');

    $seqId = null;
    $cursor = null;
    do {
        // Get Direct Inbox with Limit = 20. That limit is set by Instagram and tells the server
        // how many threads to return.
        $inboxResponse = $ig->direct->getInbox($cursor, $seqId, 20);
        $seqId = $inboxResponse->getSeqId();
        $inbox = $inboxResponse->getInbox();
        $cursor = $inbox->getNextCursor()->getCursorThreadV2Id();
        $threads = $inbox->getThreads();
        // In this example we will be iterating each thread to load all the items of each.
        foreach ($threads as $thread) {
            $oldestCursor = null;
            $previous = null;

            do {
                $previous = $oldestCursor;
                // Oldest cursor is used to paginate. In the first iteration $oldestCursor is null, next
                // iteration it will take the oldest cursor from the response.
                $threadInbox = $ig->direct->getThread($thread->getThreadId(), $oldestCursor)->getThread();
                $ig->event->sendNavigation('button', 'direct_inbox', 'direct_thread', null, null, ['thread_id' => $threadInbox->getThreadId()]);
                $oldestCursor = $thread->getOldestCursor();
                // Get the items from the thread
                $threadItems = $threadInbox->getItems();

                foreach ($threadItems as $threadItem) {
                    if ($threadItem->getType() === 'reaction') {
                        $ig->direct->hideThread($threadInbox->getThreadId());
                        break;
                    }
                }
                $ig->event->sendNavigation('back', 'direct_thread', 'direct_inbox');
                // We will stop the do-while loop when oldesct cursor is null or is the same as the previous one.
            } while ($oldestCursor !== null && $previous !== $oldestCursor);
        }
    } while($cursor !== null);

    $ig->event->sendNavigation('back', 'direct_inbox', 'feed_timeline');
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
