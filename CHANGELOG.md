# Stable release v17.10.5
## Date: 21/07/2021

### Updates & fixes

- **Event:** Update `sendDirectMessageIntentOrAttempt()`

### Examples

- Update shareMedia example

# Stable release v17.10.4
## Date: 21/07/2021

### Updates & fixes

- **Media:** Update `like()`
- **Request:** Update `_addDefaultHeaders()`

- **General update:** Remove `_csrftoken`. Deprecated. No longer used.

# Stable release v17.10.3
## Date: 20/07/2021

### Updates & fixes

- **Utils:** Update `throwIfInvalidStorySlider()`
- **Event:** Update `_addCommonProperties()`
- **Event:** Update `sendDirectMessageIntentOrAttempt()`
- **Event:** Update `_getModuleClass()`

### Examples

Update direct examples

# Stable release v17.10.2
## Date: 15/07/2021

### Updates & fixes

- **ServerMessageThrower:** Update `autoThrow()`
- **Response:** Update WebCheckpointResponse

# Stable release v17.10.1
## Date: 14/07/2021

### Updates & fixes

- **Instagram:** Update `_setUser()`

# Stable release v17.10.0
## Date: 12/07/2021

Instagram version: 195.0.0.31.123

## Critical update!
#### Update is highly recommended

### Updates & fixes

- **Checkpoint:** Update checkpoint with latest header authentication mechanism
- **Client:** Update Pigeon Session ID format (UFS)
- **Client:** Update `X-MID`
- **Constants:** Update to Instagram version 195.0.0.31.123
- **Debug:** Update `printResponse()`
- **Events:** Update `_sendBatchEvents()` to match latest events format.
- **Events:** Update `_getModuleClass()`
- **Events:** Update `_addEventBody()`
- **Events:** Update `_addBatchBody()`
- **Instagram:** Update `getEventsCompressedMode()`
- **Request:** Update `_addDefaultHeaders()`. Added new header `X-IG-Nav-Chain`
- **Settings:** Update StorageHandler. Added new persistent key `mid`

# Stable release v17.9.8
## Date: 09/07/2021

**WIP:** 195.0.0.31.123

### Critical update

- **Instagram:** Update `_setUser()` to use Authorization header if set.
- **Client:** Update `updateFromCurrentSettings()`

# Stable release v17.9.7
## Date: 09/07/2021

**WIP:** 195.0.0.31.123

### Critical update

- **Instagram:** Update `_setUser()` to use Authorization header if set.
- **Client:** Update `updateFromCurrentSettings()`

# Stable release v17.9.6
## Date: 08/07/2021

### Critical update

- **RealtimeConnection:** Update RTC credential.

### Updates and fixes

- **Internal:** Fixed typo in `_getTargetSegmentDuration()`
- **Update model and responses**

### Examples

- **Example:** Update checkpoint example

# Stable release v17.9.5
## Date: 06/07/2021

### Examples

- **Example:** Update likeFromUserFollowers example
- **Example:** Add likeFromExploreLocation example

# Stable release v17.9.4
## Date: 05/07/2021

### Updates and fixes

- **Internal:** WIP. Finishing reels feature.
- **Media constraints:** Added reels (clips) constraints.

### Documentation

- Update documentation

# Stable release v17.9.3
## Date: 02/07/2021

**NOTE:** If you are using any function to upload media. You should update to this release as soon as possible.

### Updates and fixes

- **Internal:** Update `configureSinglePhoto()`
- **Internal:** Update `configureSingleVideo()`
- **Responses:** Update `MusicGenresResponse` in order to match PSR-4

### Documentation

- Update documentation

# Stable release v17.9.2
## Date: 29/06/2021

### Wiki

- **Direc/Realtime:** Added documentation about how to handle quick answers and RTC notifications

### Tools

- **Frida:** Added frida script to debug MQTT communications from apk.

# Stable release v17.9.1
## Date: 26/06/2021

### Updates and fixes

- **Media:** Update `like()` and `likeComment()`.

# Stable release v17.9.0
## Date: 24/06/2021

### New features

The following functions will be used for X-IG-SALT-IDS header.

- **Client:** Added `incrementAndGetUserFlowCounter()`
- **Client:** Added `generateFlowId()`
- **Client:** Added `generateNewFlowId()`

# Stable release v17.8.12
## Date: 24/06/2021

### Updates and fixes

- **Instagram:** Update Exceptions in namespace with correct format.

# Stable release v17.8.11
## Date: 22/06/2021

### Updates and fixes

- **Instagram:** Update login flow. Prevents failure on IGTV feed.

# Stable release v17.8.10
## Date: 22/06/2021

### Updates and fixes

- **Instagram:** Hot fix. Update login flow

# Stable release v17.8.9
## Date: 21/06/2021

### Updates and fixes

- **Event:** Critical update! Fixed a bug that affected sessions chain and nav chains

# Stable release v17.8.8
## Date: 21/06/2021

### Updates and fixes

- **Instagram:** Hot fix. Update login flow

# Stable release v17.8.7
## Date: 21/06/2021

### Updates and fixes

- **Instagram:** Update login flow

# Stable release v17.8.6
## Date: 18/06/2021

### Updates and fixes

- Update User model

# Stable release v17.8.5
## Date: 17/06/2021

### Updates and fixes

- Update `approvePendingThreads()`

### Libraries

- **WIP:** Auto-patching tool: libliger.so

# Stable release v17.8.4
## Date: 14/06/2021

### Examples

- Update followFromSearchMultipleUsers

### Documentation

- Update documentation

# Stable release v17.8.3
## Date: 09/06/2021

### Examples

- Update likeFromMediaLikers
- Update likeFromUserFollowers

# Stable release v17.8.2
## Date: 09/06/2021

### Updates and fixes

- **Event:** Update `preparePerfWithImpressions()`
- **Event:** Update `_validateNavigationPath()`

### Examples

- Multiple fixes in examples.


# Stable release v17.8.1
## Date: 08/06/2021

### WIP

- Improving and updating new graph system

### Examples

- Improvements and cleanup

# Stable release v17.8.0
## Date: 04/06/2021

### New features

- **Event:** Add `sendIgtvPreviewEnd()`
- **Event:** Add `sendIgtvViewerAction()`

### Updates and fixes

- **Event:** Update `sendVideoAction()`

# Stable release v17.7.3
## Date: 03/06/2021

### Updates and fixes

- **Event:** Update `_validateNavigationPath()`

# Stable release v17.7.2
## Date: 02/06/2021

### Examples

- **Example:** Added 2FA Login via approved notification

# Stable release v17.7.1
## Date: 01/06/2021

### New features

- **Instagram:** Added `checkTrustedNotificationStatus()`. This function is used to check if 2FA was approved via notification in an already logged in device.

# Stable release v17.7.0
## Date: 31/05/2021

### New features

- **Event:** Added `preparePerfWithImpressions()`

### Updates and fixes

- Applied codestyle

### Examples

- **Example:** Added goToProfileFromExternalUrl

### Documentation

- Update documentation

# Stable release v17.6.7
## Date: 27/05/2021

### Updates and fixes

- **Direct:** Update `_sendDirectItems()`
- **Direct:** Update `_sendDirectItem()`

# Stable release v17.6.6
## Date: 26/05/2021

### Updates and fixes

- **ServerMessageThrower:** Update `autoThrow()`. Fixes an issue managing exceptions when 2FA is required.

# Stable release v17.6.5
## Date: 24/05/2021

### Updates and fixes

- Update Dockerfile

### Examples

- Update examples

### Documentation

- Update documentation

# Stable release v17.6.4
## Date: 21/05/2021

### Updates and fixes

- **Constants:** Update Constants
- **ServerMessageThrower:** Update class thrower for new challenges
- **Checkpoint:** Update checkpoint class and models

# Stable release v17.6.3
## Date: 18/05/2021

### Updates and fixes

- **Constants:** Update Instagram version to 187.0.0.32.120
- **Event:** Update nav chain classes.

# Stable release v17.6.2
## Date: 15/05/2021

### Updates and fixes

- **Event:** Update `_validateNavigationPath()`

# Stable release v17.6.1
## Date: 14/05/2021

### Updates and fixes

- **Event:** Update `_validateNavigationPath()`

### Example

- **Example:** Add likeFromSelfFollowers
- **Example:** Add likeFromSelfFollowings
- **Example:** Update removeFollowings
- **Example:** Update removeFollowers
- **Example:** Update blockUser

# Stable release v17.6.0
## Date: 12/05/2021

### New features

- **Event:** Add `sendUserReport()`

### Updates and fixes

- **Event:** Update `sendProfileAction()`

### Examples

- **Example:** Update removeFollowings
- **Example:** Add blockUser

# Stable release v17.5.2
## Date: 11/05/2021

### Updates and fixes

- **Event:** Update `sendProfileAction()`
- **Event:** Update `_validateNavigationPath()`

### Examples

- **Example:** Add followFromExternalUrlProfile
- **Example:** Update followFromSearch.
- **Example:** Update followFromSearchMultipleUsers.

# Stable release v17.5.1
## Date: 10/05/2021

### Updates and fixes

- **Instagram:** Update `finishTwoFactorLogin()`. `waterfall_id` param has been included to provide consistent data along with events.
- **Instagram:** Added public property loginWaterfallId

### Examples

- **Example:** Added followFromSearchMultipleUsers.

### Documentation

- **Documentation:** Update doc

# Stable release v17.5.0
## Date: 06/05/2021

### New features

- **Live:** Added `getArchivedLives()`
- **Live:** Added `deleteArchivedLive()`

### Updates and fixes

- **Model:** Update Item model
- **Model:** Update Broadcast model

# Stable release v17.4.1
## Date: 06/05/2021

### Updates and fixes

- **Event:** Upddate `sendNavigationTabClicked()`

### Examples

- **Example:** Update followFromSearch

### Documentation

- Update documentation

# Stable release v17.4.0
## Date: 06/05/2021

### New features

- **Live:** Get and set live settings. Now it is possible to archive/unarchive live streams.

### Responses

