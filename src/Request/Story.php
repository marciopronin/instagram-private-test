<?php

namespace InstagramAPI\Request;

use InstagramAPI\Constants;
use InstagramAPI\Request\Metadata\Internal as InternalMetadata;
use InstagramAPI\Response;
use InstagramAPI\Signatures;
use InstagramAPI\Utils;

/**
 * Functions for managing your story and interacting with other stories.
 *
 * @see Media for more functions that let you interact with the media.
 */
class Story extends RequestCollection
{
    /**
     * Uploads a photo to your Instagram story.
     *
     * @param string $photoFilename    The photo filename.
     * @param array  $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see Internal::configureSinglePhoto() for available metadata fields.
     */
    public function uploadPhoto(
        $photoFilename,
        array $externalMetadata = [])
    {
        return $this->ig->internal->uploadSinglePhoto(Constants::FEED_STORY, $photoFilename, null, $externalMetadata);
    }

    /**
     * Uploads a photo to your Instagram close friends story.
     *
     * @param string $photoFilename    The photo filename.
     * @param array  $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see Internal::configureSinglePhoto() for available metadata fields.
     * @see https://help.instagram.com/2183694401643300
     */
    public function uploadCloseFriendsPhoto(
        $photoFilename,
        array $externalMetadata = [])
    {
        $internalMetadata = new InternalMetadata(Utils::generateUploadId(true));
        $internalMetadata->setBestieMedia(true);

        return $this->ig->internal->uploadSinglePhoto(Constants::FEED_STORY, $photoFilename, $internalMetadata, $externalMetadata);
    }

    /**
     * Uploads a video to your Instagram story.
     *
     * @param string $videoFilename    The video filename.
     * @param array  $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video upload fails.
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see Internal::configureSingleVideo() for available metadata fields.
     */
    public function uploadVideo(
        $videoFilename,
        array $externalMetadata = [])
    {
        return $this->ig->internal->uploadSingleVideo(Constants::FEED_STORY, $videoFilename, null, $externalMetadata);
    }

    /**
     * Uploads a video to your Instagram close friends story.
     *
     * @param string $videoFilename    The video filename.
     * @param array  $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video upload fails.
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see Internal::configureSingleVideo() for available metadata fields.
     * @see https://help.instagram.com/2183694401643300
     */
    public function uploadCloseFriendsVideo(
        $videoFilename,
        array $externalMetadata = [])
    {
        $internalMetadata = new InternalMetadata();
        $internalMetadata->setBestieMedia(true);

        return $this->ig->internal->uploadSingleVideo(Constants::FEED_STORY, $videoFilename, $internalMetadata, $externalMetadata);
    }

    /**
     * Get the global story feed which contains everyone you follow.
     *
     * Note that users will eventually drop out of this list even though they
     * still have stories. So it's always safer to call getUserStoryFeed() if
     * a specific user's story feed matters to you.
     *
     * @param string     $reason        (optional) Reason for the request.
     * @param mixed|null $requestId
     * @param mixed|null $traySessionId
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsTrayFeedResponse
     *
     * @see Story::getUserStoryFeed()
     */
    public function getReelsTrayFeed(
        $reason = 'pull_to_refresh',
        $requestId = null,
        $traySessionId = null)
    {
        $request = $this->ig->request('feed/reels_tray/')
            ->setSignedPost(false)
            ->setRequestPriority(0)
            ->addPost('reason', $reason)
            ->addPost('timezone_offset', ($this->ig->getTimezoneOffset() !== null) ? $this->ig->getTimezoneOffset() : date('Z'))
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('reel_tray_impressions', json_encode([], JSON_FORCE_OBJECT))
            ->addPost('_uuid', $this->ig->uuid);

        if ($reason === 'pull_to_refresh') {
            $request->addPost('supported_capabilities_new', $this->ig->internal->getSupportedCapabilities());
        }

        if ($requestId !== null) {
            $request->addPost('request_id', $requestId);
        } else {
            $request->addPost('request_id', Signatures::generateUUID());
        }

        if ($traySessionId !== null) {
            $request->addPost('tray_session_id', $traySessionId);
        } else {
            $request->addPost('tray_session_id', Signatures::generateUUID());
        }

        if ($this->ig->isExperimentEnabled('25215', 0, true)) {
            $request->addPost('page_size', $this->ig->getExperimentParam('25215', 1, 50));
        }

        return $request->getResponse(new Response\ReelsTrayFeedResponse());
    }

