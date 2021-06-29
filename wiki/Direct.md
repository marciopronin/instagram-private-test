# Direct messaging

### WARNING!!

If you are using Direct for sending messages, only first message should be sent using HTTP class, rest of messages MUST be sent using MQTT!! Using it in any other way increases the chances of the account to be flagged.

Realtime client now admit to send multiple type of messages, including:

- `sendLikeToDirect()`
- `sendPostToDirect()`
- `sendStoryToDirect()`
- `sendProfileToDirect()`
- `sendLocationToDirect()`
- `sendHashtagToDirect()`
- `sendReactionToDirect()`
- `deleteReactionFromDirect()`

See all functions in: `/src/Realtime.php`.

**NOTE:** Sending links still to be sent using direct HTTP.

# Direct events

## From timeline to Direct

`$ig->event->sendNavigation('on_launch_direct_inbox', 'feed_timeline', 'direct_inbox');`

## Searching a user in Direct 

```php
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
```

## Navigate to recently created thread

```php
$groupSession = \InstagramAPI\Signatures::generateUUID();
$ig->event->sendDirectUserSearchSelection($userId, $position, $groupSession); // search user selection
$ig->event->sendGroupCreation($groupSession);
$ig->event->sendNavigation('button', 'direct_inbox', 'direct_thread', null, null, ['user_id' => $userId]);
$ig->event->sendEnterDirectThread(null, $sessionId);
```

## Navigate to thread

```php
$ig->event->sendNavigation('button', 'direct_inbox', 'direct_thread', null, null, ['user_id' => $userId]);
$ig->event->sendEnterDirectThread(null, $sessionId);
```

## Sending a text message 

```php
$ig->event->sendDirectMessageIntentOrAttempt('send_intent', $clientContext, 'text');
$ig->event->sendTextDirectMessage();
$ig->event->sendDirectMessageIntentOrAttempt('send_attempt', $clientContext, 'text');
$ig->event->sendDirectMessageIntentOrAttempt('sent', $clientContext, 'text');
```

## Sending a image

```php
$ig->direct->sendPhoto($recipients, $photoFilename, ['client_context' => $clientContext]);
$ig->event->sendDirectMessageIntentOrAttempt('send_intent', $clientContext, 'visual_photo');
$ig->event->sendDirectMessageIntentOrAttempt('send_attempt', $clientContext, 'visual_photo');
$ig->event->sendDirectMessageIntentOrAttempt('sent', $clientContext, 'visual_photo');
```

## Sending a story reaction

```php
$ig->direct->sendStoryReaction($recipients, $reaction, $storyItems[0]->getId(), ['client_context' => $clientContext]);

$ig->event->sendDirectMessageIntentOrAttempt('send_intent', $clientContext, 'reel_share');
$ig->event->sendDirectMessageIntentOrAttempt('send_attempt', $clientContext, 'reel_share');
$ig->event->sendDirectMessageIntentOrAttempt('sent', $clientContext, 'reel_share');
```

## Navigating back to timeline

```php
$ig->event->sendNavigation('back', 'direct_thread', 'direct_inbox');
$ig->event->sendNavigation('back', 'direct_inbox', 'feed_timeline');
```

## Navigating to pending inbox

```php
$ig->event->sendNavigation('button', 'direct_inbox', 'pending_inbox');
```

## Emulating Instagram in background

```php
$ig->event->updateAppState('background', $module);
```

## Emulating coming back from background process 

```php
$ig->event->updateAppState('foreground', $module);
```

## RealtimeClient

### Quick answers

Quick answers are predefined answers for certain business accounts. These answers will be received using the RTC event: `thread-item-created`.

This is a sample of quick answers:

```json
[{"event":"patch","data":[{"op":"add","path":"/direct_v2/threads/THREAD_ID/items/ITEM_ID","value":"{"item_id": "ITEM_ID", "user_id": UID, "timestamp": TIMESTAMP, "item_type": "text", "text": "Text1.", "client_context": "CLIENT_CONTEXT", "show_forward_attribution": false, "is_shh_mode": false, "instant_reply_info": {"instant_replies": [{"title": "INSTANT_REPLY_1", "payload": "ACT::ACTID"}, {"title": "INSTANT_REPLY_2", "payload": "ACT::ACTID"}, {"title": "INSTANT_REPLY_3", "payload": "ACT::ACTID"}]}}"}],"message_type":1,"seq_id":2,"mutation_token":null,"realtime":true}]
```

### RTC notification events

It is possible to trigger an event when something has been updated or some notification is received through RTC. It is not possible to know which type of notification. In order to provide this functionality, the following lines of code must be added in `/Realtime/MQTT.php` in `_handleMessage()` function:

```php
if ($module == 'direct'){
    if (!empty($message->getData())) {
        $this->_target->emit('patch-empty-event', [$message->getData()]);
    }
}
```

In the `realtimeClient.php` example, the next lines should be added to be able to listen to this event:

```php
$this->_rtc->on('patch-empty-event', function () {
$this->_logger->info(sprintf('[RTC] RTC Event %s', PHP_EOL));

// THIS CAN BE USED TO LISTEN FOR NEW PENDING THREADS
});
```