- Added LiveArchiveSettingsResponse 

# Stable release v17.3.14
## Date: 04/05/2021

### Updates and fixes

- **Utils:** Update `throwIfInvalidStoryCountdown()`

# Stable release v17.3.13
## Date: 04/05/2021

### Updates and fixes

- **Utils:** Update `throwIfInvalidStoryCountdown()`

### Examples

- **Example:** Update uploadStoryCountdown

# Stable release v17.3.12
## Date: 30/04/2021

### Examples

- **Example:** Update goToUserProfileExample
- **Example:** Add sendAudio for Direct

# Stable release v17.3.11
## Date: 29/04/2021

### Updates and fixes

- **Events:** Update nav chain classes
- **Events:** Update `sendPerfPercentPhotosRendered()`

### Examples

- **Example:** Update editProfileExample
- **Example:** Add goToUserProfileExample

# Stable release v17.3.10
## Date: 26/04/2021

### Updates and fixes

- **Constants:** Update version to 184.0.0.30.117
- **Events:** Update navigation chain classes
- **Utils:** Update extract URL for sending direct texts

# Stable release v17.3.9
## Date: 23/04/2021

### Examples

- Update likeFromMediaLikers example

# Stable release v17.3.8
## Date: 21/04/2021

### Examples

- Update viewStoryFromTimeline example

# Stable release v17.3.7
## Date: 21/04/2021

### Examples

- Update story view examples.

# Stable release v17.3.6
## Date: 20/04/2021

### Updates and fixes

- **Internal:** Fixed an issue in `_getPhotoUploadParams()` when uploading videos to IGTV feed.

### Examples

- Update uploadVideoIGTVWithThumbnail example.

# Stable release v17.3.5
## Date: 19/04/2021

### Updates and fixes

- **Event:** Update several functions related with story events. Nnow full story emulation is achieved.

### Examples

- Story related examples has been updated with missing events.

# Stable release v17.3.4
## Date: 16/04/2021

### Updates and fixes

- **Event:** Update path log settings preference

# Stable release v17.3.3
## Date: 16/04/2021

### Updates and fixes

- **Client:** Update path log settings preference

# Stable release v17.3.2
## Date: 14/04/2021

### Updates and fixes

- **Instagram:** Update login flow

# Stable release v17.3.1
## Date: 14/04/2021

### Updates and fixes

- **Instagram:** Update login flow

# Stable release v17.3.0
## Date: 13/04/2021

### Updates and fixes

- **Instagram:** Added `loginWithEmailLink()`
- **Instagram:** Update `userLookup()`

### Events

- Added and update several events

### Documentation

- Update documentation

# Stable release v17.2.11
## Date: 12/04/2021

**WIP:** New android version will be pushed soon.
**WIP:** Proxification of MQTT and FBNS push.
**WIP:** Update stories statistics functionalities within the next hours.

### Events

- **Android strings:** Added a new list with all android strings for impressions.

# Stable release v17.2.10
## Date: 06/04/2021

### Updates and fixes

- **Instagram:** Update login flow events
- **People:** Update get followers function
- **Event:** Update navigation chains

# Stable release v17.2.9
## Date: 05/04/2021

### Updates and fixes

- **Event:** Improved event system to support multi batch events

# Stable release v17.2.8
## Date: 05/04/2021

### WIP

Working on `181.0.0.33.117` version and android strings classifier.

### Wiki

- Phising notices
- WIP

# Stable release v17.2.7
## Date: 31/03/2021

## WiP

Hot fix for disappearing `sessionn_id` in cookies.

### Updates and fixes

- **Codestyle:** Refactorization and code styling.

### Wiki

- **Direct:** Update direct documentation

# Stable release v17.2.6
## Date: 29/03/2021

### Updates and fixes

- **Internal:** Update `configureSinglePhoto()`. Story feed now supports product tags.

# Stable release v17.2.5
## Date: 27/03/2021

### Updates and fixes

- **Realtime:** Update RealtimeConnection

# Stable release v17.2.4
## Date: 26/03/2021

### Updates and fixes

- **Event:** Update navigations

### Examples

- **Live:** Update goLive example

# Stable release v17.2.3

### Updates and fixes

- **Event:** Update navigations and direct events

# Stable release v17.2.2

### Updates and fixes

- **Account:** Update `getPresenceStatus()`

# Stable release v17.2.1

### New feature

- **Instagram:** Added `setUserGuzzleOptions()` to let users pass additional options to the Guzzle Client.

# Stable release v17.2.0

### New feature

- **Instagram:** Added `setUserGuzzleOptions()` to let users pass additional options to the Guzzle Client.

# Stable release v17.1.3
## Date: 19/03/2021

### Updates and fixes

- **Client:** Update constructor.

# Stable release v17.1.2
## Date: 18/03/2021

### Updates and fixes

- **Client:** Added `curlDebug` global flag for enabling cURL debug.

# Stable release v17.1.1
## Date: 18/03/2021

### Updates and fixes

- **Client:** Added `curlDebug` global flag for enabling cURL debug.

# Stable release v17.1.0
## Date: 17/03/2021

### New feature

- **Account:** Add `getSyncedFacebookPagesIds()`

# Stable release v17.0.1
## Date: 16/03/2021

### Updates and fixes

- **Model:** Update User model

# Stable release v17.0.0
## Date: 16/03/2021

## Backward breaks

- **Event:** `sendEnterDirectThread()` fingerprint has changed. All examples were updated.

### New features

- **Event:** Add `sendGroupCreationEnter()`

### Updates and fixes

- **Event:** Update `sendDirectUserSearchSelection()`
- **Event:** Update `sendNavigation()`
- **Event:** Update `_getModuleClass()`

### Examples

Updated direct examples.

# Stable release v16.10.9
## Date: 15/03/2021

### Updates and fixes

- **Request:** Update `_buildHttpRequest()`
- **Model:** Update User model

# Stable release v16.10.8
## Date: 15/03/2021

### New features

- **Music:** Add `getMoods()` and `getGenres()`
- **Models and responses**

### Report

- **Important notes:** Addressing recent blocks from IG.

# Stable release v16.10.7
## Date: 09/03/2021

### Updates and fixes

- **LiveManager:** Remove unused function
- **Constants:** Update Android version to 177.0.0.30.119
- **Events:** Update `_getModuleClass()`

# Stable release v16.10.6
## Date: 08/03/2021

### Examples

- **LiveManager:** Update goLive
- **LiveManager:** Update auth utils

# Stable release v16.10.5
## Date: 03/03/2021

### Updates and fixes

- **Constants:** Update iOS version to 177.0.0.20.117

# Stable release v16.10.4
## Date: 01/03/2021

### Updates and fixes

- **Request:** Update `_addDefaultHeaders()`
- **Event:** Update `_getModuleClass()`.

# Stable release v16.10.3
## Date: 01/03/2021

### Updates and fixes

- **Event:** Update `_validateNavigationPath()`
- **Event:** Update `_getModuleClass()`. Do NOT use yet!
- **Instagram:** Fixed wrong default value in `finishTwoFactorLogin()`
- **Instagram:** Update constants to 176.0.0.38.116

# Stable release v16.10.2
## Date: 24/02/2021

### WIP

Working on version 176.0.0.38.116.

### Updates and fixes

- **Event:** Fixed typo in `sendIGTvNotificationPreference()`

# Stable release v16.10.1
## Date: 24/02/2021

### WIP

Working on version 176.0.0.38.116.

### Updates and fixes

- **Debug:** Update response log to avoid omitting full response.
- **Event:** Update `prepareAndSendThumbnailImpression()` to provide more info about new layout types.
- **Event:** Update `prepareAndSendExploreImpression()` to provide more info about new layout types.
- **Internal:** Update `uploadSingleVideo()`. Added a new catch for network exceptions.

# Stable release v16.10.0
## Date: 19/02/2021

### New features

- **Event:** Added `sendReelSessionSummary()`

### Examples

- **LiveManager:** Live manager has been updated for better 2FA support.

### Wiki

- **Wiki:** Update documentation for reel playback navigation.

# Stable release v16.9.4
## Date: 17/02/2021

### Wiki

- **Wiki:** Added documentation for reel playback navigation.

# Stable release v16.9.3
## Date: 16/02/2021

### Updates and fixes

- **Event:** Update `prepareAndSendExploreImpression()`. New layout type has been added.

# Stable release v16.9.2
## Date: 13/02/2021

### Examples

- **Example:** Update storyView. Now all story items are marked marked as seen at once

# Stable release v16.9.1
## Date: 08/02/2021

### Updates and fixes

- **Constants:** Update version to `172.0.0.21.123`
- **Events:** Update `_getModuleClass()`. Updated nav chain classes.

### Documentation

- **Documentation:** Update documentation

# Stable release v16.9.0
## Date: 01/02/2021

### New features

- **Events:** Add `sendStoriesRequest()`
- **Events:** Add `sendExploreSwitch()`
- **Events:** Add `sendNavigationTabClicked()`

### Documentation

- **Documentation:** Update documentation

# Stable release v16.8.7
## Date: 29/01/2021

### Updates and fixes

- **Instagram:** Update login flow
- **Instagram:** Update two factor login

### WIP

- Researching new checkpoint procedures/routines
- New endpoints
- Events are changing and being replaced for other events

# Stable release v16.8.6
## Date: 25/01/2021

### Updates and fixes

- **Constants:** Update version to 170.2.0.30.474
- **Events:** Update chain event classes

### Examples

- **Examples:** Update checkpoint
- **Examples:** Update goLive

### Documentation

- **Documentation:** Update documentation

# Stable release v16.8.5
## Date: 22/01/2021

### Updates and fixes

- **DirectHandler:** Update REACTIONS_REGEXP regexp expression
- **Direct:** Update `getThread()`

### Documentation

- **Documentation:** Update documentation

# Stable release v16.8.4
## Date: 21/01/2021

### Updates and fixes

- **Direct:** Update `getThread()`

### Documentation

- **Documentation:** Update documentation

# Stable release v16.8.3
## Date: 21/01/2021

