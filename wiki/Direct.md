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