    /**
     * Get multiple users' latest stories at once.
     *
     * @param string|string[] $feedList List of numerical UserPK IDs.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsTrayFeedResponse
     */
    public function getLatestStoryMedia(
        $feedList)
    {
        if (!is_array($feedList)) {
            $feedList = [$feedList];
        }

        foreach ($feedList as &$value) {
            $value = (string) $value;
        }
        unset($value); // Clear reference.

        return $this->ig->request('feed/get_latest_reel_media/')
            ->setSignedPost(false)
            ->addPost('user_ids', $feedList) // Must be string[] array.
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\ReelsTrayFeedResponse());
    }

    /**
     * Get multiple users' stories at once.
     *
     * @param string|string[] $feedList List of numerical UserPK IDs.
     * @param string          $source   (optional) Source app-module where the request was made.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsMediaResponse
     */
    public function getReelsMediaStream(
        $feedList,
        $source = 'feed_timeline')
    {
        if (!is_array($feedList)) {
            $feedList = [$feedList];
        }

        foreach ($feedList as &$value) {
            $value = (string) $value;
        }
        unset($value); // Clear reference.

        return $this->ig->request('feed/reels_media_stream/')
            ->addPost('supported_capabilities_new', $this->ig->internal->getSupportedCapabilities())
            ->addPost('reel_ids', $feedList) // Must be string[] array.
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('source', $source)
            ->addPost('batch_size', count($feedList))
            ->getResponse(new Response\ReelsMediaResponse());
    }

    /**
     * Get a specific user's story reel feed.
     *
     * This function gets the user's story Reel object directly, which always
     * exists and contains information about the user and their last story even
     * if that user doesn't have any active story anymore.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserReelMediaFeedResponse
     *
     * @see Story::getUserStoryFeed()
     */
    public function getUserReelMediaFeed(
        $userId)
    {
        return $this->ig->request("feed/user/{$userId}/reel_media/")
            ->getResponse(new Response\UserReelMediaFeedResponse());
    }

    /**
     * Get a specific user's story feed with broadcast details.
     *
     * This function gets the story in a roundabout way, with some extra details
     * about the "broadcast". But if there is no story available, this endpoint
     * gives you an empty response.
     *
     * NOTE: At least AT THIS MOMENT, this endpoint and the reels-tray endpoint
     * are the only ones that will give you people's "post_live" fields (their
     * saved Instagram Live Replays). The other "get user stories" funcs don't!
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserStoryFeedResponse
     *
     * @see Story::getUserReelMediaFeed()
     */
    public function getUserStoryFeed(
        $userId)
    {
        return $this->ig->request("feed/user/{$userId}/story/")
            ->addParam('supported_capabilities_new', $this->ig->internal->getSupportedCapabilities())
            ->getResponse(new Response\UserStoryFeedResponse());
    }

    /**
     * Get multiple users' story feeds (or specific highlight-details) at once.
     *
     * NOTE: Normally, you would only use this endpoint for stories (by passing
     * UserPK IDs as the parameter). But if you're looking at people's highlight
     * feeds (via `Highlight::getUserFeed()`), you may also sometimes discover
     * highlight entries that don't have any `items` array. In that case, you
     * are supposed to get the items for those highlights via this endpoint!
     * Simply pass their `id` values as the argument to this API to get details.
     *
     * @param string|string[] $feedList List of numerical UserPK IDs, OR highlight IDs (such as `highlight:123882132324123`).
     * @param string          $source   (optional) Source app-module where the request was made.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsMediaResponse
     *
     * @see Highlight::getUserFeed() More info about when to use this API for highlight-details.
     */
    public function getReelsMediaFeed(
        $feedList,
        $source = 'feed_timeline')
    {
        if (!is_array($feedList)) {
            $feedList = [$feedList];
        }

        foreach ($feedList as &$value) {
            $value = (string) $value;
        }
        unset($value); // Clear reference.

        return $this->ig->request('feed/reels_media/')
            ->addPost('supported_capabilities_new', $this->ig->internal->getSupportedCapabilities())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('user_ids', $feedList) // Must be string[] array.
            ->addPost('source', $source)
            ->getResponse(new Response\ReelsMediaResponse());
    }

    /**
     * Get injected stories (ads).
     *
     * @param string[]|int[] $storyUserIds  Array of numerical UserPK IDs.
     * @param string         $traySessionId UUID v4.
     * @param int            $entryIndex.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsMediaResponse
     */
    public function getInjectedStories(
        array $storyUserIds,
        $traySessionId,
        $entryIndex = 0)
    {
        if ($entryIndex < 0) {
            throw new \InvalidArgumentException('Entry index must be a positive number.');
        }

        if (!count($storyUserIds)) {
            throw new \InvalidArgumentException('Please provide at least one user.');
        }
        foreach ($storyUserIds as &$storyUserId) {
            if (!is_scalar($storyUserId)) {
                throw new \InvalidArgumentException('User identifier must be scalar.');
            } elseif (!ctype_digit($storyUserId) && (!is_int($storyUserId) || $storyUserId < 0)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid user identifier.', $storyUserId));
            }
            $storyUserId = (string) $storyUserId;
        }

        $request = $this->ig->request('feed/injected_reels_media/')
            ->setIsBodyCompressed(true)
            ->addHeader('X-Google-AD-ID', $this->ig->advertising_id)
            ->addHeader('X-CM-Bandwidth-KBPS', '-1.000')
            ->addHeader('X-CM-Latency', $this->ig->client->latency)
            ->addHeader('X-Ads-Opt-Out', '0')
            ->addHeader('X-DEVICE-ID', $this->ig->uuid)
            ->addPost('num_items_in_pool', '0')
            ->addPost('has_camera_permission', $this->ig->getCameraEnabled())
            ->addPost('is_prefetch', 'true')
            ->addPost('is_ads_sensitive', 'false')
            ->addPost('is_carry_over_first_page', 'false')
            ->addPost('client_doc_id', '33469793817914901585514067303')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('phone_id', $this->ig->phone_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('inserted_netego_indices', [])
            ->addPost('ad_and_netego_request_information', [])
            ->addPost('inserted_ad_indices', [])
            ->addPost('ad_request_index', '0')
            ->addPost('is_inventory_based_request_enabled', 'true')
            ->addPost('is_ad_pod_enabled', 'true')
            ->addPost('battery_level', $this->ig->getBatteryLevel())
            ->addPost('tray_session_id', $traySessionId)
            ->addPost('viewer_session_id', $traySessionId)
            ->addPost('reel_position', '0')
            ->addPost('is_charging', $this->ig->getIsDeviceCharging())
            ->addPost('will_sound_on', (int) $this->ig->getSoundEnabled())
            ->addPost('is_dark_mode', '0')
            ->addPost('tray_user_ids', $storyUserIds)
            ->addPost('is_media_based_insertion_enabled', 'true')
            ->addPost('entry_point_index', ($entryIndex !== 0) ? strval($entryIndex) : '0')
            ->addPost('earliest_request_position', '0')
            ->addPost('is_first_page', ($entryIndex !== 0) ? 'false' : 'true');

        return $request->getResponse(new Response\ReelsMediaResponse());
    }

    /**
     * Get your archived story media feed.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ArchivedStoriesFeedResponse
     */
    public function getArchivedStoriesFeed()
    {
        return $this->ig->request('archive/reel/day_shells/')
            ->addParam('include_suggested_highlights', false)
            ->addParam('is_in_archive_home', true)
            ->addParam('include_cover', 0)
            ->addParam('timezone_offset', ($this->ig->getTimezoneOffset() !== null) ? $this->ig->getTimezoneOffset() : date('Z'))
            ->getResponse(new Response\ArchivedStoriesFeedResponse());
    }

    /**
     * Get archive badge count.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ArchiveBadgeCountResponse
     */
    public function getArchiveBadgeCount()
    {
        return $this->ig->request('archive/reel/profile_archive_badge/')
            ->setSignedPost(false)
            ->addPost('timezone_offset', ($this->ig->getTimezoneOffset() !== null) ? $this->ig->getTimezoneOffset() : date('Z'))
            ->addPost('_uuid', $this->ig->uuid)
            //->addParam('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\ArchiveBadgeCountResponse());
    }

    /**
     * Get the list of users who have seen one of your story items.
     *
     * Note that this only works for your own story items. Instagram doesn't
     * allow you to see the viewer list for other people's stories!
     *
     * @param string      $storyPk The story media item's PK in Instagram's internal format (ie "3482384834").
     * @param string|null $maxId   Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelMediaViewerResponse
     */
    public function getStoryItemViewers(
        $storyPk,
        $maxId = null)
    {
        $request = $this->ig->request("media/{$storyPk}/list_reel_media_viewer/")
            ->addParam('supported_capabilities_new', $this->ig->internal->getSupportedCapabilities());
        if ($maxId !== null) {
            $request->addParam('max_id', $maxId);
        }

        return $request->getResponse(new Response\ReelMediaViewerResponse());
    }

    /**
     * Vote on a story poll.
     *
     * Note that once you vote on a story poll, you cannot change your vote.
     *
     * @param string $storyId      The story media item's ID in Instagram's internal format (ie "1542304813904481224_6112344004").
     * @param string $pollId       The poll ID in Instagram's internal format (ie "17956159684032257").
     * @param int    $votingOption Value that represents the voting option of the voter. 0 for the first option, 1 for the second option.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelMediaViewerResponse
     */
    public function votePollStory(
        $storyId,
        $pollId,
        $votingOption)
    {
        if (($votingOption !== 0) && ($votingOption !== 1)) {
            throw new \InvalidArgumentException('You must provide a valid value for voting option.');
        }

        return $this->ig->request("media/{$storyId}/{$pollId}/story_poll_vote/")
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('radio_type', $this->ig->radio_type)
            ->addPost('vote', $votingOption)
            ->getResponse(new Response\ReelMediaViewerResponse());
    }

    /**
     * Vote on a story slider.
     *
     * Note that once you vote on a story poll, you cannot change your vote.
     *
     * @param string $storyId      The story media item's ID in Instagram's internal format (ie "1542304813904481224_6112344004").
     * @param string $sliderId     The slider ID in Instagram's internal format (ie "17956159684032257").
     * @param float  $votingOption Value that represents the voting option of the voter. Should be a float from 0 to 1 (ie "0.25").
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelMediaViewerResponse
     */
    public function voteSliderStory(
        $storyId,
        $sliderId,
        $votingOption)
    {
        if ($votingOption < 0 || $votingOption > 1) {
            throw new \InvalidArgumentException('You must provide a valid value from 0 to 1 for voting option.');
        }

        return $this->ig->request("media/{$storyId}/{$sliderId}/story_slider_vote/")
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('radio_type', $this->ig->radio_type)
            ->addPost('vote', $votingOption)
            ->getResponse(new Response\ReelMediaViewerResponse());
    }

    /**
     * Get the list of users who have voted an option in a story poll.
     *
     * Note that this only works for your own story polls. Instagram doesn't
     * allow you to see the results from other people's polls!
     *
     * @param string      $storyId      The story media item's ID in Instagram's internal format (ie "1542304813904481224_6112344004").
     * @param string      $pollId       The poll ID in Instagram's internal format (ie "17956159684032257").
     * @param int         $votingOption Value that represents the voting option of the voter. 0 for the first option, 1 for the second option.
     * @param string|null $maxId        Next "maximum ID", used for pagination.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\StoryPollVotersResponse
     */
    public function getStoryPollVoters(
        $storyId,
        $pollId,
        $votingOption,
        $maxId = null)
    {
        if (($votingOption !== 0) && ($votingOption !== 1)) {
            throw new \InvalidArgumentException('You must provide a valid value for voting option.');
        }

        $request = $this->ig->request("media/{$storyId}/{$pollId}/story_poll_voters/")
            ->addParam('vote', $votingOption);

        if ($maxId !== null) {
            $request->addParam('max_id', $maxId);
        }

        return $request->getResponse(new Response\StoryPollVotersResponse());
    }

    /**
     * Respond to a question sticker on a story.
     *
     * @param string $storyId         The story media item's ID in Instagram's internal format (ie "1542304813904481224_6112344004").
     * @param string $questionId      The question ID in Instagram's internal format (ie "17956159684032257").
     * @param string $responseText    The text to respond to the question with. (Note: Android App limits this to 94 characters).
     * @param string $clientContext   The Client Context.
     * @param string $containerModule The module.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function answerStoryQuestion(
        $storyId,
        $questionId,
        $responseText,
        $clientContext,
        $containerModule = 'reel_profile')
    {
        $request = $this->ig->request("media/{$storyId}/{$questionId}/story_question_response/")
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('response', $responseText)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('type', 'text')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('container_module', $containerModule)
            ->addPost('client_context', $clientContext)
            ->addPost('mutation_token', $clientContext);

        if (!$this->ig->getIsAndroid()) {
            $request->addPost('delivery_class', 'organic');
        }

        return $request->getResponse(new Response\GenericResponse());
    }

    /**
     * Get all responses of a story question.
     *
     * @param string      $storyId    The story media item's ID in Instagram's internal format (ie "1542304813904481224").
     * @param string      $questionId The question ID in Instagram's internal format (ie "17956159684032257").
     * @param string|null $maxId      Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\StoryAnswersResponse
     */
    public function getStoryAnswers(
         $storyId,
         $questionId,
         $maxId = null)
    {
        $request = $this->ig->request("media/{$storyId}/{$questionId}/story_question_responses/");

        if ($maxId !== null) {
            $request->addParam('max_id', $maxId);
        }

        return $request->getResponse(new Response\StoryAnswersResponse());
    }

    /**
     * Deletes an answer to a story question.
     *
     * Note that you must be the owner of the story
     * to delete an answer!
     *
     * @param string $storyId  The story media item's ID in Instagram's internal format (ie "1542304813904481224").
     * @param string $answerId The question ID in Instagram's internal format (ie "17956159684032257").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function deleteStoryQuestionAnswer(
        $storyId,
        $answerId)
    {
        return $this->ig->request("media/{$storyId}/delete_story_question_response/")
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('question_id', $answerId)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Gets the created story countdowns of the current account.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\StoryCountdownsResponse
     */
    public function getStoryCountdowns()
    {
        return $this->ig->request('media/story_countdowns/')
            ->getResponse(new Response\StoryCountdownsResponse());
    }

    /**
     * Follows a story countdown to subscribe to a notification when the countdown is finished.
     *
     * @param string $countdownId The countdown ID in Instagram's internal format (ie "17956159684032257").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function followStoryCountdown(
        $countdownId)
    {
        return $this->ig->request("media/{$countdownId}/follow_story_countdown/")
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Unfollows a story countdown to unsubscribe from a notification when the countdown is finished.
     *
     * @param string $countdownId The countdown ID in Instagram's internal format (ie "17956159684032257").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function unfollowStoryCountdown(
        $countdownId)
    {
        return $this->ig->request("media/{$countdownId}/unfollow_story_countdown/")
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Respond to a quiz sticker on a story.
     *
     * Note that once you vote on a story quiz, you cannot change your vote.
     *
     * @param string $storyId        The story media item's ID in Instagram's internal format (ie "1542304813904481224_6112344004").
     * @param string $quizId         The quiz ID in Instagram's internal format (ie "17956159684032257").
     * @param int    $selectedOption The option you select (Can be 0, 1, 2, 3).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function answerStoryQuiz(
        $storyId,
        $quizId,
        $selectedOption)
    {
        return $this->ig->request("media/{$storyId}/{$quizId}/story_quiz_answer/")
            ->setSignedPost(false)
            ->addPost('answer', $selectedOption)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get all responses of a story quiz.
     *
     * @param string      $storyId The story media item's ID in Instagram's internal format (ie "1542304813904481224_6112344004").
     * @param string      $quizId  The question ID in Instagram's internal format (ie "17956159684032257").
     * @param string|null $maxId   Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\StoryQuizAnswersResponse
     */
    public function getStoryQuizAnswers(
        $storyId,
        $quizId,
        $maxId = null)
    {
        $request = $this->ig->request("media/{$storyId}/{$quizId}/story_quiz_participants/");

        if ($maxId !== null) {
            $request->addParam('max_id', $maxId);
        }

        return $request->getResponse(new Response\StoryQuizAnswersResponse());
    }

    /**
     * Get all votes of a story slider.
     *
     * @param string      $storyId  The story media item's ID in Instagram's internal format (ie "1542304813904481224_6112344004").
     * @param string      $sliderId The slider ID in Instagram's internal format (ie "17956159684032257").
     * @param string|null $maxId    Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\StorySliderVotersResponse
     */
    public function getStorySliderVoters(
        $storyId,
        $sliderId,
        $maxId = null)
    {
        $request = $this->ig->request("media/{$storyId}/{$sliderId}/story_slider_voters/");

        if ($maxId !== null) {
            $request->addParam('max_id', $maxId);
        }

        return $request->getResponse(new Response\StorySliderVotersResponse());
    }

    /**
     * Get list of charities for use in the donation sticker on stories.
     *
     * @param string|null $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CharitiesListResponse
     */
    public function getCharities(
        $maxId = null)
    {
        $request = $this->ig->request('fundraiser/story_charities_nullstate/');

        if ($maxId !== null) {
            $request->addParam('max_id', $maxId);
        }

        return $request->getResponse(new Response\CharitiesListResponse());
    }

    /**
     * Searches a list of charities for use in the donation sticker on stories.
     *
     * @param string      $query Search query.
     * @param string|null $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CharitiesListResponse
     */
    public function searchCharities(
        $query,
        $maxId = null)
    {
        $request = $this->ig->request('fundraiser/story_charities_search/')
            ->addParam('query', $query);

        if ($maxId !== null) {
            $request->addParam('max_id', $maxId);
        }

        return $request->getResponse(new Response\CharitiesListResponse());
    }

    /**
     * Creates the array for a donation sticker.
     *
     * @param \InstagramAPI\Response\Model\User $charityUser          The User object of the charity's Instagram account.
     * @param float                             $x
     * @param float                             $y
     * @param float                             $width
     * @param float                             $height
     * @param float                             $rotation
     * @param string|null                       $title                The title of the donation sticker.
     * @param string                            $titleColor           Hex color code for the title color.
     * @param string                            $subtitleColor        Hex color code for the subtitle color.
     * @param string                            $buttonTextColor      Hex color code for the button text color.
     * @param string                            $startBackgroundColor
     * @param string                            $endBackgroundColor
     *
     * @return array
     *
     * @see Story::getCharities()
     * @see Story::searchCharities()
     * @see Story::uploadPhoto()
     * @see Story::uploadVideo()
     */
    public function createDonateSticker(
        $charityUser,
        $x = 0.5,
        $y = 0.5,
        $width = 0.6805556,
        $height = 0.254738,
        $rotation = 0.0,
        $title = null,
        $titleColor = '#000000',
        $subtitleColor = '#999999ff',
        $buttonTextColor = '#3897f0',
        $startBackgroundColor = '#fafafa',
        $endBackgroundColor = '#fafafa')
    {
        return [
            [
                'x'                      => $x,
                'y'                      => $y,
                'z'                      => 0,
                'width'                  => $width,
                'height'                 => $height,
                'rotation'               => $rotation,
                'title'                  => ($title !== null ? strtoupper($title) : ('HELP SUPPORT '.strtoupper($charityUser->getFullName()))),
                'ig_charity_id'          => $charityUser->getPk(),
                'title_color'            => $titleColor,
                'subtitle_color'         => $subtitleColor,
                'button_text_color'      => $buttonTextColor,
                'start_background_color' => $startBackgroundColor,
                'end_background_color'   => $endBackgroundColor,
                'source_name'            => 'sticker_tray',
                'user'                   => [
                    'username'                      => $charityUser->getUsername(),
                    'full_name'                     => $charityUser->getFullName(),
                    'profile_pic_url'               => $charityUser->getProfilePicUrl(),
                    'profile_pic_id'                => $charityUser->getProfilePicId(),
                    'has_anonymous_profile_picture' => $charityUser->getHasAnonymousProfilePicture(),
                    'id'                            => $charityUser->getPk(),
                    'usertag_review_enabled'        => false,
                    'mutual_followers_count'        => $charityUser->getMutualFollowersCount(),
                    'show_besties_badge'            => false,
                    'is_private'                    => $charityUser->getIsPrivate(),
                    'allowed_commenter_type'        => 'any',
                    'is_verified'                   => $charityUser->getIsVerified(),
                    'is_new'                        => false,
                    'feed_post_reshare_disabled'    => false,
                ],
                'is_sticker' => true,
            ],
        ];
    }

    /**
     * Mark story media items as seen.
     *
     * The various story-related endpoints only give you lists of story media.
     * They don't actually mark any stories as "seen", so the user doesn't know
     * that you've seen their story. Actually marking the story as "seen" is
     * done via this endpoint instead. The official app calls this endpoint
     * periodically (with 1 or more items at a time) while watching a story.
     *
     * Tip: You can pass in the whole "getItems()" array from a user's story
     * feed (retrieved via any of the other story endpoints), to easily mark
     * all of that user's story media items as seen.
     *
     * WARNING: ONLY USE *THIS* ENDPOINT IF THE STORIES CAME FROM THE ENDPOINTS
     * IN *THIS* REQUEST-COLLECTION FILE: From "getReelsTrayFeed()" or the
     * user-specific story endpoints. Do NOT use this endpoint if the stories
     * came from any OTHER request-collections, such as Location-based stories!
     * Other request-collections have THEIR OWN special story-marking functions!
     *
     * @param Response\Model\Item[] $items Array of one or more story media Items.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaSeenResponse
     *
     * @see Location::markStoryMediaSeen()
     * @see Hashtag::markStoryMediaSeen()
     */
    public function markMediaSeen(
        array $items)
    {
        // NOTE: NULL = Use each item's owner ID as the "source ID".
        return $this->ig->internal->markStoryMediaSeen($items, null);
    }

    /**
     * Get your story settings.
     *
     * This has information such as your story messaging mode (who can reply
     * to your story), and the list of users you have blocked from seeing your
     * stories.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelSettingsResponse
     */
    public function getReelSettings()
    {
        return $this->ig->request('users/reel_settings/')
            ->getResponse(new Response\ReelSettingsResponse());
    }

    /**
     * Set your story settings.
     *
     * @param string      $messagePrefs      Who can reply to your story. Valid values are "anyone" (meaning
     *                                       your followers), "following" (followers that you follow back),
     *                                       or "off" (meaning that nobody can reply to your story).
     * @param bool|null   $allowStoryReshare Allow story reshare.
     * @param string|null $autoArchive       Auto archive stories for viewing them later. It will appear in your
     *                                       archive once it has disappeared from your story feed. Valid values
     *                                       "on" and "off".
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelSettingsResponse
     */
    public function setReelSettings(
        $messagePrefs,
        $allowStoryReshare = null,
        $autoArchive = null)
    {
        if (!in_array($messagePrefs, ['anyone', 'following', 'off'])) {
            throw new \InvalidArgumentException('You must provide a valid message preference value.');
        }

        $request = $this->ig->request('users/set_reel_settings/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('message_prefs', $messagePrefs);

        if ($allowStoryReshare !== null) {
            if (!is_bool($allowStoryReshare)) {
                throw new \InvalidArgumentException('You must provide a valid value for allowing story reshare.');
            }
            $request->addPost('allow_story_reshare', $allowStoryReshare);
        }

        if ($autoArchive !== null) {
            if (!in_array($autoArchive, ['on', 'off'])) {
                throw new \InvalidArgumentException('You must provide a valid value for auto archive.');
            }
            $request->addPost('reel_auto_archive', $autoArchive);
        }

        return $request->getResponse(new Response\ReelSettingsResponse());
    }

    /**
     * Get private stories members.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PrivateStoriesMembersResponse
     */
    public function getPrivateStoriesMembers()
    {
        return $this->ig->request('stories/private_stories/members/')
            ->getResponse(new Response\PrivateStoriesMembersResponse());
    }

    /**
     * Add private stories members.
     *
     * @param string $userId Numerical UserPK ID.
     * @param string $module Module.
     * @param string $source Source module.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function addPrivateStoriesMember(
        $userId,
        $module = 'audience_selection',
        $source = 'story_share_sheet')
    {
        return $this->ig->request('stories/private_stories/add_member/')
            ->setSignedPost(false)
            ->addPost('module', $module)
            ->addPost('source', $source)
            ->addPost('user_id', $userId)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Remove private stories members.
     *
     * @param string $userId Numerical UserPK ID.
     * @param string $module Module.
     * @param string $source Source module.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function removePrivateStoriesMember(
        $userId,
        $module = 'audience_selection',
        $source = 'story_share_sheet')
    {
        return $this->ig->request('stories/private_stories/remove_member/')
            ->setSignedPost(false)
            ->addPost('module', $module)
            ->addPost('source', $source)
            ->addPost('user_id', $userId)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Bulk Add/Remove viewers from your private stories allow list.
     *
     * @param string[] $add    UserIds to add to your private stories allow list.
     * @param string[] $remove UserIds to remove from private stories allow list.
     * @param string   $module (optional) Module.
     * @param string   $source (optional) Source module.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function setPrivateStoriesMembers(
        array $add,
        array $remove,
        $module = 'audience_selection',
        $source = 'story_share_sheet')
    {
        return $this->ig->request('stories/private_stories/bulk_update_members/')
            ->setSignedPost(false)
            ->addPost('module', $module)
            ->addPost('source', $source)
           ->addPost('added_user_ids', json_encode($add))
           ->addPost('removed_user_ids', json_encode($remove))
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get story allow list viewers.
     *
     * @param string $storyId The story media item's ID in Instagram's internal format (ie "1542304813904481224").
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PrivateStoriesMembersResponse
     */
    public function getStoryAllowlist(
        $storyId)
    {
        return $this->ig->request("stories/private_stories/media/{$storyId}/allowlist/")
            ->getResponse(new Response\PrivateStoriesMembersResponse());
    }

    /**
     * Add story allow list viewer.
     *
     * @param string $storyId The story media item's ID in Instagram's internal format (ie "1542304813904481224").
     * @param string $userId  Numerical UserPK ID.
     * @param string $module  Module.
     * @param string $source  Source module.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function addViewerToAllowList(
        $storyId,
        $userId,
        $module = 'audience_selection',
        $source = 'self_reel')
    {
        return $this->ig->request("stories/private_stories/{$storyId}/add_viewer/")
            ->setSignedPost(false)
            ->addPost('module', $module)
            ->addPost('source', $source)
            ->addPost('user_id', $userId)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Remove story allow list viewer.
     *
     * @param string $storyId The story media item's ID in Instagram's internal format (ie "1542304813904481224").
     * @param string $userId  Numerical UserPK ID.
     * @param string $module  Module.
     * @param string $source  Source module.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function removeViewerToAllowList(
        $storyId,
        $userId,
        $module = 'audience_selection',
        $source = 'self_reel')
    {
        return $this->ig->request("stories/private_stories/{$storyId}/remove_viewer/")
            ->setSignedPost(false)
            ->addPost('module', $module)
            ->addPost('source', $source)
            ->addPost('user_id', $userId)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Bulk Add/Remove viewers from your story allow list.
     *
     * Note: You probably should not touch $module and $source as there is only one way to modify your story allow list.
     *
     * @param string   $storyId The story media item's ID in Instagram's internal format (ie "1542304813904481224").
     * @param string[] $add     UserIds to add to your private story allow list.
     * @param string[] $remove  UserIds to remove from your private story allow list.
     * @param string   $module  (optional) From which app module (page) you have change your private story allow list.
     * @param string   $source  (optional) Source page of app-module of where you changed your private story allow list.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function setStoryAllowList(
        $storyId,
        array $add,
        array $remove,
        $module = 'audience_selection',
        $source = 'self_reel')
    {
        return $this->ig->request("stories/private_stories/media/{$storyId}/allowlist/edit/")
            ->setSignedPost(false)
            ->addPost('module', $module)
            ->addPost('media_id', $storyId)
            ->addPost('source', $source)
            ->addPost('added_user_ids', json_encode($add))
            ->addPost('removed_user_ids', json_encode($remove))
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Send story like.
     *
     * @param string $storyId         The story media item's ID in Instagram's internal format (ie "1542304813904481224").
     * @param string $traySessionId   UUID v4.
     * @param string $viewerSessionId UUID v4.
     * @param string $containerModule The module.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function sendLike(
        $storyId,
        $traySessionId,
        $viewerSessionId,
        $containerModule = 'reel_profile')
    {
        return $this->_sendStoryInteraction('send_story_like', $storyId, $traySessionId, $viewerSessionId, $containerModule);
    }

    /**
     * Unsend story like.
     *
     * @param string $storyId         The story media item's ID in Instagram's internal format (ie "1542304813904481224").
     * @param string $traySessionId   UUID v4.
     * @param string $viewerSessionId UUID v4.
     * @param string $containerModule The module.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function unsendLike(
        $storyId,
        $traySessionId,
        $viewerSessionId,
        $containerModule = 'reel_profile')
    {
        return $this->_sendStoryInteraction('unsend_story_like', $storyId, $traySessionId, $viewerSessionId, $containerModule);
    }

    /**
     * Crosspost story.
     *
     * @param string $storyId         The story media item's ID in Instagram's internal format (ie "1542304813904481224").
     * @param string $destinationId   Destination ID.
     * @param string $destinationType USER or PAGE.
     * @param mixed  $containerModule
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function crosspost(
        $storyId,
        $destinationId,
        $destinationType,
        $containerModule = 'ig_self_story')
    {
        return $this->ig->request("media/{$storyId}/share/")
            ->addPost('xpost_surface', $containerModule)
            ->addPost('share_to_fb_destination_type', $destinationType)
            ->addPost('media_id', $storyId)
            ->addPost('share_to_fb_destination_id', $destinationId)
            ->addPost('share_to_facebook', '1')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('waterfall_id', Signatures::generateUUID())
            ->addPost('use_fb_post_time', '1')
            ->addPost('no_token_crosspost', '1')
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Perform story interactions.
     *
     * @param string $endpoint        Story interactions endpoint.
     * @param string $storyId         The story media item's ID in Instagram's internal format (ie "1542304813904481224").
     * @param string $traySessionId   UUID v4.
     * @param string $viewerSessionId UUID v4.
     * @param string $containerModule The module.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    protected function _sendStoryInteraction(
        $endpoint,
        $storyId,
        $traySessionId,
        $viewerSessionId,
        $containerModule = 'reel_profile')
    {
        return $this->ig->request("story_interactions/{$endpoint}/")
            ->addPost('media_id', $storyId)
            ->addPost('container_module', $containerModule)
            ->addPost('tray_session_id', $traySessionId)
            ->addPost('viewer_session_id', $viewerSessionId)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->getResponse(new Response\GenericResponse());
    }
}