### Updates and fixes

- **Checkpoint:** Update `sendChallenge()`
- **Exception:** Update ServerMessageThrower

### Examples

- **Examples:** Update checkpoint

### Documentation

- **Documentation:** Update documentation

# Stable release v16.8.2
## Date: 21/01/2021

### Updates and fixes

- **DirectHandler:** Update `_likeThreadItem()`
- **Request:** Update `_addDefaultHeaders()`
- **Settings:** Update StorageHandler

### Examples

- **Examples:** Update realtimeClient

### Documentation

- **Documentation:** Update documentation

### WIP notice

- **WIP:** Notes on WIP

# Stable release v16.8.1
## Date: 20/01/2021

### Updates and fixes

- **DirectHandler:** Update `_likeThreadItem()`

### Examples

- **Examples:** Update realtimeClient

# Stable release v16.8.0
## Date: 19/01/2021

### New features

- **DirectHandler:** Add `_likeThreadItem()`

### Updates and fixes

- **DirectHandler:** Update handlers and events

### Examples

- **Examples:** Update realtimeClient

### Documentation

- **Documentation:** Update documentation

# Stable release v16.7.8
## Date: 18/01/2021

### WIP notice

- **WIP:** Notes on WIP

# Stable release v16.7.7
## Date: 15/01/2021

### Updates and fixes

- **Event:** Fix bug `_sendBatchEvents()`

# Stable release v16.7.6
## Date: 15/01/2021

### Updates and fixes

- **Event:** Update `_sendBatchEvents()`

### Documentation

- **Documentation:** Update documentation

### Wiki

- **Wiki:** Direct/Realtime. Now realtime can be used to send different actions (post, location, hashtah...)
- **Report:** Added report from 14-01-2021.

# Stable release v16.7.5
## Date: 14/01/2021

### Updates and fixes

- **Event:** Update `_getModuleClass()`
- **Event:** Update `_validateNavigationPath()`

### Examples

- **Examples:** Update uploadPhoto

### Documentation

- **Documentation:** Update documentation

# Stable release v16.7.4
## Date: 13/01/2021

### Updates and fixes

- **Instagram:** Update `_sendLoginFlow()`

# Stable release v16.7.3
## Date: 12/01/2021

### Updates and fixes

- **Timeline**: Delete unused experiment check in `getTimelineFeed()`
- **Realtime:** Delete `_isRtcReshareEnabled()`. Enabled by default for anyone. This should have fixed the issue for sending posts, hashtags, and profiles via Realtime client

### Documentation

- **Documentation:** Update documentation

# Stable release v16.7.2
## Date: 06/01/2021

### Examples

- **Example:** Add viewStoryFromSearch
- **Example:** Update checkUserInfoExample

# Stable release v16.7.1
## Date: 04/01/2021

### Examples

- **Example:** Add checkUserInfoExample
- **Example:** Update getCommentsTimeline
- **Example:** Update LiveManager goLive

# Stable release v16.7.0
## Date: 29/12/2020

### New features

- **Events:** Web: Add `updateDateOfBirth()`

### Updates and fixes

- **Constants:** Constants: Update to 169.1.0.29.135
- **Event:** Update `_getModuleClass()`. New nav chains for 169.1.0.29.135

### Examples

- **Example:** Update paginationExample
- **Example:** Add getCommentsTimeline

### Documentation

- **Documentation:** Update documentation

# Stable release v16.6.1
## Date: 25/12/2020

### Updates and fixes

- **Realtime:** Update IrisSubscribe. Realtime fixed and working again!

Happy Xmas!

# Stable release v16.6.0
## Date: 23/12/2020

### New features

- **Account:** Add `updateTagSettingsAction()`
- **Account:** Add `updateMentionSettingsAction()`

### Wiki

- **WIP/Work in progress**

### Documentation

- **Documentation:** Update documentation

# Stable release v16.5.1
## Date: 19/12/2020

### Updates and fixes

- **Usertag:** Update `removeTag()`

### Examples

- **Example:** Added viewStoriesFromLikerList

# Stable release v16.5.0
## Date: 18/12/2020

### New features

- **Usertag:** Add `removeTag()`

# Stable release v16.4.12
## Date: 16/12/2020

### Updates and fixes

- **Event:** Update event body
- Update model and responses

### Documentation

- **Documentation:** Update documentation

### Examples

- **Example:** Added viewStoryFromTimeline

# Stable release v16.4.11
## Date: 08/12/2020

### Updates and fixes

- **Model and responses:** Update LoginResponse and models

### Documentation

- **Documentation:** Update documentation

# Stable release v16.4.10
## Date: 08/12/2020

### Updates and fixes

- **Constants:** Update capabilities
- **Internal:** Update story parameters
- **Utils:** Update throwIfInvalidStoryLocationSticker

### Examples

- **Example:** Added uploadStoryLocation

# Stable release v16.4.9
## Date: 07/12/2020

### Updates and fixes

- **Instagram:** Add stop deletion token

### Wiki

- **Wiki:** Clarifying MQTT messages and Deletion Token

### Documentation

- **Documentation:** Update documentation

# Stable release v16.4.8
## Date: 07/12/2020

### Examples

- **Examples:** Update followFromExternalUrl
- **Examples:** Update followFromMediaLikers
- **Examples:** Update mediaLikers
- **Examples:** Update timelinePagination
- **Examples:** Update likeFromExploreTag
- **Examples:** Update likeFromExploreUser
- **Examples:** Update likeFromMediaLikers
- **Examples:** Update likeFromUserFollowers
- **Examples:** Update likeFromUserFollowings

# Stable release v16.4.7
## Date: 06/12/2020

### Examples

- **Examples:** Update comment
- **Examples:** Update commentLike
- **Examples:** Update followFromExternalUrl
- **Examples:** Update followFromMediaLikers
- **Examples:** Update mediaLikers
- **Examples:** Update timelinePagination
- **Examples:** Update likeFromExploreTag
- **Examples:** Update likeFromExploreUser
- **Examples:** Update likeFromMediaLikers
- **Examples:** Update likeFromUserFollowers
- **Examples:** Update likeFromUserFollowings

### Documentation

- **Documentation:** Update documentation

# Stable release v16.4.6
## Date: 03/12/2020

### Examples

- **Examples:** Update sendText

# Stable release v16.4.5
## Date: 02/12/2020

### Updates and fixes

- **Instagram:** Update login flow

### Examples

- **Examples:** Update followFromLocationSearch
- **Examples:** Update followFromSearch
- **Examples:** Update unfollowFromSearch
- **Examples:** Update checkUsers
- **Examples:** Update muteFromSearchUser
- **Examples:** Update SettingUserNotifications

### Documentation

- **Documentation:** Update documentation

# Stable release v16.4.4
## Date: 02/12/2020

### Updates and fixes

- **Constraints:** Update `TimelineConstraints`

### Wiki

- **Wiki:** Added warning regarding direct messaging usage.

### Documentation

- **Documentation:** Update documentation

# Stable release v16.4.3
## Date: 30/11/2020

### Updates and fixes

**Instagram:** Update to 167.0.0.24.120
**Request:** Add new header `X-FB-Client-IP`
**Event:** Update NavChain classes

### Documentation

- **Documentation:** Update documentation

# Stable release v16.4.2
## Date: 27/11/2020

### Updates and fixes

- **Client:** Update visibility bandwidth and network params
- **Debug:** Add condition for RAW DATA

### Examples

- **Example:** Update followFromLocationSearch
- **Example:** Update followFromSearch
- **Example:** Update likeFromExploreTag

### Documentation

- **Documentation:** Update documentation

# Stable release v16.4.1
## Date: 23/11/2020

### Updates and fixes

- **Event:** Fixed IDE issues

### Documentation

- **Documentation:** Update documentation

### Wiki

- **Wiki:** WIP. NavChains v167.

# Stable release v16.4.0
## Date: 19/11/2020

### New feature:

- **Instagram:** Add `decrementNavChainStep()`

### Updates and fixes

- **Constants:** Update batch query
- **Event:** Update nav chain algorithm

### Examples

- **Example:** Add checkUsers example

### Documentation

- **Documentation:** Update documentation

# Stable release v16.3.4
## Date: 17/11/2020

### Updates and fixes

- **Media:** Update `getOembedInfo()`

### Wiki and code

- **Wiki:** AccountCompromised
- **Wiki:** Bundle

- Added Superpack compiled bundle and old decompiled JS bundle.

# Stable release v16.3.3
## Date: 13/11/2020

### Updates and fixes

- **Constants:** Update Constants
- **Account:** Update registration functions for iOS support

### Documentation

- **Documentation:** Update documentation

# Stable release v16.3.2
## Date: 10/11/2020

### Updates and fixes

- **Instagram/Internal:** Add a function to disable auto retries media upload

# Stable release v16.3.1
## Date: 9/11/2020

### Updates and Fixed

- **Event:** Update `sendNavigation()`

# Stable release v16.3.0
## Date: 9/11/2020

### New features

- **Instagram:** Add navigation chain set and get functions

### Updates and Fixed

- **Client:** Update `X-IG-WWW-Claim` default value
- **Event:** Update `_getModuleClass()`
- **Event:** Update `_generateNavChain()`
- **Event:** Update `sendUpdateSessionChain()`


### Wiki

- **Wiki:** Add notes about navigation chain. MUST READ.

### Documentation

- **Documentation:** Update documentation

# Stable release v16.2.2
## Date: 6/11/2020

### Updates and Fixed

- **Event:** Update `prepareAndSendExploreImpression()`

# Stable release v16.2.1
## Date: 5/11/2020

### Updates and Fixed

- **Direct:** Update `getThreadByParticipants()`

### Examples

- **Example:** Update direct sendText.

### Documentation

- **Documentation:** Update documentation

# Stable release v16.2.0
## Date: 4/11/2020

### New features

- **Event:** Add `_getModuleClass()`
- **Event:** Add `_generateNavChain()`
- **Event:** Add `sendUpdateSessionChain()`

### Examples

- **Example:** Update goLive example.

### Documentation

- **Documentation:** Update documentation

# Stable release v16.1.2
## Date: 1/11/2020

### Updates and fixes

- **Client:** Update client. Fixes issue from v16.1.1.

### Documentation

- **Documentation:** Update documentation

# Stable release v16.1.1
## Date: 31/10/2020

### Updates and fixes

- **Client:** Update header seters
- **Constants:** Update capabilities
- **Event:** Fix throw statement
- **Exception:** Added missing exception `InvalidArgumentException`
- **Internal:** Update location sticker
- **Utils:** Update `throwIfInvalidStoryLocationSticker()`
- **Settings:** Update StorageHandler

### Examples

- **Example:** Update uploadStory

### Documentation

- **Documentation:** Update documentation

# Stable release v16.1.0
## Date: 30/10/2020

### New features

- **Web:** Add `sendGraphqlQuery()`

### Updates and fixes

- **Constants:** Update iOS constants
- **Utils:** Update supported devices

### Documentation

- **Documentation:** Update documentation

# Stable release v16.0.1
## Date: 29/10/2020

### Dataset

- **Data:** `ads_countries_config.json`

### Updates and fixes

- **Constants:** Update to 165.1.0.29.119
- **Event:** Update `sendStringImpressions()`

# Stable release v16.0.0
## Date: 29/10/2020

### BACKWARD BREAKS ⚠️

- **Direct:** Update `markItemSeen()`. A new argument has been added to `markItemSeen()`.

### New features

- **Account:** Add `getCrossPostingDestinationStatus()`
- **Direct:** Add `getHasInteropUpgraded()`

### Updates and fixes

- **Direct:** Update `markItemSeen()`.
- **Discover:** Update `getExploreFeed()`
- **Instagram:** Update login flow
- **Internal:** Share story to FB page. 'USER' or 'PAGE' can be selected with 'share_to_fb_destination_type' external metadata key.
- **Request:** Update headers

### Examples

- **Example:** Add approvePendingInbox

### Documentation

- **Documentation:** Update documentation

# Stable release v15.8.4
## Date: 26/10/2020

### Updates and fixes

- **Internal:** Update `uploadSinglePhoto()`
- **Utils:** Update `extractURLs()`

### Examples

- **Example:** Update goLive

# Stable release v15.8.3
## Date: 21/10/2020

### Updates and fixes

- **Internal:** Update `shareToIgtv()`

### Examples

- **Example:** Update liveBroadcast
- **Examples:** Update uploadVideoIGTVWithThumbnail

### Documentation

- **Documentation:** Update documentation

# Stable release v15.8.2
## Date: 20/10/2020

### Updates and fixes

**Instagram:** Update `setLocale()` and `setAcceptLanguage()`

# Stable release v15.8.1
## Date: 19/10/2020

### Updates and fixes

- **Constants:** Bump version to `163.0.0.45.122` in order to be able to use all IG features. WIP.

# Stable release v15.8.0
## Date: 16/10/2020

### New features

**Live:** Live: Add `shareToIgtv()`

### Updates and fixes

- **Instagram:** Update `setAcceptLanguage()`
- **Internal:** Add share to IGTV
- **Metadata:** Add broadcast ID property

### Examples

- **Example:** Update liveBroadcast
- **Examples:** Update goLive

### Documentation

- **Documentation:** Update documentation

# Stable release v15.7.1
## Date: 15/10/2020

### Updates and fixes

- **Instagram:** Update `setLocale()`

### Documentation

- **Documentation:** Update documentation

# Stable release v15.7.0
## Date: 14/10/2020

### New feature

- **Account:** Add `setBirthday()`

### Examples

- **Example:** Update Gating

### Documentation

- **Documentation:** Update documentation

# Stable release v15.6.5
## Date: 13/10/2020

### Updates and fixes

- **Internal:** Update `configureSinglePhoto`
- **Internal:** Update `configureSingleVideo`

### Examples

- **Example:** Update checkpoint

# Stable release v15.6.4
## Date: 13/10/2020

### Updates and fixes

- **Internal:** Update `configureSinglePhoto`
- **Internal:** Update `configureSingleVideo`

### Examples

- **Example:** Update checkpoint

# Stable release v15.6.3
## Date: 13/10/2020

### Updates and fixes

- **Exception:** Add `ReviewContactPointChangeFormException`

### Examples

- **Example:** Update checkpoint

### Documentation

- **Documentation:** Update documentation

# Stable release v15.6.2
## Date: 12/10/2020

### Updates and fixes

- **Utils:** Update `throwIfInvalidStoryHashtags`

### Examples

- **Example:** Live utils remove code
- **Example:** Update LiveManager auth

### Documentation

- **Documentation:** Update documentation

# Stable release v15.6.1
## Date: 10/10/2020

### Updates and fixes

- **Checkpoint:** Update `sendSetNewPassword()`

# Stable release v15.6.0
## Date: 09/10/2020

### New features

- **Checkpoint:** Add `sendSetNewPassword()`
- **Exception:** Add `LegacyForceSetNewPasswordFormException`

### Examples

- **Example:** Update checkpoint

### Documentation

- **Documentation:** Update documentation

# Stable release v15.5.0
## Date: 09/10/2020

### New features

- **Event:** Add `sendSearchInitiated()`
- **Exception:** Add `VerifySMSCodeFormException`

### Examples

- **Example:** Update checkpoint

### Documentation

- **Documentation:** Update documentation

# Stable release v15.4.6
## Date: 07/10/2020

### Updates and fixes

- **Utils:** Update `encryptPassword()`

# Stable release v15.4.5
## Date: 06/10/2020

### Updates and fixes

- **People:** Update `linkAddressBook()`

# Stable release v15.4.4
## Date: 05/10/2020

### Wiki

- **Wiki:** Update FAQ. Add instructions for disabling HTTP2.

### Examples

- **Example:** Add likeFromUserFollowers
- **Example:** Add sendReactionFromUserSearch

- **Example:** Update likeFromExploreTag
- **Example:** Update likeFromExploreUser
- **Example:** Update likeFromTimeline
- **Example:** Update likeFromUserFollowings


# Stable release v15.4.3
## Date: 30/09/2020

### Updates and fixes

- **Direct:** Update `getPendingInbox()`
- **Event:** Update `_validateNavigationPath()`
- **Story:** Update `getReelsTrayFeed()`

### Examples

- **Example:** Add likeFromUserFollowings

### Documentation

- **Documentation:** Update documentation

# Stable release v15.4.2 (Hot fix)
## Date: 29/09/2020

### New features

- **Instagram:** You can now disable HTTP2 and use HTTP1 instead. NOT RECOMMENDED! `\InstagramAPI\Instagram::$disableHttp2 = true;`

# Stable release v15.4.1
## Date: 29/09/2020

### New features

- **Instagram:** You can now disable HTTP2 and use HTTP1 instead. NOT RECOMMENDED! `\InstagramAPI\Instagram::$disableHttp2 = true;`

# Stable release v15.4.0
## Date: 29/09/2020

### New features

- **Creative:** Add `getSegmentationModels()`
- **Creative:** Add `getCameraModels()`

### Updates and fixes

- **Instagram:** Update `_sendLoginFlow()`
- **Creative:** Update `getFaceModels()`
- **Internal:** Update `_getPhotoUploadParams()`
- **Models and responses:** Update models and responses

### Examples

- **Example:** Update checkpoint example
- **Example:** Update checkpoint uploadStory

### Documentation

- **Documentation:** Update documentation

# Stable release v15.3.0
## Date: 28/09/2020

### New features

- **Reel:** Add `uploadVideo()`
- **Constants:** Add `BLACKLISTED_PASSWORDS`
- **Constants:** Add `PRIDE_HASHTAGS`

### Updates and fixes

- **Account:** Update `create()`
- **Account:** Update `createValidated()`
- **Event:** Update `sendReelPlaybackNavigation()`

### Documentation

- **Documentation:** Update documentation

# Stable release v15.2.0
## Date: 23/09/2020

### New features

- **Event:** Add `sendReelTrayImpression()`
- **Event:** Add `sendFeedItemInserted()`
- **Event:** Add `sendOrganicCarouselImpression()`

### Documentation

- **Documentation:** Update documentation

# Stable release v15.1.1
## Date: 22/09/2020

### Updates and fixes

- **Utils:** Update `checkIsValidiDevice()`

# Stable release v15.1.0
## Date: 22/09/2020

### New features

- **Event:** Add `sendSearchFollowButtonClicked()`

### Updates and fixes

- **Discover:** Update `getChainingUsers()`

### Examples

- **Example:** Update followFromSearch
- **Example:** Update muteFromFollowings
- **Example:** Update likeFromMediaLikers

### Documentation

- **Documentation:** Update documentation

# Stable release v15.0.0
## Date: 22/09/2020

### BACKWARD BREAKS ⚠️

- **Checkpoint:** `sendWebFormSmsCode()` has been renamed to `sendWebFormSecurityCode()`

### Updates and fixes

- **Checkpoint:** Update `sendChallenge()`
- **Exception:** Update ServerMessageThrower
- **Exception:** Add AcknowledgeFormException
- **Exception:** Add VerifyEmailCodeFormException
- **Models and responses:** Update models and responses

### Examples

- **Example:** Update checkpoint

### Documentation

- **Documentation:** Update documentation

# Stable release v14.12.1
## Date: 21/09/2020

### Updates and fixes

- **Constants:** Update iOS to 159.0.0.28.123

### Examples

- **Example:** Update followFromSearch
- **Example:** Update followFromActivity
- **Example:** unfollowFromSearch

# Stable release v14.12.0
## Date: 19/09/2020

### New features

- **Instagram:** Added getter and setter dark mode

### Updates and fixes

- **Highlight:** Update `getUserFeed()`

### Documentation

- **Documentation:** Update documentation

# Stable release v14.11.1
## Date: 18/09/2020

### Updates and fixes

- **Constant:** Update android version to 159.0.0.40.122
- **Internal:** Update `getFacebookOTA()`

### Documentation

- **Documentation:** Update documentation

# Stable release v14.11.0
## Date: 15/09/2020

### New features

- **Event:** Add `sendIGStartCameraSession()`
- **Event:** Add `sendCameraWaterfall()`
- **Event:** Add `sendNametagSessionStart()`
- **Event:** Add `sendIgCameraShareMedia()`
- **Event:** Add `sendIgCameraEndPostCaptureSession()`
- **Event:** Add `sendIgCameraEndSession()`


### Updates and fixes

- **Event:** Update `_validateNavigationPath()`

### Examples

- **Examples:** Update `uploadStorySlider`.

### Documentation

- **Documentation:** Update documentation

# Stable release v14.10.5
## Date: 14/09/2020

### Updates and fixes

- **Client:** Update `_buildGuzzleOptions()`
- **Internal:** Update `configureSingleVideo()`

### Documentation

- **Documentation:** Update documentation

# Stable release v14.10.4
## Date: 10/09/2020

### Updates and fixes

- **Event:** Update `sendProfileAction()` and `_validateNavigationPath()`

# Stable release v14.10.3
## Date: 10/09/2020

### Updates and fixes

- **Client:** Update `_buildGuzzleOptions()`. Now API uses HTTP2.

### Documentation

- **Documentation:** Update documentation

# Stable release v14.10.2
## Date: 09/09/2020

### Updates and fixes

- **Event:** Update event body

# Stable release v14.10.1
## Date: 09/09/2020

### Examples:

- **Examples:** Update UnfollowFromSearch
- **Examples:** Add muteFromSearchUser

# Stable release v14.10.0
## Date: 07/09/2020

### New features

- **Constants:** Add `PDQ_VIDEO_TIME_FRAMES`
- **Internal:** Add `updateMediaWithPdqHashes()`

### Updates and fixes

- **Internal:** Update `uploadSingleVideo()`
- **Internal:** Update `uploadVideoThumbnail()`
- **Media:** Update `PDQHasher`

### Documentation

- **Documentation:** Update documentation

# Stable release v14.9.2
## Date: 06/09/2020

### Examples

- **Examples:** Add `unfollowFromSearch`.
- **Examples:** Add `checkIfUserFollowsOtherUser`

# Stable release v14.9.1
## Date: 05/09/2020

### Updates and fixes

- **Utils:** Update `extractURLs()`.
- **Events:** Fix `_validateNavigationOptions()`

### Documentation

- **Documentation:** Update documentation

# Stable release v14.9.0
## Date: 02/09/2020

### New features

- **Media:** Added PDQ Hashing algorithm.

### Updates and fixes

- **Internal:** Update `configure_mode` types.

### Documentation

- **Documentation:** Update documentation

# Stable release v14.8.1
## Date: 01/09/2020

### Updates and fixes

- **Constants:** Added new `SHARE_TYPE` constants.
- **Instagram:** Update `generateUUID()`
- **Internal:** Update share type constants.
- **Settings:** Update `StorageHandler`. New `seq_id` key added.
- **Signatures:** Update `generateUUID()`.

### Documentation

- **Documentation:** Update documentation

# Stable release v14.8.0
## Date: 28/08/2020

### New features

- **IGTV:** Add `getAllUserSeries()`
- **IGTV:** Add `createSeries()`
- **Live:** Add `getPostLiveThumbnails()`
- **Live:** Add `getFundraiserInfo()`
- Added models and responses

### Updates and fixes

- **Discover:** Update `getExploreFeed()`
- **Internal:** Update `configureSingleVideo()`
- **Live:** Update `create()`

### Documentation

- **Documentation:** Update documentation

### Wiki

- **Wiki:** Added docummentation for device fingerprinting and recommendations (`deviceFingerprint.md`).

# Stable release v14.7.0
## Date: 25/08/2020

### New features

- **Discover:** Add `getExploreReels()`
- **Instagram:** Added new Reel class
- **Reel:** Add `getHome()`
- **Reel:** Add `getUserReels()`
- **Reel:** Add `getMusic()`
- **Response:** Added Reels response

### Updates and fixes

- **Constants:** Update to 155.0.0.37.107

### Documentation

- **Documentation:** Update documentation

# Stable release v14.6.1
## Date: 21/08/2020

### Updates and fixes

- **Direct:** Update `_sendDirectItem()`
- **Event:** Update `_validateNavigationOptions()`
- **Internal:** Update `configureSinglePhoto()`

### Wiki

- **Wiki:** Added docummentation for feedback (`feedback.md`).

### Documentation

- **Documentation:** Update documentation

# Stable release v14.6.0
## Date: 17/08/2020

### New features

- **Utils:** Add `checkIsValidiDevice()`. More customization for iOS users. See /wiki/iDevices.md
- **Web:** Add `getPasswordChanges()`

### Updates and fixes

- **Constants:** Update iOS version
- **Instagram:** Update `setIosModel()`
- **Model:** Update `AccountAccessToolSettingsPages`

### Wiki

- **Wiki:** Added instructions (`iDevices.md`) for using different iPhone models.

### Documentation

- **Documentation:** Update documentation

# Stable release v14.5.3
## Date: 14/08/2020

### Updates and fixes

- **Constants:** Update iOS constants
- **Instagram:** Update `_login()` for iOS
- **Instagram:** Update `_setUser()` for iOS
- **Internal:** Update `_splitVideoIntoSegments()`
- **Media:** Update MediaDetails
- **Media:** Update VideoDetails

# Stable release v14.5.2
## Date: 14/08/2020

### Updates and fixes

- **Events:** Fix `sendNavigation()`
- **Utils:** Fix `extractURLs()`

# Stable release v14.5.1
## Date: 11/08/2020

### Updates and fixes

- **Constants:** iOS to 153.0.0.26.73
- **GoodDevices:** Update User Agents

### Examples

- **Example:** Update directInboxHide

### Documentation

- **Documentation:** Update documentation


# Stable release v14.5.0
## Date: 07/08/2020

### New features

- **Realtime:** Add `isConnected()`

### Updates and fixes

- **Event:** Update `sendNavigation()`
- **Response:** Update `TimelineFeedResponse`
- **Utils:** Update `extractURLs()`

### Examples

- **Example:** Add directInboxHide

### Documentation

- **Documentation:** Update documentation


# Stable release v14.4.0
## Date: 05/08/2020

### New features

- **Client:** Add `_getRequestId()` and update logger

### Updates and fixes

- **Web:** Update `sendEmailVerificationCode()` and `checkEmailVerificationCode()`

### Examples

- **Example:** Update paginationExample
- **Example:** Add sendReaction

### Documentation

- **Documentation:** Update documentation


# Stable release v14.3.0
## Date: 04/08/2020

### New features

- **Web:** Add `sendEmailVerificationCode()`
- **Web:** Add `checkEmailVerificationCode()`

### Updates and fixes

- **Web:** Update `createAccount()`
- **Web:** Update `sendSignupSms()`
- **Composer:** Update composer

# Stable release v14.2.0
## Date: 03/08/2020

### New features

- **Business:** Add `getMonetizationProductsEligibilityData()` and response model

### Updates and fixes

- **Instagram:** Update `_sendLoginFlow()`
- **Direct:** Update `sendText()`
- **GoodDevices:** Delete `FLAGGED_DEVICES`
- **People:** Update `getSharePrefill()`
- **Timeline:** Update `getTimelineFeed()`


### Documentation

- **Documentation:** Update documentation


# Stable release v14.1.2
## Date: 01/08/2020

### Updates and fixes

- **Instagram:** Update `_sendLoginFlow()`
- **Direct:** Update `getInbox()`

# Stable release v14.1.1
## Date: 31/07/2020

### Updates and fixes

- **Instagram:** Update `_sendLoginFlow()`
- **Example:** Update directInbox

# Stable release v14.1.0
## Date: 30/07/2020

### New features

- **Web:** Add `createAccount()`
- **Web:** Add `sendSignupSms()`

### Updates and fixes

- **Utils:** Update `encryptPasswordForBrowser()`

### Documentation

- **Documentation:** Update documentation

# Stable release v14.0.1
## Date: 28/07/2020

### Updates and fixes

- **Client:** Update `_buildGuzzleOptions()`
- **Event:** Update `_sendBatchEvents()`

# Stable release v14.0.0
## Date: 27/07/2020

### BACKWARD BREAKS ⚠️

- **Direct:** `getInbox()` signature has changed. Arguments have been updated.

### Updates and fixes

- **Account**: Update `create()` and `createValidated()`
- **Instagram:** Update `_sendPreLoginFlow()`
- **Utils**: Update `extractURLs()`

# Stable release v13.1.5
## Date: 26/07/2020

### Updates and fixes

- **Events:** Update headers **Critical**
- **People:** Update `getFollowers()` function

# Stable release v13.1.4
## Date: 25/07/2020

### Updates and fixes

- **Constants:** Update constants to 151.0.0.23.120

### Examples

- **Example:** Update SMS registration
- **Example:** Update followFromLocationSearch

# Stable release v13.1.3
## Date: 21/07/2020

### EXPERIMENTAL

- **Direct:** `sendAudio()`. Still at research. Won't work in its current status.

### Updates and fixes

- **Direct:** Update `_sendDirectItem()`
- **Event:** Update `_validateNavigationPath()`
- **Event:** Update `prepareAndSendThumbnailImpression()`

### Examples

- **Example**: Update mute from following

### Wiki

- **Wiki:** Added instructions (`Composer.md`) for using composer with private repository

### Documentation

- **Documentation:** Update documentation

# Stable release v13.1.2
## Date: 19/07/2020

### Updates and fixes

- **Constants:** Update `SUPPORTED_CAPABILITIES`
- **Internal:** Update `sendLauncherSync()`

### Documentation

- **Documentation:** Update documentation

# Stable release v13.1.1
## Date: 18/07/2020

### Updates and fixes

- **Account:** Update `prepareAndSendExploreImpression()`
- **General:** Update codestyle

# Stable release v13.1.0
## Date: 17/07/2020 

### New features

- **Instagram:** Added `skipAccountValidation` flag

### Updates and fixes

- **Account:** Update `createValidated()`
- **Account:** Update `checkPhoneNumber()`
- **Instagram:** Update `_updateLoginState()`

### Examples

- **Example**: Add mute from following

### Documentation

- **Documentation:** Update documentation

# Stable release v13.0.1
## Date: 12/07/2020

### Updates and fixes

- **ServerMessageThrower:** Added new exception

### Examples

- **Checkpoint:** Update checkpoint

### Documentation

- **Documentation:** Update documentation

# Stable release v13.0.0
## Date: 10/07/2020

### BACKWARD BREAKS ⚠️

- **Account:** Function signature of `create()` has changed. Now it has `$signupCode` param.

### New features

- **Constants** Update constants to 148.0.0.33.121
- **Account:** Added function `sendEmailVerificationCode()`
- **Account:** Added function `checkConfirmationCode()`

### Updates and fixes

- **Realtime:** Update `isConnecte()` visibility

# Stable release v12.10.1
## Date: 28/06/2020

### Updates and fixes

- 14be78e **Direct**: Update `getInbox()`
- 8af0bb9 **Instagram**: Update `_login()`


### Documentation

- e721d2c **Documentation**: Update doc

# Stable release v12.10.0
## Date: 26/06/2020

### New features

- db9e308 **Direct**: Add `moveThread()`


### Updates and fixes

- 76796af **Direct**: Update `approvePendingThreads()` doc
- 1bbef04 **Codestyle**: Update codestyle
- b84c169 **StorageHandler**:  Update `PERSISTENT_KEYS`
- 6dabab5 **Instagram**: Update `_login()`
- 9fc068a **Direct**: Update `approvePendingThreads()`
- 3043be7 **Client**: Update `api()`


### Documentation

- cb57800 **Documentation**: Update doc
- 889805e **Documentation**: Update doc
- f6a02a6 **Documentation**: Update doc


### Examples

- 69adbf3 **Example**: Update removeFollowings
- d15ec91 **Example**: Update web login

# Stable release v12.9.1
## Date: 17/06/2020

### Updates and fixes

- 6921a44 **Exception**: Update ServerMessageThrower
- a87f90c **Instagram**: Update `_login()`
- 2efd991 Update model and responses
- 7c3af5c Update .gitignore
- cc661f7 **Account**: Update `getAccountsMultiLogin()`
- 8165e15 **Instagram**: Update `_login()`


### Documentation

- 2078897 **Documentation**: Update doc
- 7543796 **Documentation**: Update doc


### Examples

- 57bcd7a **Example**: Update checkpoint
- 8440e05 **Example**: Update goLive
- e54985e **Example**: Add LiveManager/getCharities


# Stable release v12.9.0
## Date: 15/06/2020

### New features

- e5171f3 **Instagram**: Add iOS Model and DPI setters and getters


### Updates and fixes

- 2bd22b8 **Event**: Update `_validateNavigationPath()`
- 11d8b3e **Request**: Update `getRawResponse()`
- 5376310 **Account**: Update `checkEmail()`
- 7523279 **ServerMessageThrower**: Add  ChallengeFinishedException
- de52153 **Device**: Update iOS Model and iOS DPI custom settings
- 6906f90 **UserAgent**: Update `buildiOSUserAgent()`


### Documentation

- a9b01d6 **Documentation**: Update doc


### Examples

- 68ce331 **Example**: Add followFromExternalUrl
- af8c038 **Example**: Add explorePagination


# Stable release v12.8.0
## Date: 13/06/2020

**> [!] Note: Recommended update**

### New features

- 9dceae2 **Account**: Add `getAccountsMultiLogin()`
- 3f4fc8a **Instagram**: Add `getEventsCompressed()`


### Updates and fixes

- dffc126 **Exception**: Update ServerMessageThrower
- ea8b33a **Constants**: Update to 142.0.0.34.110 (Android)
- a436508 **Direct**: Update `approvePendingThreads()`
- bdcf328 **Event**: Update `forceSendBatch()` and _sendBatchEvents()
- 1c26168 **Client**: Update client headers on events


### Documentation

- 165f80f **Documentation**: Update doc
- 864b51d **Documentation**: Update doc


### Examples

- 6ec1c73 **Example**: Update likeFromExternalUrl


# Stable release v12.7.0
## Date: 11/06/2020

### New features

- e6b3025 **Live**: Add `getCharityDonations()`
- 1311436 **Live**: Add `searchCharity()`
- c8dd1b8 **Live**: Add `getDefaultCharities()`


### Updates and fixes

- 83d4b65 **Live**: Update `start()`
- e511a97 **Live**: Update `start()`


### Documentation

- fa2ebeb **Documentation**: Update doc
- 0bcdd64 **Documentation**: Update doc
- 143929b **Wiki**: Add captcha wiki
- 2bff987 **Wiki**: Update NoCaptchaProxyless
- 18aec8a **Wiki**: Update NoCaptcha
- a150fda **Wiki**: Update Anticaptcha


### Examples

- 3ddbb33 **Example**: Update liveBroadcast
- 61ba675 **Example**: Add likeFromExternalUrl
- 9d6064e **Example**: Update checkVod
- 621f035 **Example**: Add LiveManager

# Stable release v12.6.1
## Date: 08/06/2020

### Updates and fixes

- 14ff511 **Dotfiles**: Update PHP CS
- c5a47dc **Story**: Update `answerStoryQuestion()`


### Documentation

- bf25860 **Wiki**: Added Anticaptcha code examples
- b88181e **Documentation**: Update doc


### Examples

- be9cbf4 **Example**: Update registration examples

# Stable release v12.6.0
## Date: 06/06/2020

### New features

- f9e4119 **Media**: Add `checkOffensiveComment()`


### Updates and fixes

- 8ae8355 **Internal**: Update `sendConsent()`


### Documentation

- 7f2fc36 **Documentation**: Update doc


### Examples

- ad5de8d **Example**: Update consent
- 2c5f015 **Example**: Update Gating

# Stable release v12.5.1
## Date: 05/06/2020

### Updates and fixes

- 9208156 **Timeline**: Update `getSelfUserFeed()`
- 69cc638 **Models**: Update Gating models
- 9932da7 **Realtime**: Update ReactMqttClient `disconnect()`


### Examples

- aeb246e **Example**: Add Gating
- a3b5e29 **Example**: Update twoFactorLogin
- 8e07792 **Example**: Update likeFromMediaLikers
- 9183f8d **Example**: Update twoFactorLogin
- 8800d33 **Example**: Add likeFromMediaLikers


# Stable release v12.5.0
## Date: 03/06/2020

### New features

- 44e8a25 **Account**: Add `regenBackupCodes()`


### Updates and fixes

- 8f88725 **Direct**: Update `approvePendingThreads()`
- eb102ba **Timeline**: Update `getUserFeed()`
- 054d901 **Exception**: Update `autoThrow()`
- cac4731 **Checkpoint**: Update `sendChallenge()`


### Documentation

- 17a4c7d **Documentation**: Update docs
- 5f3af01 **Wiki**: Add Facebook Login doc

### Examples

- 788883d **Example**: Update checkpoint


# Stable release v12.4.0
## Date: 01/06/2020

### New features

- 5bb415a **Web**: Added get and setters for web user agent


### Updates and fixes

- 79ca902 **Devtool**: Update prepareChangelog
- 76af9b1 **Event**: Update `sendCommentImpression()`
- cabef38 **Event**: Update `sendOrganicMediaImpression()`
- 4585741 **Event**: Update `sendOrganicViewedImpression()`
- 9c9e52f **Constants**: Added `WEB_USER_AGENT`
- c005957 **Account**: Update registration functions


### Documentation

- c28b01f **Documentation**: Update docs


### Examples

- f663625 **Example**: Update timelinePagination
- 283afc3 **Example**: Update followFromActivity
- 15c5d6c **Example**: Update registration examples


# Stable release v12.3.0
## Date: 29/05/2020

### New features

- 4ea1228 **Client**: Add resolve host in guzzle options
- d3eaac2 **Event**: Add `prepareAndSendThumbnailImpression()`
- deaf71f **Devtools**: Automatically generate changelog
- 1cf146e **Realtime**: Add new AppPresence query
- 24fa230 **Music**: Add `getLyrics()`
- 54adc42 **Internal**: Add music sticker for stories
- 57ec053 **Music**: Add `keywordSearch()`
- 952e54d **Music**: Add `search()`
- c0b6447 **Request**: Add Music request collection


### Updates and fixes

- 03df97e **Media**: Update DirectConstraints
- ca7f999 **Direct**: Fixed sending video via Direct HTTP
- e37d86d **Account**: Fix typo `getNamePrefill()`
- 24acc3f **Event**: Update `tab_index` property
- 91bfc44 **Client**: Update `mapServerResponse()`
- 322f592 **Utils**: Update `extractURLs()`
- afcfdc9 **Direct**: Update `createGroupThread()`
- 443578e Fix properties visibility
- 529d60f 0815582 **Realtime**: Update GraphQL parser
- 1a708ce fdd6236 Update codestyle
- f45cd2a Update models and response doc

### Documentation

- 7f8894a **Wiki**: Add modules
- eeb29be **FAQ**: Added F.A.Q. document
- 0b9beff **Documentation**: Update

### Examples

- bb878da **Example**: Update realtimeHttp
- 46d0cd1 **Example**: Update ExtendingInstagram
- 9cbcf40 **Example**: Add note in checkpoint
- 8e14fe7 **Examples**: Add uploadStoryMusic
- 8c83809 **Examples:** Update registration examples
- bb878da **Example**: Update realtimeHttp

# Stable release v12.2.0
## Date: 25/05/2020

**> Recommended update! <**

### Documentation

- 9f8107e fae3104 Instagram Private API Wiki
- 942822a 3f094c0 Code documentation

### New feature

- 967d855 **Discover**: Add `getAyml()`

### Updates and fixes

- 938eb6a **Timeline**: update `getTimelineFeed()`
- 2dc839c **Signatures**: Update `signData()`

### Example

- c50c484 **Example**: Update followFromUserFollowers


# Stable release v12.1.0
## Date: 22/05/2020

### Updates and fixes

- 5c8450a **Internal:** Update `getFacebookDodResources()`
- 392e61c **Client**: `saveCookieJar()`
- 29dc83e **Event:** Update `_validateNavigationPath()`
- 326f84a **Account:** Update `changePassword()`
- 4ecdafd **Push:** Update `register()`
- fb90012 **Account:** add `getNamePrefill()`
- 7f0184f 267dc68 e2bc921 **Instagram:** Update `_sendPreLoginFlow()`
- cc5bb1c **Internal:** Update `sendLauncherSync()` php doc
- 7979130 **Account:** Update `setContactPointPrefill()`
- 3332927 **Constants:** Update capabilities
- 238fe76 26ea102 **Signatures:** Update `generateSignature()` for iOS
- 88057a8 **Constants:** Update iOS constants

# Stable release v12.0.0
## Date: 20/05/2020

### BACKWARD BREAKS ⚠️

- a95da1a **Instagram:** Update constructor. `Auth key` is no longer a parameter in the `InstagramAPI` constructor:

```php
    public function __construct(
        $debug = false,
        $truncatedDebug = false,
        array $storageConfig = [],
        $platform = 'android',
        $logger = null)
```


### New features

- b6feecf **People:** add `getUnfollowChaining()`
- 9b7f50b **Signatures:** iOS signature algorithm

### Updates and fixes

- aca6f9b **Event:** Update `_validateNavigationPath()`
- 07123a6 **ServerMessageThrower:** Update `autoThrow()`
- 2cc7275 **Media:** Update `like()` and `unlike()`
- 6d21b89 **Media:** Update `comment()`
- 3826045 **Event:** update `_validateNavigationPath()`
- 7dbc6b4 9276ea6 **Account:** Update `create()` and `createValidated()`
- 8a0d23b **Request:** Update `_getRequestBody()` and `_buildHttpRequest()`

### Examples

- 7476488 ce6e747 994cff0 **Examples:** Update registration examples



# Stable release v11.2.0
## Date: 18/05/2020

### New features

- 5355ef8 **Event:** add `sendDobPick()`
- a47fea2 **Event:** Add `prepareAndSendExploreImpression()`
- 883d3bf **Event:** add `sendIgNuxFlow()`

### Updates and fixes

- 4c4091d **Model:** Update TwoByTwoItem model
- 7cca4c9 cf569d9 d4594d4 **Event:** Fixed typo `sendFlowSteps()`
- 15a6f18 **Event:** update `_addCommonProperties()`
762f8ee 2bf3782 **Event:** Update `_validateNavigationPath()`
- fdf24e5 **Instagram:** Update `sendFlowSteps()`

### Examples

- 18e4895 Update examples
- d40fbfa **Example:** Update emailRegistration
- 0c53c9d **Example:** Update smsRegistration

# Stable release v11.1.0
## Date: 14/05/2020

### New features

- 2dac93e **Event:** add `sendPhoneId()`

### Updates and fixes

- 07c46e2 **Event:** Update `_validateNavigationPath()`
- 865937d **Event:** Update `sendNavigation()`
- 0d2f2d0 **Event:** Update `sendProfileAction()`
- 55c1719 **Checkpoint:** Update models and responses for sitekey

### Examples

- 71adc03 **Example:** add editProfile

# Stable release v11.0.2
## Date: 13/05/2020

### Updates and fixes

- b75e974 **Response:** Update `ActivityNewsResponse`
- 75e4846 **Typo fix:** `FollowChainingRecsResponse`
- a82d9b8 23fd10a **Event:** Update `_validateNavigationPath()`
- 22c5e91 **Event:** Update `sendFeedButtonTapped()`
- 7be944a **Event:** Update `sendProfileView()`
- 4265c2c **Event:** Update `sendThumbnailImpression()`
- 7c12ee7 **Event:** Update `sendNavigation()`

### Examples

- 47a4635 **Example:** Add followFromLocationSearch
- 888ed72 **Examples:** Update likeFromExploreTag


# Stable release v11.0.1
## Date: 10/05/2020

- d72e401 **Event:** Update `_validateNavigationPath()`
- cfc9f57 **Realtime/Parser:** Update `parseMessage()`
- 948b8cc **Codestyle:** Update models and responses

# Stable release v11.0.0
## Date: 07/05/2020

### BACKWARD BREAKS ⚠️

- 033feab **Hashtag:** Remove deprecated `getRelated()`. Use `getAllFollowChainingRecs()` and `getFollowChainingRecs()` instead.
- 46a8881 **Timeline:** Update `getUserFeed()`: Arguments have changed.

```php
    public function getUserFeed(
        $userId,
+       $excludeComment = true,
        $maxId = null)
```

### Updates and fixes

#### > Recommended update

- 057c787 **Request:** Update headers
- 161a011 **Client:** Update headers
- 9c2858b **Business:** Update `getStatistics()`
- 31988aa **Request:** Update `_addDefaultHeaders()`
- 9961b82 **Highlight:** Update `getUserFeed()`
- e009aa5 **Response:** Delete deprecated response

### New features

- 27e06d0 **Hashtag:** Add `getAllFollowChainingRecs()`
- 0ea78ec **Hashtag:** Add `getFollowChainingRecs()`

# Stable release v10.7.0
## Date: 04/05/2020

### > Recommended update!

- 2837337 **Realtime:** Remove unused experiments
- 91d49f8 **Instagram:** Update `_login()`
- 117cf0a **Web:** Update `login()`
- 935bb2c **Composer:** add libsodium to the suggested extensions
- 71f27bb **Utils:** Add warning to `encryptPasswordForBrowser()`
- 2a747d4 **Utils:** `encryptPasswordForBrowser()`
- 04ba155 c53728b **Instagram:** Update `_login()`
- cf1e368 **Utils:** Add `getPhoneCountryCode()`
- b28c6f6 **Signature:** Update `generateSignature()`
- 803dc6d **Constants:** Update to 138.0.0.28.117

# Stable release v10.6.1
## Date: 30/04/2020

### Updates and fixes

- 78bcbdd **Event:** Update `sendOrganicMediaImpression()` and `sendOrganicViewedImpression()`
- 7293be4 **Event:** Update `sendMuteMedia()`
- fbace6e **Examples:** Update realtimeClient
- ed8bb9e 87cff80 **Realtime/Command:** Update `IrisSubscribe`
- 31c48ad **Realtime:** Update `receiveOfflineMessages()`
- d4046b9 **MQTT:** Update `_getClient()`
- 45b97f0 **Request:** Trick request to be `GenericResponse`
- 1d9a49d **Checkpoint:** Update `sendAcceptEscalationInformational()`
- 4a93722 **Exception:** Update `ServerMessageThrower`
- b236e84 **Example:** Update checkpoint

# Stable release v10.6.0
## Date: 26/04/2020

### New features

- 31157e6 **Event:** add `sendNewsfeedStoryImpression()`
- 8b6d19d **People:** add `getLeastInteractedWith()`

### Updates and fixes

- a57259b **Models:** Update properties
- f068dcf **Media:** Update `getComments()`
- 8ea5e58 **Event:** Update `sendOrganicTimespent()`
- 46c4208 **Event:** Update `_validateNavigationPath()`

# Stable release v10.5.1
## Date: 24/04/2020

### Updates and fixes

- 994d0cb **Minor fixes:** Doc and typo fixes

# Stable release v10.5.0
## Date: 23/04/2020

### New features


- 3a9245d **Instagram:** Add custom logger
- 593b57d **Model and Responses:** Add new story slider models
- 6c48379 Add and update Models and Responses
- c7f6523 **Story:** add `getStorySliderVoters()`

### Updates and fixes

- be6f9f1 **Utils:** Update `encryptPassword()`
- 64c5da8 **Media:** Update `getComments()`

# Stable release v10.4.0
## Date: 20/04/2020

### New features

- 713e229 **Live:** add `setSubscriptionPreference()`
- 20d5273 **People:** Add `favoriteForTv()` and `unfavoriteForTv()`
- da86dca **Event:** Add `sendIGTvNotificationPreference()`

### Updates and fixes

- 6f2d42d **Event:** Update `sendProfileAction()`

### New example

- d1a8469 **Example:** Add SettingUserNotifications

# Stable release v10.3.1
## Date: 19/04/2020

### New features

- 1715077 **Event:** Add `sendOrganicNumberOfLikes()`

### Updates and fixes

- bd277a **Event:** Update `sendOrganicMediaImpression()`
- f33e1f3 **Event:** Update `sendOrganicTimespent()`
- 22d2f1a **Example:** Update followFromMediaLikers

# Stable release v10.3.0
## Date: 17/04/2020

### New features

- 9f9ba19 **Event:** add `sendReelPlaybackNavigation()`

### Updates and fixes

- 8c955bc **Location|Hashtag|Internal:** Update `markStoryMediaSeen()`
- 8be01bc **Internal:** Update `markStoryMediaSeen()`

# Stable release v10.2.0
## Date: 16/04/2020

### New features

- 6af371b Internal: Add `getViewableStatuses()`

### Updates and fixes

- e05fc56 **Event:** Update `sendRecommendedUserImpression()`
- 3961862 **Internal:** Update `markStoryMediaSeen()`

# Stable release v10.1.3
## Date: 15/04/2020

- a7373ac **Event:** Update `_validateNavigationOptions()` and `_validateNavigationPath()`
- e11a18b **Examples:** add uploadPhotoLocation

# Stable release v10.1.2
## Date: 13/04/2020

### Updates and fixes

- d8e47f0 **Checkpoint:** Update functions

# Stable release v10.1.1
## Date: 11/04/2020

### Updates and fixes

- 65c1b6d **Event:** Update `_validateNavigationPath()`
- 6a4f50c **Realtime:** Update Skywalker parser
- d42449a **Checkpoint:** Update `_getWebFormRequest()`
- eed4e38 **Examples:** Update checkpoint
- b1ff530 **Models:** Update Web form models
- fd0b5ec **Response:** Update `WebCheckpointResponse()`

# Stable release v10.1.0
## Date: 09/04/2020


### New features

- b2a4362 **Checkpoint:** add `selectVerificationMethodForm()`

### Updates and fixes

- 48dcca6 **Events:** Update `_validateNavigationPath()`
- 1e410fb **Event:** Update `_validateNavigationPath()`
- 304af9c **Exception:** Update `ServerMessageThrower`
- cdcebd9 **Exception:** Added web form exceptions
- 0fa5e65 **Example:** Update checkpoint

### New example

- 7c74530 **Example:** ExtendingInstagram

# Stable release v10.0.1
## Date: 06/04/2020

### Updates & Fixes

- 3b7b8d5 **Instagram:** Update `_updateLoginState()`
- 0a6ef78 **Event:** Update `sendVideoAction()`
- ee71eb3 **Example:** Fix typo newsFeedToDirect

# Stable release v10.0.0
## Date: 04/04/2020

### BACKWARD BREAKS ⚠️

- 2cdb8ff **Checkpoint:** Renamed `getCompromisedPage()` to `getWebFormCheckpoint()`

Checkpoint example is updated with the latest checkpoint modifications and implements new functions related to web form challenges.

### New features

- 230588b **Event:** Add `sendRecommendedUserImpression()`
- a0a1b5d **Event:** Add `sendBadgingEvent()`
- 84d1e41 **People:** Add `getNewsInboxSeen()`

### Updates & Fixes

- 704a67c **Realtime:** Update DirectCommand
- 7e9be06 **People:** Update `getRecentActivityInbox()`
- a1ea02d **People:** Update `getNewsInboxSeen()`
- 8ce0b12 **Realtime/DirectCommand:** Update client context generation
- ffd98f8 **Direct:** Fixed inconsistent client context
- fd9e81f Add `will_sound_on` getter
- c1d10c9 **Debug:** Set `debugLog` to `false` by default
- d61f81b **Instagram:** Add sound enabled getter and setter
- 8853481 **Examples:** Refactored and implemented web form challenges
- 0805628 Add response and models for web form challenge
- 8e8a0a8 **Request:** Update `getRawResponse()`
- 784fbd2 **ServerMessageThrower:** Update `autoThrow()`
- 2de2c63 **Exception:** Add new web form checkpoint exceptions
- a8b0ab2 7bfea35 **Client:** Update `mapServerResponse()`
- 66f2daa **Example:** Update all upload examples
- 81997fd **Example:** Update uploadPhoto

### New examples

- e6a22ce **Example:** Add newsFeedToDirect

# Stable release v9.3.1
## Date: 01/04/2020

### Updates & Fixes

- f66bb79 **Events:** Update radio type properties
- c9bfc23 **Events:** Update `SendVideoAction` and `sendOrganicViewedImpression`

# Stable release v9.3.0
## Date: 30/03/2020

### New features

- 6bc84c3 **Event:** add `sendFeedButtonTapped()`

### Updates & Fixes

- 4afd41c **Event:** Update `sendSearchResults()`
- 8c83969 **Event:** Update navigation paths

# Stable release v9.2.0
## Date: 30/03/2020

### New features

- 1bd2f9d **People:** add `getUnfollowChainingCount()`

### Updates & Fixes

- cfacda0 6d92fa1 **Event:** Update `_validateNavigationPath`
- bd039e5 Event: Update `sendVideoAction()`
- 46c6091 da5ba05 **Event:** Update `sendOrganicMediaImpression`

# Stable release v9.1.1
## Date: 29/03/2020

- 7566854 **Example:** add sendImage
- a5862ff **General:** Update `is_charging` and `battery_level` getters


# Stable release v9.1.0
## Date: 28/03/2020

### New features

- 4c777f9 **Checkpoint:** add web form requests

### Updates & Fixes

- 7a3f91f  **Example:** Update viewStory
- a3f06c2 **Example:** Follow followFromUserFollowers
- 84bd1fb **Example:** Update checkpoint

# Stable release v9.0.2
## Date: 26/03/2020

### Updates & Fixes

- f7b2489 **Example:** Update checkpoint example
- 6d16035 **Checkpoint:** Update `sendChallenge()`
- 1a9ae97 **Checkpoint:** Update `sendCaptchaResponse()`
- da67c5a **Event:** Update `sendProfileAction()`
- 7b53781 **Example:** Update examples

# Stable release v9.0.1
## Date: 25/03/2020

### Updates & Fixes

- ac5c66c **Event:** Added module `reel_follow_list`
- 8d844e6 **Checkpoint:** Support for recaptcha
- 57527b0 **Examples:** Add removeFollowings
- f986cc7 **Event:** Update `sendFollowButtonTapped()`
- 06c7e8f **Example:** add removeFollowers
- dd7996c **Event:** Update `sendRemoveFollowerConfirmed()`
- 20a6e11 **Event:** Update `sendProfileAction()`
- c77dc16 **Event:** Update `sendProfileAction()`
- 6420785 **People:** Update `getSelfInfo()`
- 52ee132 **Push:** Fixed bad username/password error

# Stable release v9.0.0
## Date: 24/03/2020

### BACKWARD BREAKS ⚠️

- `sendFollowButtonTapped()` function has been updated and now the parameters passed to the function has changed:

```php
    public function sendFollowButtonTapped(
        $userId,
        $module,
        $navstack,
        $entryModule = null)
```

All examples were updated according to this change.

### Updates & Fixes

- 3badae0 63eeac2 **Event:** Update `sendFollowButtonTapped()`
- 45812e3 4287ba4 **Event:** Update `sendNavigation()`
- 19525ef **Event:** Update `_getNavDepthForModules()`
- ed7b3c0 7662859 **Event:** Update `sendProfileAction()`

### Example updates

- 34e751e **Example:** add followFromMediaLikers
- cb4ed0b **Example:** Delete unfollow example
- bc984fa **Example:** add followFromUserFollowers
- 7225259 **Example:** Update followFromSearch
- 80b295b b818ad1 **Examples:** add checkUserFollowers

# Stable release v8.4.1
## Date: 23/03/2020

This release contains final fixes for the realtime client. Also this releases fixes some errors in events.

### Updates & Fixes

- b9c260d f8bf7fc **Event:** Update `_validateNavigationPath()`
- e4150e1 **Event:** Update `sendNavigation()`
- 6303d0e **React/PersistentTrait**: Update `_cancelReconnectTimer()`

### New exaple:

- 57be033 **Example:** Get media likers

# Stable release v8.4.0
## Date: 20/03/2020

### New feature

- a8d5cd7 **Internal:** add `getFacebookDodResources()`

### Updates & Fixes

- 67c4cc9  8e5cc55 Update constants to `133.0.0.32.120`
- e0b342b **StorageHandler:** Update `setFbnsAuth()`
- 70f2e0c **Push/Fbns:** Update callback function
- c33b83e **Event:** Update `_validateNavigationPath()`


# Stable release v8.3.0
## Date: 17/03/2020

### New features

- 71289de **Account:** Add `logoutSession()`

### Updates & fixes

- ef4a7b8 **Composer:** Update FBNS-React
- c1c3070 **Examples:** Update followFromSearch
- 5ed9343 **Push:** Update FBNS Auth `update()`
- ec04dee **Example:** Update customSettings

# Stable release v8.2.0
## Date: 15/03/2020


### New features

- 2614dc9 **Event:** add `sendOrganicShareButton()`
- e05986e **Event:** Add `sendDirectShareMedia()`
- e3670d7 **Event:** Add `sendExternalShareOption()`

### Updates & fixes

- 98bf991 Models and responses: Update model and responses
- e55d3b5 **Event:** Update `sendOrganicShareButton()`
- e501d04 **Example:** update sendText
- 9c2b10a **Event:** Update `sendDirectUserSearchSelection()`
- a6c5717 **Event:** Update `sendOrganicMediaImpression()`
- f814294 **Example:** Update followFromSearch

### New examples

- 1aa2e66 **Example:** Add shareMedia



# Stable release v8.1.1-3
## Date: 12/03/2020


- Fix composer dependencies
- 06d41ed - **Event:** Update `sendOrganicViewedImpression()`
f3a7fb6 - **Push:** - Compatibility with FBNS-React


# Stable release v8.1.0
## Date: 09/03/2020

## New features

14d343b **Event:** Add `sendGroupCreation()`

## Updates & Fixes

19352eb **Composer:** Update PHP constraint (Now is compatible with php7.2)
27261a3 **Example:** Update sendText
b5558e5 **Event:** Update `_addEventBody()`
9af6739 **People:** Update `search()`
be6737f 886a85e **Event:** Update `_getNavDepthForModules()`
694f761 **Example:** Update checkpoint
6599414 **Checkpoint:** Update `getCompromisedPage()`
eafb827 **Realtime:** Update Realtime with latest FBNS-React (#25)

# Stable release v8.0.0
## Date: 08/03/2020

### Backward breaks ⚠️

- 398c856 **Event:** Update `sendSearchResultsPage()`
- c6080b7 518ecea **Event:** Update `sendSearchResults()`

**Notes:**
- A new param (`$module`) has been added to `sendSearchResultsPage()`.
- `$timeToSearch` in  `sendSearchResults()` has been replaced by `$module`.

### New features

- d2b0a21 **Event:** Add `sendRelatedHashtagItem()`

### Updates & Fixes

- f7a1806 **Response:** Update `TagFeedResponse`
- 44a34e1 **Event:** Update `sendNavigation()`
- 2b9ce88 **Event:** Update navigation modules

### Examples

- 5bd372d **Examples:** Update examples to match updated events.

# Stable release v7.1.0
## Date: 07/03/2020

### New features

- 5988028 **Event:** Add `sendUnfollowSuccessful()`
- fc8c00f **Event:** Add `sendOrganicActionMenu()`

### Updates and fixes

- 96bb7c9 **Composer:** Require PHP7.4
- 82232cb **Example:** update `followFromSearch`
- 286865b **Examples:** Update likeFromExploreTag
