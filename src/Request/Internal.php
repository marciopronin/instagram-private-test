<?php

namespace InstagramAPI\Request;

use GuzzleHttp\Psr7\LimitStream;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Utils as GuzzleUtils;
use InstagramAPI\Constants;
use InstagramAPI\Exception\Checkpoint\ChallengeRequiredException;
use InstagramAPI\Exception\Checkpoint\CheckpointRequiredException;
use InstagramAPI\Exception\ConsentRequiredException;
use InstagramAPI\Exception\FeedbackRequiredException;
use InstagramAPI\Exception\InstagramException;
use InstagramAPI\Exception\LoginRequiredException;
use InstagramAPI\Exception\NetworkException;
use InstagramAPI\Exception\RetryUploadFlowException;
use InstagramAPI\Exception\SettingsException;
use InstagramAPI\Exception\ThrottledException;
use InstagramAPI\Exception\UploadFailedException;
use InstagramAPI\Media\MediaDetails;
use InstagramAPI\Media\PDQ\PDQHasher as PDQHasher;
use InstagramAPI\Media\Video\FFmpeg;
use InstagramAPI\Media\Video\InstagramThumbnail;
use InstagramAPI\Media\Video\VideoDetails;
use InstagramAPI\Request;
use InstagramAPI\Request\Metadata\Internal as InternalMetadata;
use InstagramAPI\Response;
use InstagramAPI\Signatures;
use InstagramAPI\Utils;
use Winbox\Args;

/**
 * Collection of various INTERNAL library functions.
 *
 * THESE FUNCTIONS ARE NOT FOR PUBLIC USE! DO NOT TOUCH!
 */
class Internal extends RequestCollection
{
    /** @var int Number of retries for each video chunk. */
    const MAX_CHUNK_RETRIES = 5;

    /** @var int Number of retries for resumable uploader. */
    const MAX_RESUMABLE_RETRIES = 15;

    /** @var int Minimum video chunk size in bytes. */
    const MIN_CHUNK_SIZE = 204800;

    /** @var int Maximum video chunk size in bytes. */
    const MAX_CHUNK_SIZE = 5242880;

    /** @var int Number of retries for each media configuration. */
    protected $_maxConfigureRetries = 25;

    /**
     * Set max configure retries.
     *
     * @param int $value Max Configure retries.
     */
    public function setMaxConfigureRetries(
        $value)
    {
        if (is_int($value) === false || ($value < 0 || $value > 25)) {
            throw new \InvalidArgumentException('The supplied value for max configure retries is not valid.');
        }
        $this->_maxConfigureRetries = $value;
    }

    /**
     * Get max configure retries.
     */
    public function getMaxConfigureRetries()
    {
        return $this->_maxConfigureRetries;
    }

    /**
     * UPLOADS A *SINGLE* PHOTO.
     *
     * This is NOT used for albums!
     *
     * @param int                   $targetFeed       One of the FEED_X constants.
     * @param string                $photoFilename    The photo filename.
     * @param InternalMetadata|null $internalMetadata (optional) Internal library-generated metadata object.
     * @param array                 $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see Internal::configureSinglePhoto() for available metadata fields.
     */
    public function uploadSinglePhoto(
        $targetFeed,
        $photoFilename,
        InternalMetadata $internalMetadata = null,
        array $externalMetadata = [])
    {
        // Make sure we only allow these particular feeds for this function.
        if ($targetFeed !== Constants::FEED_TIMELINE
            && $targetFeed !== Constants::FEED_STORY
            && $targetFeed !== Constants::FEED_DIRECT
            && $targetFeed !== Constants::FEED_DIRECT_STORY
            && $targetFeed !== Constants::FEED_TV
            && $targetFeed !== Constants::FEED_REELS
        ) {
            throw new \InvalidArgumentException(sprintf('Bad target feed "%s".', $targetFeed));
        }

        // Validate and prepare internal metadata object.
        if ($internalMetadata === null) {
            $internalMetadata = new InternalMetadata(Utils::generateUploadId(true));
        }

        try {
            if ($internalMetadata->getPhotoDetails() === null) {
                $internalMetadata->setPhotoDetails($targetFeed, $photoFilename);
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                sprintf('Failed to get photo details: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        // Perform the upload.
        $this->uploadPhotoData($targetFeed, $internalMetadata);

        if ($targetFeed === Constants::FEED_DIRECT) {
            return $internalMetadata;
        }

        // Configure the uploaded photo/share to IGTV and attach it to our timeline/IGTV.
        try {
            /** @var \InstagramAPI\Response\ConfigureResponse $configure */
            $configure = $this->ig->internal->configureWithRetries(
                function () use ($targetFeed, $internalMetadata, $externalMetadata) {
                    // Configure the uploaded image and attach it to our timeline/story/IGTV.
                    $isShared = null;
                    if (isset($externalMetadata['share_to_fb_destination_id'])) {
                        $externalMetadata['client_shared_at'] = time();
                        $isShared = $this->configureSinglePhoto($targetFeed, $internalMetadata, $externalMetadata);
                        $externalMetadata['crosspost'] = true;
                    }

                    $response = $this->configureSinglePhoto($targetFeed, $internalMetadata, $externalMetadata);

                    return ($isShared === null) ? $response : $isShared;
                }
            );

            try {
                list($hash, $quality) = @PDQHasher::computeHashAndQualityFromFilename($photoFilename, false, false);
                if ($hash !== null) {
                    $pdqHashes[] = $hash->toHexString();
                    $this->updateMediaWithPdqHashes($internalMetadata->getUploadId(), $pdqHashes);
                }
            } catch (\Exception $e) {
                // pass
            }
        } catch (InstagramException $e) {
            // Pass Instagram's error as is.
            throw $e;
        } catch (\Exception $e) {
            // Wrap runtime errors.
            throw new UploadFailedException(
                sprintf('Upload of "%s" failed: %s', basename($photoFilename), $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        return $configure;
    }

    /**
     * Upload the data for a photo to Instagram.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException
     */
    public function uploadPhotoData(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        // Make sure we have photo details.
        if ($internalMetadata->getPhotoDetails() === null) {
            throw new \InvalidArgumentException('Photo details are missing from the internal metadata.');
        }

        try {
            $this->_uploadResumablePhoto($targetFeed, $internalMetadata);
            // The following code was to choose between uploading the photo in
            // one piece of upload it in segments. Instagram seems to be forcing
            // resumable upload.
            /*
            if ($this->_useResumablePhotoUploader($targetFeed, $internalMetadata)) {
                $this->_uploadResumablePhoto($targetFeed, $internalMetadata);
            } else {
                $internalMetadata->setPhotoUploadResponse(
                    $this->_uploadPhotoInOnePiece($targetFeed, $internalMetadata)
                );
            }
            */
        } catch (InstagramException $e) {
            // Pass Instagram's error as is.
            throw $e;
        } catch (\Exception $e) {
            // Wrap runtime errors.
            throw new UploadFailedException(
                sprintf(
                    'Upload of "%s" failed: %s',
                    $internalMetadata->getPhotoDetails()->getBasename(),
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Configures parameters for a *SINGLE* uploaded photo file.
     *
     * WARNING TO CONTRIBUTORS: THIS IS ONLY FOR *TIMELINE* AND *STORY* -PHOTOS-.
     * USE "configureTimelineAlbum()" FOR ALBUMS and "configureSingleVideo()" FOR VIDEOS.
     * AND IF FUTURE INSTAGRAM FEATURES NEED CONFIGURATION AND ARE NON-TRIVIAL,
     * GIVE THEM THEIR OWN FUNCTION LIKE WE DID WITH "configureTimelineAlbum()",
     * TO AVOID ADDING BUGGY AND UNMAINTAINABLE SPIDERWEB CODE!
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     * @param array            $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     */
    public function configureSinglePhoto(
        $targetFeed,
        InternalMetadata $internalMetadata,
        array $externalMetadata = [])
    {
        // Determine the target endpoint for the photo.
        switch ($targetFeed) {
        case Constants::FEED_TIMELINE:
            $endpoint = 'media/configure/';
            break;
        case Constants::FEED_DIRECT_STORY:
        case Constants::FEED_STORY:
            $endpoint = 'media/configure_to_story/';
            break;
        case Constants::FEED_TV:
            $endpoint = 'media/configure_to_igtv/';
            break;
        case Constants::FEED_REELS:
            $endpoint = 'media/configure_to_clips/';
            break;
        default:
            throw new \InvalidArgumentException(sprintf('Bad target feed "%s".', $targetFeed));
        }

        // Available external metadata parameters:
        /** @var string Caption to use for the media. */
        $captionText = isset($externalMetadata['caption']) ? $externalMetadata['caption'] : '';
        /** @var string Accesibility caption to use for the media. */
        $altText = isset($externalMetadata['custom_accessibility_caption']) ? $externalMetadata['custom_accessibility_caption'] : null;
        /** @var Response\Model\Location|null A Location object describing where
         * the media was taken. */
        $location = (isset($externalMetadata['location'])) ? $externalMetadata['location'] : null;
        /** @var array|null Array of story location sticker instructions. ONLY
         * USED FOR STORY MEDIA! */
        $locationSticker = (isset($externalMetadata['location_sticker']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['location_sticker'] : null;
        /** @var array|null Array of usertagging instructions, in the format
         * [['position'=>[0.5,0.5], 'user_id'=>'123'], ...]. ONLY FOR TIMELINE PHOTOS! */
        $usertags = (isset($externalMetadata['usertags']) && $targetFeed == Constants::FEED_TIMELINE) ? $externalMetadata['usertags'] : null;
        /** @var array Coauthor ID */
        $coauthor = (isset($externalMetadata['invite_coauthor_user_id']) && $targetFeed == Constants::FEED_TIMELINE) ? $externalMetadata['invite_coauthor_user_id'] : null;
        /** @var string|null Link to attach to the media. ONLY USED FOR STORY MEDIA,
         * AND YOU MUST HAVE A BUSINESS INSTAGRAM ACCOUNT TO POST A STORY LINK! */
        $link = (isset($externalMetadata['link']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['link'] : null;
        /** @var string|null Link to attach to the media. ONLY USED FOR STORY MEDIA,
         * AND YOU MUST HAVE A BUSINESS INSTAGRAM ACCOUNT TO POST A STORY LINK! */
        $linkSticker = (isset($externalMetadata['link_sticker']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['link_sticker'] : null;
        /** @var void Photo filter. THIS DOES NOTHING! All real filters are done in the mobile app. */
        // $filter = isset($externalMetadata['filter']) ? $externalMetadata['filter'] : null;
        $filter = null; // COMMENTED OUT SO USERS UNDERSTAND THEY CAN'T USE THIS!
        /** @var array Hashtags to use for the media. ONLY STORY MEDIA! */
        $hashtags = (isset($externalMetadata['hashtags']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['hashtags'] : null;
        /** @var array Story captions text metadata to use for the media. ONLY STORY MEDIA! */
        $storyTextMetadata = (isset($externalMetadata['story_caption_text_metadata']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_caption_text_metadata'] : null;
        /** @var array Story captions to use for the media. ONLY STORY MEDIA! */
        $storyCaptions = (isset($externalMetadata['story_captions']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_captions'] : null;
        /** @var array Mentions to use for the media. ONLY STORY MEDIA! */
        $storyMentions = (isset($externalMetadata['story_mentions']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_mentions'] : null;
        /** @var array Story poll to use for the media. ONLY STORY MEDIA! */
        $storyPoll = (isset($externalMetadata['story_polls']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_polls'] : null;
        /** @var array Story slider to use for the media. ONLY STORY MEDIA! */
        $storySlider = (isset($externalMetadata['story_sliders']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_sliders'] : null;
        /** @var array Story question to use for the media. ONLY STORY MEDIA */
        $storyQuestion = (isset($externalMetadata['story_questions']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_questions'] : null;
        /** @var array Story countdown to use for the media. ONLY STORY MEDIA */
        $storyCountdown = (isset($externalMetadata['story_countdowns']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_countdowns'] : null;
        /** @var array Story quiz to use for the media. ONLY STORY MEDIA */
        $storyQuiz = (isset($externalMetadata['story_quizs']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_quizs'] : null;
        /** @var array Chat sticker to use for the media. ONLY STORY MEDIA */
        $chatSticker = (isset($externalMetadata['chat_sticker']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['chat_sticker'] : null;
        /** @var array Story fundraiser to use for the media. ONLY STORY MEDIA */
        $storyFundraisers = (isset($externalMetadata['story_fundraisers']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_fundraisers'] : null;
        /** @var array Story emoji reaction to use for the media. ONLY STORY MEDIA */
        $storyEmojiReaction = (isset($externalMetadata['story_emoji_reaction']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_emoji_reaction'] : null;
        /** @var array Attached media used to share media to story feed. ONLY STORY MEDIA! */
        $attachedMedia = (isset($externalMetadata['attached_media']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['attached_media'] : null;
        /** @var array Product Tags to use for the media. ONLY FOR TIMELINE PHOTOS! */
        $productTags = (isset($externalMetadata['product_tags']) && $targetFeed == Constants::FEED_TIMELINE) ? $externalMetadata['product_tags'] : null;
        /** @var array Title for IGTV. */
        $igtvTitle = (isset($externalMetadata['igtv_title']) && $targetFeed == Constants::FEED_TV) ? $externalMetadata['igtv_title'] : null;
        /** @var array Series ID for IGTV. */
        $igtvSeriesId = (isset($externalMetadata['igtv_series_id']) && $targetFeed == Constants::FEED_TV) ? $externalMetadata['igtv_series_id'] : null;
        /** @var array IGTV composer session ID. ONLY TV MEDIA! */
        $igtvSessionId = (isset($externalMetadata['igtv_session_id']) && $targetFeed == Constants::FEED_TV) ? $externalMetadata['igtv_session_id'] : null;
        /** @var array REELS (CLIPS) share preview to feed. ONLY REEL MEDIA! */
        $reelShareToFeed = (isset($externalMetadata['reel_share_preview_to_feed']) && $targetFeed == Constants::FEED_REELS) ? $externalMetadata['reel_share_preview_to_feed'] : null;

        // Fix very bad external user-metadata values.
        if (!is_string($captionText)) {
            $captionText = '';
        }

        // Critically important internal library-generated metadata parameters:
        /** @var string The ID of the entry to configure. */
        $uploadId = $internalMetadata->getUploadId();
        /** @var int Width of the photo. */
        $photoWidth = $internalMetadata->getPhotoDetails()->getWidth();
        /** @var int Height of the photo. */
        $photoHeight = $internalMetadata->getPhotoDetails()->getHeight();

        // Build the request...
        $request = $this->ig->request($endpoint)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addHeader('retry_context', json_encode($this->_getRetryContext()))
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('nav_chain', $this->ig->getNavChain())
            ->addPost('edits',
                [
                    'crop_original_size'    => [(float) $photoWidth, (float) $photoHeight],
                    'crop_zoom'             => 1.0,
                    'crop_center'           => [0.0, -0.0],
                ])
            ->addPost('device',
                [
                    'manufacturer'      => $this->ig->device->getManufacturer(),
                    'model'             => $this->ig->device->getModel(),
                    'android_version'   => intval($this->ig->device->getAndroidVersion()),
                    'android_release'   => $this->ig->device->getAndroidRelease(),
                ])
            ->addPost('extra',
                [
                    'source_width'  => $photoWidth,
                    'source_height' => $photoHeight,
                ]);

        // 'ig_android_eu_configure_disabled', 'route_to_us'
        if ($this->ig->isExperimentEnabled('26156', 0, false) || !$this->ig->getIsEUUser()) {
            $request->addHeader('X-IG-EU-CONFIGURE-DISABLED', 'true');
        }

        $stickerIds = [];
        $tapModels = [];
        $staticModels = [];

        switch ($targetFeed) {
            case Constants::FEED_TIMELINE:
                $date = date('Y:m:d H:i:s');
                $request
                    ->addPost('timezone_offset', ($this->ig->getTimezoneOffset() !== null) ? $this->ig->getTimezoneOffset() : date('Z'))
                    ->addPost('caption', $captionText)
                    ->addPost('source_type', '4')
                    ->addPost('include_e2ee_mentioned_user_list', '1')
                    ->addPost('scene_capture_type', '')
                    //->addPost('media_folder', 'Camera')
                    ->addPost('upload_id', $uploadId);
                   // ->addPost('configure_mode', Constants::SHARE_TYPE['FOLLOWERS_SHARE']); // 0 - FOLLOWERS_SHARE

                if ($internalMetadata->isBestieMedia()) {
                    $request->addPost('audience', 'besties');
                } else {
                    $request->addPost('audience', 'default');
                }

                if ($usertags !== null) {
                    Utils::throwIfInvalidUsertags($usertags);
                    $request->addPost('usertags', json_encode($usertags));

                    if ($coauthor !== null) {
                        if (is_array($coauthor) && count($coauthor) > 1) {
                            if (count($coauthor) > 5) {
                                throw new \InvalidArgumentException('Maximum coauthors allowed is 5.');
                            }
                            $request->addPost('invite_coauthor_user_ids', $coauthor);
                        } else {
                            if (is_array($coauthor)) {
                                $coauthor = $coauthor[0];
                            }
                            $request->addPost('invite_coauthor_user_id', $coauthor);
                        }
                        $request->addPost('internal_features', 'coauthor_post');
                    }
                }
                if ($productTags !== null) {
                    Utils::throwIfInvalidProductTags($productTags);
                    $request->addPost('product_tags', json_encode($productTags));
                }
                if ($altText !== null) {
                    $request->addPost('custom_accessibility_caption', $altText);
                }
                if (isset($externalMetadata['share_to_fb_destination_id']) && isset($externalMetadata['fb_access_token'])) {
                    $request->addPost('share_to_fb_destination_id', $externalMetadata['share_to_fb_destination_id'])
                            ->addPost('fb_access_token', $externalMetadata['fb_access_token'])
                            ->addPost('share_to_fb_destination_type', isset($externalMetadata['share_to_fb_destination_type']) ? $externalMetadata['share_to_fb_destination_type'] : 'USER');
                }
                break;
            case Constants::FEED_STORY:
                if ($internalMetadata->isBestieMedia()) {
                    $request->addPost('audience', 'besties');
                } else {
                    $request->addPost('audience', 'default');
                }

                $request
                    ->addHeader('retry_context', json_encode($this->_getRetryContext()))
                    ->addPost('include_e2ee_mentioned_user_list', '1')
                    ->addPost('supported_capabilities_new', $this->getSupportedCapabilities())
                    ->addPost('client_shared_at', isset($externalMetadata['client_shared_at']) ? (string) $externalMetadata['client_shared_at'] : (string) time())
                    ->addPost('source_type', '3')
                    ->addPost('configure_mode', '1')
                    ->addPost('allow_multi_configures', '1')
                    //->addPost('configure_mode', Constants::SHARE_TYPE['REEL_SHARE']) // 2 - REEL_SHARE
                    ->addPost('client_timestamp', (string) (time() - mt_rand(3, 10)))
                    ->addPost('publish_id', 1)
                    ->addPost('upload_id', $uploadId)
                    ->addPost('original_media_type', '1') // photo
                    ->addPost('camera_session_id', Signatures::generateUUID())
                    ->addPost('scene_capture_type', '')
                    ->addPost('creation_surface', 'camera')
                    ->addPost('capture_type', 'normal')
                    ->addPost('has_original_sound', '1')
                    ->addPost('creation_surface', 'camera')
                    ->addPost('composition_id', Signatures::generateUUID())
                    ->addPost('attempt_id', Signatures::generateUUID())
                    ->addPost('camera_entry_point', '360');

                $request->addPost('media_transformation_info', json_encode([
                    'width'                 => $photoWidth,
                    'height'                => $photoHeight,
                    'x_transform'           => 0,
                    'y_transform'           => 0,
                    'zoom'                  => number_format(1, 1),
                    'rotation'              => number_format(0, 1),
                    'background_coverage'   => number_format(0, 1),
                ]));

                /*
                if (is_string($link) && Utils::hasValidWebURLSyntax($link)) {
                    $story_cta = '[{"links":[{"webUri":'.json_encode($link).'}]}]';
                    $request->addPost('story_cta', $story_cta);
                }
                */
                if ($captionText !== '' && $storyTextMetadata !== null) {
                    $request->addPost('caption', $captionText)
                            ->addPost('rich_text_format_types', json_encode(['modern_refreshed_v2']))
                            ->addPost('text_metadata', $storyTextMetadata);

                    if ($storyCaptions !== null) {
                        $request->addPost('story_captions', $storyCaptions);
                    }
                }
                if ($linkSticker !== null) {
                    Utils::throwIfInvalidStoryLinkSticker($linkSticker);

                    $tapModels[] = $linkSticker;
                    $stickerIds[] = 'link_sticker_default';
                }
                if ($productTags !== null) {
                    Utils::throwIfInvalidProductTags($productTags);
                    $request->addPost('product_tags', json_encode($productTags));
                }
                if ($hashtags !== null) {
                    Utils::throwIfInvalidStoryHashtagSticker($hashtags);

                    foreach ($hashtags as $hashtag) {
                        $tapModels[] = $hashtag;
                        $stickerIds[] = 'hashtag_sticker';
                    }
                }
                if ($locationSticker !== null && $location !== null) {
                    Utils::throwIfInvalidStoryLocationSticker($locationSticker);

                    $tapModels[] = $locationSticker;
                    $stickerIds[] = 'location_sticker';
                }
                if ($storyMentions !== null) {
                    Utils::throwIfInvalidStoryMentionSticker($storyMentions);

                    foreach ($storyMentions as $storyMention) {
                        $tapModels[] = $storyMention;
                        if ($storyMention['is_sticker']) {
                            $stickerIds[] = 'mention_sticker';
                        } else {
                            $static = $storyMention;
                            $static['str_id'] = 'text_sticker_'.Signatures::generateUUID();
                            $static['sticker_type'] = 'text_sticker';
                            $staticModels[] = $static;
                        }
                    }
                }
                if ($storyPoll !== null) {
                    Utils::throwIfInvalidStoryPoll($storyPoll);
                    $request
                        ->addPost('internal_features', 'poll_sticker_v2');

                    $tapModels[] = $storyPoll[0];
                    $stickerIds[] = 'polling_sticker_v2';
                }
                if ($storySlider !== null) {
                    Utils::throwIfInvalidStorySlider($storySlider);
                    $request
                        ->addPost('story_sliders', json_encode($storySlider));

                    $stickerIds[] = 'emoji_slider_'.$storySlider[0]['emoji'];
                }
                if ($storyEmojiReaction !== null) {
                    //Utils::throwIfInvalidStorySlider($storyEmojiReaction);
                    $request
                        ->addPost('story_reaction_stickers', json_encode($storyEmojiReaction));

                    $tapModels[] = $storyEmojiReaction;
                    $stickerIds[] = 'emoji_reaction_'.$storyEmojiReaction[0]['emoji'];
                }
                if ($storyQuestion !== null) {
                    Utils::throwIfInvalidStoryQuestion($storyQuestion);
                    $request
                        ->addPost('story_questions', json_encode($storyQuestion));

                    $stickerIds[] = 'question_sticker_ama';
                }
                if ($storyCountdown !== null) {
                    Utils::throwIfInvalidStoryCountdown($storyCountdown);
                    $request
                        ->addPost('story_countdowns', json_encode($storyCountdown));

                    $stickerIds[] = 'countdown_sticker_time';
                }
                if ($storyQuiz !== null) {
                    Utils::throwIfInvalidStoryQuiz($storyQuiz);
                    $request
                        ->addPost('story_quizs', json_encode($storyQuiz));

                    $stickerIds[] = 'quiz_story_sticker_default';
                }
                if ($chatSticker !== null) {
                    Utils::throwIfInvalidChatSticker($chatSticker);
                    $request
                        ->addPost('story_chats', json_encode($chatSticker));

                    $stickerIds[] = 'chat_sticker_bundle_id';
                }
                if ($storyFundraisers !== null) {
                    $request
                        ->addPost('story_fundraisers', json_encode($storyFundraisers));

                    $stickerIds[] = 'fundraiser_sticker_id';
                }
                if ($attachedMedia !== null) {
                    Utils::throwIfInvalidAttachedMedia($attachedMedia);
                    $request
                        ->addPost('attached_media', json_encode($attachedMedia));

                    $stickerIds[] = 'media_simple_'.reset($attachedMedia)['media_id'];
                }
                if (isset($externalMetadata['share_to_fb_destination_id'])) {
                    $request->addPost('xpost_surface', 'ig_story_composer');
                }
                if (isset($externalMetadata['share_to_fb_destination_id']) && isset($externalMetadata['crosspost'])) {
                    $request->addPost('share_to_fb_destination_id', $externalMetadata['share_to_fb_destination_id'])
                            ->addPost('share_to_facebook', '1')
                            ->addPost('share_to_fb_destination_type', isset($externalMetadata['share_to_fb_destination_type']) ? $externalMetadata['share_to_fb_destination_type'] : 'USER');

                    if (isset($externalMetadata['fb_access_token'])) {
                        $request->addPost('fb_access_token', $externalMetadata['fb_access_token']);
                    } else {
                        $request->addPost('no_token_crosspost', '1');
                    }
                }
                if (isset($externalMetadata['xpost'])) {
                    $request->addPost('xpost_surface', 'ig_story_composer');
                }
                break;
            case Constants::FEED_DIRECT_STORY:
                $request
                    ->addPost('recipient_users', $internalMetadata->getDirectUsers())
                    ->addPost('thread_ids', $internalMetadata->getDirectThreads())
                    ->addPost('client_shared_at', (string) time())
                    ->addPost('source_type', '3')
                    ->addPost('configure_mode', Constants::SHARE_TYPE['REEL_SHARE']) // 2 - REEL_SHARE (stories)
                    ->addPost('client_timestamp', (string) (time() - mt_rand(3, 10)))
                    ->addPost('upload_id', $uploadId);

                if ($internalMetadata->getStoryViewMode() !== null) {
                    $request->addPost('view_mode', $internalMetadata->getStoryViewMode());
                }
                break;
            case Constants::FEED_TV:
            case Constants::FEED_REELS:
                if ($targetFeed === Constants::FEED_TV) {
                    if ($igtvTitle === null) {
                        throw new \InvalidArgumentException('You must provide a title for the media.');
                    }
                    if ($igtvSessionId === null) {
                        throw new \InvalidArgumentException('You must provide a session ID for the media.');
                    }
                    $request
                        ->addHeader('is_igtv_video', '1')
                        ->addPost('title', $igtvTitle)
                        ->addPost('igtv_composer_session_id', $igtvSessionId);

                    if ($igtvSeriesId !== null) {
                        $request->addPost('igtv_series_id', $igtvSeriesId);
                    }
                } else {
                    $request->addPost('is_clips_video', '1')
                            ->addPost('audience', 'default');

                    if ($reelShareToFeed !== null) {
                        $request->addPost('clips_share_preview_to_feed', $reelShareToFeed === true ? '1' : '0');
                    } else {
                        $request->addPost('clips_share_preview_to_feed', '1');
                    }
                }

                $request
                    ->addPost('igtv_ads_toggled_on', '0')
                    ->addPost('timezone_offset', ($this->ig->getTimezoneOffset() !== null) ? $this->ig->getTimezoneOffset() : date('Z'))
                    ->addPost('source_type', '4')
                    ->addPost('keep_shoppable_products', '0')
                    ->addPost('igtv_share_preview_to_feed', '1')
                    ->addPost('caption', $captionText)
                    ->addPost('upload_id', $uploadId);

                break;
        }

        $request->addPost('tap_models', json_encode($tapModels, JSON_UNESCAPED_SLASHES));
        if (!empty($staticModels)) {
            $request->addPost('static_models', json_encode($staticModels, JSON_UNESCAPED_SLASHES));
        }

        if (!empty($stickerIds)) {
            $storyStickerIds = null;
            if (count($stickerIds) === 1) {
                $storyStickerIds = $stickerIds[0];
            } else {
                $storyStickerIds = implode(',', $stickerIds);
            }
            $request->addPost('story_sticker_ids', $storyStickerIds);
        }

        if ($location instanceof Response\Model\Location) {
            if ($targetFeed === Constants::FEED_TIMELINE) {
                $request->addPost('location', Utils::buildMediaLocationJSON($location));

                $request
                    ->addPost('geotag_enabled', '1')
                    ->addPost('posting_latitude', $location->getLat())
                    ->addPost('posting_longitude', $location->getLng())
                    ->addPost('media_latitude', $location->getLat())
                    ->addPost('media_longitude', $location->getLng());
            }
        }

        $configure = $request->getResponse(new Response\ConfigureResponse());

        return $configure;
    }

    /**
     * Uploads a raw video file.
     *
     * @param int                   $targetFeed       One of the FEED_X constants.
     * @param string                $videoFilename    The video filename.
     * @param InternalMetadata|null $internalMetadata (optional) Internal library-generated metadata object.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video upload fails.
     *
     * @return InternalMetadata Updated internal metadata object.
     */
    public function uploadVideo(
        $targetFeed,
        $videoFilename,
        InternalMetadata $internalMetadata = null)
    {
        if ($internalMetadata === null) {
            $internalMetadata = new InternalMetadata();
        }

        try {
            if ($internalMetadata->getVideoDetails() === null) {
                $internalMetadata->setVideoDetails($targetFeed, $videoFilename);
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                sprintf('Failed to get photo details: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        try {
            $this->_uploadSegmentedVideo($targetFeed, $internalMetadata);

            // The following code was to choose between uploading the video using
            // segmented uplaoders or resumable uploader. Instagram seems to be forcing
            // segmented uploader.
            /*
            if ($this->_useSegmentedVideoUploader($targetFeed, $internalMetadata)) {
                $this->_uploadSegmentedVideo($targetFeed, $internalMetadata);
            } elseif ($this->_useResumableVideoUploader($targetFeed, $internalMetadata)) {
                $this->_uploadResumableVideo($targetFeed, $internalMetadata);
            } else {
                // Request parameters for uploading a new video.
                $internalMetadata->setVideoUploadUrls($this->_requestVideoUploadURL($targetFeed, $internalMetadata));

                // Attempt to upload the video data.
                $internalMetadata->setVideoUploadResponse($this->_uploadVideoChunks($targetFeed, $internalMetadata));
            }
            */
        } catch (InstagramException $e) {
            // Pass Instagram's error as is.
            throw $e;
        } catch (\Exception $e) {
            // Wrap runtime errors.
            throw new UploadFailedException(
                sprintf('Upload of "%s" failed: %s', basename($videoFilename), $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        return $internalMetadata;
    }

    /**
     * UPLOADS A *SINGLE* VIDEO.
     *
     * This is NOT used for albums!
     *
     * @param int                   $targetFeed       One of the FEED_X constants.
     * @param string                $videoFilename    The video filename.
     * @param InternalMetadata|null $internalMetadata (optional) Internal library-generated metadata object.
     * @param array                 $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video upload fails.
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see Internal::configureSingleVideo() for available metadata fields.
     */
    public function uploadSingleVideo(
        $targetFeed,
        $videoFilename,
        InternalMetadata $internalMetadata = null,
        array $externalMetadata = [])
    {
        // Make sure we only allow these particular feeds for this function.
        if ($targetFeed !== Constants::FEED_TIMELINE
            && $targetFeed !== Constants::FEED_STORY
            && $targetFeed !== Constants::FEED_DIRECT_STORY
            && $targetFeed !== Constants::FEED_TV
            && $targetFeed !== Constants::FEED_REELS
        ) {
            throw new \InvalidArgumentException(sprintf('Bad target feed "%s".', $targetFeed));
        }

        // Attempt to upload the video.
        $internalMetadata = $this->uploadVideo($targetFeed, $videoFilename, $internalMetadata);

        // Attempt to upload the thumbnail, associated with our video's ID.
        $pdqHashes = $this->uploadVideoThumbnail($targetFeed, $internalMetadata, $externalMetadata);

        // Configure the uploaded video and attach it to our timeline/story.
        try {
            /** @var \InstagramAPI\Response\ConfigureResponse $configure */
            $configure = $this->ig->internal->configureWithRetries(
                function () use ($targetFeed, $internalMetadata, $externalMetadata) {
                    // Attempt to configure video parameters.
                    $isShared = null;
                    if (isset($externalMetadata['share_to_fb_destination_id'])) {
                        $externalMetadata['client_shared_at'] = time();
                        $isShared = $this->configureSingleVideo($targetFeed, $internalMetadata, $externalMetadata);
                        $externalMetadata['crosspost'] = true;
                    }
                    $response = $this->configureSingleVideo($targetFeed, $internalMetadata, $externalMetadata);

                    return ($isShared === null) ? $response : $isShared;
                }
            );
            // $this->updateMediaWithPdqHashes($internalMetadata->getUploadId(), $pdqHashes);
        } catch (InstagramException $e) {
            // Pass Instagram's error as is.
            throw $e;
        } catch (NetworkException $e) {
            throw $e;
        } catch (\Exception $e) {
            // Wrap runtime errors.
            throw new UploadFailedException(
                sprintf('Upload of "%s" failed: %s', basename($videoFilename), $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        return $configure;
    }

    /**
     * Performs a resumable upload of a photo file, with support for retries.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     * @param array            $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException
     *
     * @return string[]        PDQ Hashes.
     */
    public function uploadVideoThumbnail(
        $targetFeed,
        InternalMetadata $internalMetadata,
        array $externalMetadata = [])
    {
        if ($internalMetadata->getVideoDetails() === null) {
            throw new \InvalidArgumentException('Video details are missing from the internal metadata.');
        }

        try {
            // Automatically crop&resize the thumbnail to Instagram's requirements.
            $options = ['targetFeed' => $targetFeed];
            if (isset($externalMetadata['thumbnail_timestamp'])) {
                $options['thumbnailTimestamp'] = $externalMetadata['thumbnail_timestamp'];
            }
            if (($targetFeed === Constants::FEED_TV || $targetFeed === Constants::FEED_TIMELINE || $targetFeed === Constants::FEED_REELS) && isset($externalMetadata['cover_photo'])) {
                $videoThumbnail = new \InstagramAPI\Media\Photo\InstagramPhoto($externalMetadata['cover_photo'], $options);
            } else {
                $videoThumbnail = new InstagramThumbnail(
                    $internalMetadata->getVideoDetails()->getFilename(),
                    $options
                );
            }

            $pdqHashes = [];
            foreach (Constants::PDQ_VIDEO_TIME_FRAMES as $timeFrame) {
                try {
                    $options['thumbnailTimestamp'] = $timeFrame;
                    $frame = new InstagramThumbnail($internalMetadata->getVideoDetails()->getFilename(), $options);
                    list($hash, $quality) = @PDQHasher::computeHashAndQualityFromFilename($frame->getFile(), false, false);
                    if ($hash !== null) {
                        $pdqHashes[] = $hash->toHexString();
                    }
                } catch (\Exception $e) {
                    // pass
                }
            }

            // Validate and upload the thumbnail.
            if ($targetFeed !== Constants::FEED_STORY) {
                $internalMetadata->setPhotoDetails($targetFeed, $videoThumbnail->getFile());
                $this->uploadPhotoData($targetFeed, $internalMetadata);
            }

            return $pdqHashes;
        } catch (InstagramException $e) {
            // Pass Instagram's error as is.
            throw $e;
        } catch (\Exception $e) {
            // Wrap runtime errors.
            throw new UploadFailedException(
                sprintf('Upload of video thumbnail failed: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Asks Instagram for parameters for uploading a new video.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @throws \InstagramAPI\Exception\InstagramException If the request fails.
     *
     * @return \InstagramAPI\Response\UploadJobVideoResponse
     */
    protected function _requestVideoUploadURL(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        $request = $this->ig->request('upload/video/')
            ->setSignedPost(false)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid);

        foreach ($this->_getVideoUploadParams($targetFeed, $internalMetadata) as $key => $value) {
            $request->addPost($key, $value);
        }

        // Perform the "pre-upload" API request.
        /** @var Response\UploadJobVideoResponse $response */
        $response = $request->getResponse(new Response\UploadJobVideoResponse());

        return $response;
    }

    /**
     * Configures parameters for a *SINGLE* uploaded video file.
     *
     * WARNING TO CONTRIBUTORS: THIS IS ONLY FOR *TIMELINE* AND *STORY* -VIDEOS-.
     * USE "configureTimelineAlbum()" FOR ALBUMS and "configureSinglePhoto()" FOR PHOTOS.
     * AND IF FUTURE INSTAGRAM FEATURES NEED CONFIGURATION AND ARE NON-TRIVIAL,
     * GIVE THEM THEIR OWN FUNCTION LIKE WE DID WITH "configureTimelineAlbum()",
     * TO AVOID ADDING BUGGY AND UNMAINTAINABLE SPIDERWEB CODE!
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     * @param array            $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     */
    public function configureSingleVideo(
        $targetFeed,
        InternalMetadata $internalMetadata,
        array $externalMetadata = [])
    {
        $uploadParams = $this->_getVideoUploadParams($targetFeed, $internalMetadata);

        $this->ig->event->sendConfigureMedia(
            'attempt',
            $internalMetadata->getUploadId(),
            $uploadParams['media_type'],
            $internalMetadata->getWaterfallID()
        );

        // Determine the target endpoint for the video.
        switch ($targetFeed) {
        case Constants::FEED_TIMELINE:
            $endpoint = 'media/configure/';
            break;
        case Constants::FEED_DIRECT_STORY:
        case Constants::FEED_STORY:
            $endpoint = 'media/configure_to_story/';
            break;
        case Constants::FEED_TV:
            $endpoint = 'media/configure_to_igtv/';
            break;
        case Constants::FEED_REELS:
            $endpoint = 'media/configure_to_clips/';
            break;
        default:
            throw new \InvalidArgumentException(sprintf('Bad target feed "%s".', $targetFeed));
        }

        // Available external metadata parameters:
        /** @var string Caption to use for the media. */
        $captionText = isset($externalMetadata['caption']) ? $externalMetadata['caption'] : '';
        /** @var string[]|null Array of numerical UserPK IDs of people tagged in
         * your video. ONLY USED IN STORY VIDEOS! TODO: Actually, it's not even
         * implemented for stories. */
        $usertags = (isset($externalMetadata['usertags'])) ? $externalMetadata['usertags'] : null;
        /** @var array Coauthor ID */
        $coauthor = (isset($externalMetadata['invite_coauthor_user_id']) && ($targetFeed == Constants::FEED_TIMELINE || $targetFeed == Constants::FEED_REELS)) ? $externalMetadata['invite_coauthor_user_id'] : null;
        /** @var Response\Model\Location|null A Location object describing where
         * the media was taken. */
        $location = (isset($externalMetadata['location'])) ? $externalMetadata['location'] : null;
        /** @var array|null Array of story location sticker instructions. ONLY
         * USED FOR STORY MEDIA! */
        $locationSticker = (isset($externalMetadata['location_sticker']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['location_sticker'] : null;
        /** @var string|null Link to attach to the media. ONLY USED FOR STORY MEDIA,
         * AND YOU MUST HAVE A BUSINESS INSTAGRAM ACCOUNT TO POST A STORY LINK! */
        $link = (isset($externalMetadata['link']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['link'] : null;
        /** @var string|null Link to attach to the media. ONLY USED FOR STORY MEDIA,
         * AND YOU MUST HAVE A BUSINESS INSTAGRAM ACCOUNT TO POST A STORY LINK! */
        $linkSticker = (isset($externalMetadata['link_sticker']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['link_sticker'] : null;
        /** @var array Hashtags to use for the media. ONLY STORY MEDIA! */
        $hashtags = (isset($externalMetadata['hashtags']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['hashtags'] : null;
        /** @var array Story captions text metadata to use for the media. ONLY STORY MEDIA! */
        $storyTextMetadata = (isset($externalMetadata['story_caption_text_metadata']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_caption_text_metadata'] : null;
        /** @var array Story captions to use for the media. ONLY STORY MEDIA! */
        $storyCaptions = (isset($externalMetadata['story_captions']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_captions'] : null;
        /** @var array Mentions to use for the media. ONLY STORY MEDIA! */
        $storyMentions = (isset($externalMetadata['story_mentions']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_mentions'] : null;
        /** @var array Story music to use for the media. ONLY STORY MEDIA */
        $storyMusic = (isset($externalMetadata['story_music']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_music'] : null;
        /** @var array Story poll to use for the media. ONLY STORY MEDIA! */
        $storyPoll = (isset($externalMetadata['story_polls']) && ($targetFeed == Constants::FEED_STORY || $targetFeed == Constants::FEED_REELS)) ? $externalMetadata['story_polls'] : null;
        /** @var array Attached media used to share media to story feed. ONLY STORY MEDIA! */
        $storySlider = (isset($externalMetadata['story_sliders']) && ($targetFeed == Constants::FEED_STORY || $targetFeed == Constants::FEED_REELS)) ? $externalMetadata['story_sliders'] : null;
        /** @var array Story question to use for the media. ONLY STORY MEDIA */
        $storyQuestion = (isset($externalMetadata['story_questions']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_questions'] : null;
        /** @var array Story countdown to use for the media. ONLY STORY MEDIA */
        $storyCountdown = (isset($externalMetadata['story_countdowns']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_countdowns'] : null;
        /** @var array Story quiz to use for the media. ONLY STORY MEDIA */
        $storyQuiz = (isset($externalMetadata['story_quizs']) && ($targetFeed == Constants::FEED_STORY || $targetFeed == Constants::FEED_REELS)) ? $externalMetadata['story_quizs'] : null;
        /** @var array Chat sticker to use for the media. ONLY STORY MEDIA */
        $chatSticker = (isset($externalMetadata['chat_sticker']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['chat_sticker'] : null;
        /** @var array Story fundraiser to use for the media. ONLY STORY MEDIA */
        $storyFundraisers = (isset($externalMetadata['story_fundraisers']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_fundraisers'] : null;
        /** @var array Story emoji reaction to use for the media. ONLY STORY MEDIA */
        $storyEmojiReaction = (isset($externalMetadata['story_emoji_reaction']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['story_emoji_reaction'] : null;
        /** @var array Attached media used to share media to story feed. ONLY STORY MEDIA! */
        $attachedMedia = (isset($externalMetadata['attached_media']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['attached_media'] : null;
        /** @var array Title of the media uploaded to your channel. ONLY TV MEDIA! */
        $title = (isset($externalMetadata['title']) && $targetFeed == Constants::FEED_TV) ? $externalMetadata['title'] : null;
        /** @var array In which series to add the video. ONLY TV MEDIA! */
        $seriesId = (isset($externalMetadata['series_id']) && $targetFeed == Constants::FEED_TV) ? $externalMetadata['series_id'] : null;
        /** @var array IGTV composer session ID. ONLY TV MEDIA! */
        $sessionId = (isset($externalMetadata['igtv_session_id']) && $targetFeed == Constants::FEED_TV) ? $externalMetadata['igtv_session_id'] : null;
        /** @var array IGTV Ads. ONLY TV MEDIA! */
        $igtvAds = (isset($externalMetadata['igtv_ads_toggled_on']) && $targetFeed == Constants::FEED_TV) ? $externalMetadata['igtv_ads_toggled_on'] : null;
        /** @var array IGTV share preview to feed. ONLY TV MEDIA! */
        $igtvShareToFeed = (isset($externalMetadata['igtv_share_preview_to_feed']) && $targetFeed == Constants::FEED_TV) ? $externalMetadata['igtv_share_preview_to_feed'] : null;
        /** @var array REELS (CLIPS) share preview to feed. ONLY REEL MEDIA! */
        $reelShareToFeed = (isset($externalMetadata['reel_share_preview_to_feed']) && $targetFeed == Constants::FEED_REELS) ? $externalMetadata['reel_share_preview_to_feed'] : null;
        /** @var array REELS (CLIPS) interest topics. ONLY REEL MEDIA! */
        $reelTopics = (isset($externalMetadata['interest_topics']) && $targetFeed == Constants::FEED_REELS) ? $externalMetadata['interest_topics'] : null;

        // Fix very bad external user-metadata values.
        if (!is_string($captionText)) {
            $captionText = '';
        }

        $uploadId = $internalMetadata->getUploadId();
        $videoDetails = $internalMetadata->getVideoDetails();

        // Build the request...
        $request = $this->ig->request($endpoint)
            ->addHeader('retry_context', json_encode($this->_getRetryContext()))
            ->addParam('video', 1)
            ->addPost('video_result', $internalMetadata->getVideoUploadResponse() !== null ? (string) $internalMetadata->getVideoUploadResponse()->getResult() : '')
            ->addPost('upload_id', $uploadId)
            ->addPost('poster_frame_index', 0)
            ->addPost('length', $videoDetails->getDuration())
            ->addPost('audio_muted', false) // $videoDetails->getAudioCodec() === null
            ->addPost('filter_type', 0)
            ->addPost('source_type', 4)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('camera_position', 'back')
            ->addPost('device',
                [
                    'manufacturer'      => $this->ig->device->getManufacturer(),
                    'model'             => $this->ig->device->getModel(),
                    'android_version'   => intval($this->ig->device->getAndroidVersion()),
                    'android_release'   => $this->ig->device->getAndroidRelease(),
                ])
            ->addPost('extra',
                [
                    'source_width'  => $videoDetails->getWidth(),
                    'source_height' => $videoDetails->getHeight(),
                ])
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('nav_chain', $this->ig->getNavChain());

        if ($this->ig->isExperimentEnabled('26156', 0, false) || !$this->ig->getIsEUUser()) {
            $request->addHeader('X-IG-EU-CONFIGURE-DISABLED', 'true');
        }

        $stickerIds = [];
        $tapModels = [];
        $staticModels = [];

        switch ($targetFeed) {
            case Constants::FEED_TIMELINE:
                if ($internalMetadata->isBestieMedia()) {
                    $request->addPost('audience', 'besties');
                } else {
                    $request->addPost('audience', 'default');
                }
                $request->addPost('caption', $captionText)
                    ->addPost('include_e2ee_mentioned_user_list', '1');

                if ($usertags !== null) {
                    Utils::throwIfInvalidUsertags($usertags);
                    $request->addPost('usertags', json_encode($usertags));
                    //->addPost('configure_mode', Constants::SHARE_TYPE['FOLLOWERS_SHARE']); // 0 - FOLLOWERS_SHARE
                    if ($coauthor !== null) {
                        if (is_array($coauthor) && count($coauthor) > 1) {
                            if (count($coauthor) > 5) {
                                throw new \InvalidArgumentException('Maximum coauthors allowed is 5.');
                            }
                            $request->addPost('invite_coauthor_user_ids', $coauthor);
                        } else {
                            if (is_array($coauthor)) {
                                $coauthor = $coauthor[0];
                            }
                            $request->addPost('invite_coauthor_user_id', $coauthor);
                        }
                        $request->addPost('internal_features', 'coauthor_post');
                    }
                }
                break;
            case Constants::FEED_STORY:
                if ($internalMetadata->isBestieMedia()) {
                    $request->addPost('audience', 'besties');
                } else {
                    $request->addPost('audience', 'default');
                }

                if ($captionText !== '' && $storyTextMetadata !== null) {
                    $request->addPost('caption', $captionText)
                            ->addPost('rich_text_format_types', json_encode(['modern_refreshed_v2']))
                            ->addPost('text_metadata', $storyTextMetadata);

                    if ($storyCaptions !== null) {
                        $request->addPost('story_captions', $storyCaptions);
                    }
                }

                $request
                    ->addPost('include_e2ee_mentioned_user_list', '1')
                    ->addPost('timezone_offset', ($this->ig->getTimezoneOffset() !== null) ? $this->ig->getTimezoneOffset() : date('Z'))
                    ->addPost('supported_capabilities_new', $this->getSupportedCapabilities())
                    ->addPost('configure_mode', '1')
                    //->addPost('configure_mode', Constants::SHARE_TYPE['REEL_SHARE']) // 2 - REEL_SHARE
                    ->addPost('allow_multi_configures', '1')
                    //->addPost('story_media_creation_date', time() - mt_rand(10, 20))
                    ->addPost('client_shared_at', time() - mt_rand(3, 10))
                    ->addPost('client_timestamp', time())
                    ->addPost('publish_id', 1)
                    ->addPost('camera_session_id', Signatures::generateUUID())
                    ->addPost('date_time_original', sprintf('%sT%s.000Z', date('Ymd'), date('His')))
                    ->addPost('composition_id', Signatures::generateUUID())
                    //->addPost('attempt_id', Signatures::generateUUID())
                    ->addPost('camera_entry_point', '360')
                    ->addPost('clips', [
                        [
                            'length'          => $videoDetails->getDuration(),
                            'source_type'     => '3',
                            'camera_position' => 'back',
                        ],
                    ])
                    ->addPost('original_media_type', '2'); // video

                $request->addPost('media_transformation_info', json_encode([
                    'width'                 => $videoDetails->getWidth(),
                    'height'                => $videoDetails->getHeight(),
                    'x_transform'           => 0,
                    'y_transform'           => 0,
                    'zoom'                  => number_format(1, 1),
                    'rotation'              => number_format(0, 1),
                    'background_coverage'   => number_format(0, 1),
                ]));

                /*
                if (is_string($link) && Utils::hasValidWebURLSyntax($link)) {
                    $story_cta = '[{"links":[{"webUri":'.json_encode($link).'}]}]';
                    $request->addPost('story_cta', $story_cta);
                }
                */
                if ($linkSticker !== null) {
                    Utils::throwIfInvalidStoryLinkSticker($linkSticker);

                    $tapModels[] = $linkSticker;
                    $stickerIds[] = 'link_sticker_default';
                }
                if ($hashtags !== null) {
                    Utils::throwIfInvalidStoryHashtagSticker($hashtags);

                    foreach ($hashtags as $hashtag) {
                        $tapModels[] = $hashtag;
                        $stickerIds[] = 'hashtag_sticker';
                    }
                }
                if ($locationSticker !== null && $location !== null) {
                    Utils::throwIfInvalidStoryLocationSticker($locationSticker);

                    $tapModels[] = $locationSticker;
                    $stickerIds[] = 'location_sticker';
                }
                if ($storyMentions !== null) {
                    Utils::throwIfInvalidStoryMentionSticker($storyMentions);

                    foreach ($storyMentions as $storyMention) {
                        $tapModels[] = $storyMention;
                        if ($storyMention['is_sticker']) {
                            $stickerIds[] = 'mention_sticker';
                        } else {
                            $static = $storyMention;
                            $static['str_id'] = 'text_sticker_'.Signatures::generateUUID();
                            $static['sticker_type'] = 'text_sticker';
                            $staticModels[] = $static;
                        }
                    }
                }
                if ($storyMusic !== null) {
                    //Utils::throwIfInvalidStoryCountdown($storyCountdown);
                    $request
                        ->addPost('story_music_stickers', json_encode($storyMusic[0]['story_music_stickers']))
                        ->addPost('story_music_lyric_stickers', json_encode($storyMusic[0]['story_music_lyric_stickers']))
                        ->addPost('story_music_metadata', json_encode($storyMusic[0]['story_music_metadata']))
                        ->addPost('internal_features', 'music_lyrics_sticker');

                    $tapModels[] = $storyMusic[0]['story_music_stickers'];
                    $stickerIds[] = 'music_overlay_sticker_lyrics_dynamic_reveal';
                }
                if ($storyPoll !== null) {
                    Utils::throwIfInvalidStoryPoll($storyPoll);
                    $request
                        ->addPost('internal_features', 'poll_sticker_v2');

                    $tapModels[] = $storyPoll[0];
                    $stickerIds[] = 'polling_sticker_v2';
                }
                if ($storySlider !== null) {
                    Utils::throwIfInvalidStorySlider($storySlider);
                    $request
                        ->addPost('story_sliders', json_encode($storySlider));

                    $stickerIds[] = 'emoji_slider_'.$storySlider[0]['emoji'];
                }
                if ($storyEmojiReaction !== null) {
                    //Utils::throwIfInvalidStorySlider($storyEmojiReaction);
                    $request
                        ->addPost('story_reaction_stickers', json_encode($storyEmojiReaction));

                    $tapModels[] = $storyEmojiReaction;
                    $stickerIds[] = 'emoji_reaction_'.$storyEmojiReaction[0]['emoji'];
                }
                if ($storyQuestion !== null) {
                    Utils::throwIfInvalidStoryQuestion($storyQuestion);
                    $request
                        ->addPost('story_questions', json_encode($storyQuestion));

                    $stickerIds[] = 'question_sticker_ama';
                }
                if ($storyCountdown !== null) {
                    Utils::throwIfInvalidStoryCountdown($storyCountdown);
                    $request
                        ->addPost('story_countdowns', json_encode($storyCountdown));

                    $stickerIds[] = 'countdown_sticker_time';
                }
                if ($storyQuiz !== null) {
                    Utils::throwIfInvalidStoryQuiz($storyQuiz);
                    $request
                        ->addPost('story_quizs', json_encode($storyQuiz));

                    $stickerIds[] = 'quiz_story_sticker_default';
                }
                if ($chatSticker !== null) {
                    Utils::throwIfInvalidChatSticker($chatSticker);
                    $request
                        ->addPost('story_chats', json_encode($chatSticker));

                    $stickerIds[] = 'chat_sticker_bundle_id';
                }
                if ($storyFundraisers !== null) {
                    $request
                        ->addPost('story_fundraisers', json_encode($storyFundraisers));

                    $stickerIds[] = 'fundraiser_sticker_id';
                }
                if ($attachedMedia !== null) {
                    Utils::throwIfInvalidAttachedMedia($attachedMedia);
                    $request
                        ->addPost('attached_media', json_encode($attachedMedia));

                    $stickerIds[] = 'media_simple_'.reset($attachedMedia)['media_id'];
                }
                if (isset($externalMetadata['share_to_fb_destination_id'])) {
                    $request->addPost('allow_multi_configures', '1')
                            ->addPost('xpost_surface', 'ig_story_composer');
                }
                if (isset($externalMetadata['share_to_fb_destination_id']) && isset($externalMetadata['crosspost'])) {
                    $request->addPost('share_to_fb_destination_id', $externalMetadata['share_to_fb_destination_id'])
                            ->addPost('share_to_facebook', '1')
                            ->addPost('share_to_fb_destination_type', isset($externalMetadata['share_to_fb_destination_type']) ? $externalMetadata['share_to_fb_destination_type'] : 'USER');

                    if (isset($externalMetadata['fb_access_token'])) {
                        $request->addPost('fb_access_token', $externalMetadata['fb_access_token']);
                    } else {
                        $request->addPost('no_token_crosspost', '1');
                    }
                }
                if (isset($externalMetadata['xpost'])) {
                    $request->addPost('xpost_surface', 'ig_story_composer');
                }
                break;
            case Constants::FEED_DIRECT_STORY:
                $request
                    ->addPost('configure_mode', Constants::SHARE_TYPE['REEL_SHARE']) // 2 - REEL_SHARE (STORIES)
                    ->addPost('recipient_users', $internalMetadata->getDirectUsers())
                    ->addPost('thread_ids', $internalMetadata->getDirectThreads())
                    //->addPost('story_media_creation_date', time() - mt_rand(10, 20))
                    ->addPost('client_shared_at', time() - mt_rand(3, 10))
                    ->addPost('client_timestamp', time());

                if ($internalMetadata->getStoryViewMode() !== null) {
                    $request->addPost('view_mode', $internalMetadata->getStoryViewMode());
                }
                break;
            case Constants::FEED_REELS:
                if ($internalMetadata->isBestieMedia()) {
                    $request->addPost('audience', 'besties');
                } else {
                    $request->addPost('audience', 'default');
                }
                $request
                    ->addHeader('Is_clips_video', '1')
                    ->addPost('supported_capabilities_new', $this->getSupportedCapabilities())
                    ->addPost('is_shared_to_fb', isset($externalMetadata['share_to_fb']) ? strval(intval($externalMetadata['share_to_fb'])) : '0')
                    ->addPost('caption', $captionText)
                    ->addPost('timezone_offset', ($this->ig->getTimezoneOffset() !== null) ? $this->ig->getTimezoneOffset() : date('Z'))
                    ->addPost('camera_session_id', Signatures::generateUUID())
                    ->addPost('is_clips_edited', '0')
                    ->addPost('like_and_view_counts_disabled', '0')
                    ->addPost('is_gifting_enabled', '1')
                    ->addPost('camera_entry_point', '360')
                    ->addPost('disable_comments', '0')
                    ->addPost('include_e2ee_mentioned_user_list', '1')
                    ->addPost('third_party_downloads_enabled', '1')
                    ->addPost('is_created_with_sound_sync', '0')
                    ->addPost('clips_creation_entry_point', 'clips')
                    ->addPost('clips', [
                        [
                            'length'          => number_format($videoDetails->getDuration(), 3),
                            'source_type'     => '3',
                            'camera_position' => 'back',
                        ],
                    ])
                    ->addPost('clips_segments_metadata', [
                        'num_segments'      => 1,
                        'clips_segments'    => [
                            [
                                'index'                 => 0,
                                'face_effect_id'        => null,
                                'speed'                 => 100,
                                'source_type'           => '1',
                                'duration_ms'           => round($videoDetails->getDuration(), 3) * 1000,
                                'audio_type'            => 'original',
                                'from_draft'            => '0',
                                'camera_position'       => '2',
                                'media_folder'          => null,
                                'media_type'            => 'video',
                                'original_media_type'   => '2', // video
                            ],
                        ],
                    ])
                    ->addPost('clips_audio_metadata', [
                        'original'  => [
                            'volume_level'  => 1,
                        ],
                    ])
                    ->addPost('additional_audio_info', [
                        'has_voiceover_attribution' => 0,
                    ])
                    ->addPost('is_created_with_contextual_music_recs', '0')
                    ->addPost('video_subtitles_enabled', '1')
                    ->addPost('enable_smart_thumbnail', '0')
                    ->addPost('is_template_disabled', '0')
                    ->addPost('is_creator_requesting_mashup', '0')
                    ->addPost('capture_type', 'clips_v2');

                if (isset($externalMetadata['share_to_fb'])) {
                    $request->addPost('cross_app_share_type', $externalMetadata['share_to_fb']); // 1- Shared on fb recommended 2- shared as fb profile
                }

                if (isset($externalMetadata['share_to_fb_destination_id'])) {
                    $request->addPost('is_shared_to_fb', '0')
                            ->addPost('share_to_fb_destination_audience_type', 'PUBLIC');
                }

                if (isset($externalMetadata['share_to_fb_destination_id'])) {
                    $request->addPost('share_to_fb_destination_id', $externalMetadata['share_to_fb_destination_id'])
                            ->addPost('share_to_facebook', '1')
                            ->addPost('share_to_fb_destination_type', isset($externalMetadata['share_to_fb_destination_type']) ? $externalMetadata['share_to_fb_destination_type'] : 'USER');

                    if (isset($externalMetadata['fb_access_token'])) {
                        $request->addPost('fb_access_token', $externalMetadata['fb_access_token']);
                    } else {
                        $request->addPost('no_token_crosspost', '1');
                    }
                }

                if ($reelShareToFeed !== null) {
                    $request->addPost('clips_share_preview_to_feed', $reelShareToFeed === true ? '1' : '0');
                } else {
                    $request->addPost('clips_share_preview_to_feed', '1');
                }
                if ($storyPoll !== null) {
                    Utils::throwIfInvalidStoryPoll($storyPoll);
                    $tapModels[] = $storyPoll[0];
                }
                if ($storySlider !== null) {
                    Utils::throwIfInvalidStorySlider($storySlider);
                    $tapModels[] = $storySlider[0];
                }
                if ($usertags !== null) {
                    Utils::throwIfInvalidUsertags($usertags);
                    $request->addPost('usertags', json_encode($usertags));
                    if ($coauthor !== null) {
                        if (is_array($coauthor) && count($coauthor) > 1) {
                            if (count($coauthor) > 5) {
                                throw new \InvalidArgumentException('Maximum coauthors allowed is 5.');
                            }
                            $request->addPost('invite_coauthor_user_ids', $coauthor);
                        } else {
                            if (is_array($coauthor)) {
                                $coauthor = $coauthor[0];
                            }
                            $request->addPost('invite_coauthor_user_id', $coauthor);
                        }
                        $request->addPost('internal_features', 'coauthor_post');
                    }
                }
                if ($storyQuiz !== null) {
                    Utils::throwIfInvalidStoryQuiz($storyQuiz);
                    $tapModels[] = $storyQuiz[0];
                }
                if ($storyQuestion !== null) {
                    Utils::throwIfInvalidStoryQuestion($storyQuestion);
                    $tapModels[] = $storyQuestion[0];
                }
                if ($reelTopics !== null) {
                    if (!is_array($reelTopics)) {
                        throw new \InvalidArgumentException('You must provide an array.');
                    }
                    $request->addPost('interest_topics', $reelTopics);
                }
                break;
            case Constants::FEED_TV:
                if ($title === null) {
                    throw new \InvalidArgumentException('You must provide a title for the media.');
                }
                if ($sessionId === null) {
                    throw new \InvalidArgumentException('You must provide a session ID for the media.');
                }

                if ($seriesId !== null) {
                    $request->addPost('igtv_series_id', $seriesId);
                }

                if ($igtvShareToFeed !== null) {
                    $request->addPost('igtv_share_preview_to_feed', '1');
                }

                $request
                    ->addPost('title', $title)
                    ->addPost('caption', $captionText)
                    ->addPost('igtv_composer_session_id', $sessionId)
                    ->addPost('igtv_ads_toggled_on', boolval($igtvAds));
                    //->addPost('configure_mode', Constants::SHARE_TYPE['IGTV']); // 8 - IGTV
                break;
        }

        if (!empty($tapModels)) {
            $request->addPost('tap_models', json_encode($tapModels, JSON_UNESCAPED_SLASHES));
        }
        if (!empty($staticModels)) {
            $request->addPost('static_models', json_encode($staticModels, JSON_UNESCAPED_SLASHES));
        }

        if (!empty($stickerIds)) {
            $storyStickerIds = null;
            if (count($stickerIds) === 1) {
                $storyStickerIds = $stickerIds[0];
            } else {
                $storyStickerIds = implode(',', $stickerIds);
            }
            $request->addPost('story_sticker_ids', $storyStickerIds);
        }

        if ($targetFeed == Constants::FEED_STORY) {
            //$request->addPost('story_media_creation_date', time());
            if ($usertags !== null) {
                // Reel Mention example:
                // [{\"y\":0.3407772676161919,\"rotation\":0,\"user_id\":\"USER_ID\",\"x\":0.39892578125,\"width\":0.5619921875,\"height\":0.06011525487256372}]
                // NOTE: The backslashes are just double JSON encoding, ignore
                // that and just give us an array with these clean values, don't
                // try to encode it in any way, we do all encoding to match the above.
                // This post field will get wrapped in another json_encode call during transfer.
                $request->addPost('reel_mentions', json_encode($usertags));
            }
        }

        if ($location instanceof Response\Model\Location) {
            if ($targetFeed === Constants::FEED_TIMELINE) {
                $request->addPost('location', Utils::buildMediaLocationJSON($location));
            }
        }

        $configure = $request->getResponse(new Response\ConfigureResponse());

        return $configure;
    }

    /**
     * Uploads using facebook uploader.
     *
     * @param int                   $targetFeed       One of the FEED_X constants.
     * @param string                $videoFilename    The video filename.
     * @param InternalMetadata|null $internalMetadata (optional) Internal library-generated metadata object.
     * @param mixed                 $filename
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video upload fails.
     *
     * @return InternalMetadata Updated internal metadata object.
     */
    public function facebookUpload(
        $targetFeed,
        $filename,
        InternalMetadata $internalMetadata = null)
    {
        if ($internalMetadata === null) {
            $internalMetadata = new InternalMetadata();
        }

        if ($targetFeed !== Constants::FEED_DIRECT_AUDIO) {
            throw new InstagramException('Only Direct audio is supported at the moment.');
        }

        try {
            switch ($targetFeed) {
                case Constants::FEED_DIRECT_AUDIO:
                    try {
                        if ($internalMetadata->getVideoDetails() === null) {
                            $internalMetadata->setVideoDetails($targetFeed, $filename);
                        }
                    } catch (\Exception $e) {
                        throw new \InvalidArgumentException(
                            sprintf('Failed to get photo details: %s', $e->getMessage()),
                            $e->getCode(),
                            $e
                        );
                    }

                    $videoDetails = $internalMetadata->getVideoDetails();

                    $uploadParams = $this->_getVideoUploadParams($targetFeed, $internalMetadata);
                    $uploadParams = Utils::reorderByHashCode($uploadParams);
                    $response = $this->_uploadSegmentedVideoFacebook($targetFeed, $internalMetadata);
                    $internalMetadata->setFbAttachmentId($response->getMediaId());
                    break;
                default:
                    break;
            }
        } catch (InstagramException $e) {
            // Pass Instagram's error as is.
            throw $e;
        } catch (\Exception $e) {
            // Wrap runtime errors.
            throw new UploadFailedException(
                sprintf('Upload of "%s" failed: %s', basename($filename), $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        return $internalMetadata;
    }

    /**
     * Configures parameters for a whole album of uploaded media files.
     *
     * WARNING TO CONTRIBUTORS: THIS IS ONLY FOR *TIMELINE ALBUMS*. DO NOT MAKE
     * IT DO ANYTHING ELSE, TO AVOID ADDING BUGGY AND UNMAINTAINABLE SPIDERWEB
     * CODE!
     *
     * @param array            $media            Extended media array coming from Timeline::uploadAlbum(),
     *                                           containing the user's per-file metadata,
     *                                           and internally generated per-file metadata.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object for the album itself.
     * @param array            $externalMetadata (optional) User-provided metadata key-value pairs
     *                                           for the album itself (its caption, location, etc).
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     */
    public function configureTimelineAlbum(
        array $media,
        InternalMetadata $internalMetadata,
        array $externalMetadata = [])
    {
        $endpoint = 'media/configure_sidecar/';

        $albumUploadId = $internalMetadata->getUploadId();

        // Available external metadata parameters:
        /** @var string Caption to use for the album. */
        $captionText = isset($externalMetadata['caption']) ? $externalMetadata['caption'] : '';
        /** @var Response\Model\Location|null A Location object describing where
         * the album was taken. */
        $location = isset($externalMetadata['location']) ? $externalMetadata['location'] : null;

        // Fix very bad external user-metadata values.
        if (!is_string($captionText)) {
            $captionText = '';
        }

        // Build the album's per-children metadata.
        $date = sprintf('%sT%s.000Z', date('Ymd'), date('His'));
        $childrenMetadata = [];
        foreach ($media as $item) {
            /** @var InternalMetadata $itemInternalMetadata */
            $itemInternalMetadata = $item['internalMetadata'];
            // Get all of the common, INTERNAL per-file metadata.
            $uploadId = $itemInternalMetadata->getUploadId();
            /** @var int Width of the photo. */
            $photoWidth = $itemInternalMetadata->getPhotoDetails()->getWidth();
            /** @var int Height of the photo. */
            $photoHeight = $itemInternalMetadata->getPhotoDetails()->getHeight();

            switch ($item['type']) {
                case 'photo':
                    // Build this item's configuration.
                    $photoConfig = [
                        'upload_id'           => $uploadId,
                        'source_type'         => 4,
                        'edits'               => [
                                'crop_original_size'    => [(float) $photoWidth, (float) $photoHeight],
                                'crop_zoom'             => 1.0,
                                'crop_center'           => [0.0, -0.0],
                            ],
                        'device' => [
                                'manufacturer'      => $this->ig->device->getManufacturer(),
                                'model'             => $this->ig->device->getModel(),
                                'android_version'   => intval($this->ig->device->getAndroidVersion()),
                                'android_release'   => $this->ig->device->getAndroidRelease(),
                            ],
                        'extra' => [
                                'source_width'  => $photoWidth,
                                'source_height' => $photoHeight,
                            ],
                    ];

                    if (isset($item['usertags'])) {
                        // NOTE: These usertags were validated in Timeline::uploadAlbum.
                        $photoConfig['usertags'] = json_encode(['in' => $item['usertags']]);

                        if (isset($item['invite_coauthor_user_ids'])) {
                            if (is_array($item['invite_coauthor_user_ids']) && count($item['invite_coauthor_user_ids']) > 1) {
                                $photoConfig['invite_coauthor_user_ids'] = $item['invite_coauthor_user_ids'];
                            } else {
                                $coauthor = null;
                                if (is_array($item['invite_coauthor_user_ids'])) {
                                    $photoConfig['invite_coauthor_user_id'] = $item['invite_coauthor_user_ids'][0];
                                }
                                $photoConfig['invite_coauthor_user_id'] = $item['invite_coauthor_user_ids'];
                            }
                            $photoConfig['internal_features'] = 'coauthor_post';
                        }
                    }

                    $childrenMetadata[] = $photoConfig;
                    break;
                case 'video':
                    // Get all of the INTERNAL per-VIDEO metadata.
                    $videoDetails = $itemInternalMetadata->getVideoDetails();

                    // Build this item's configuration.
                    $videoConfig = [
                        'date_time_original'  => $date,
                        'poster_frame_index'  => 0,
                        'upload_id'           => $uploadId,
                        'source_type'         => '4',
                        'device'              => [
                                'manufacturer'      => $this->ig->device->getManufacturer(),
                                'model'             => $this->ig->device->getModel(),
                                'android_version'   => intval($this->ig->device->getAndroidVersion()),
                                'android_release'   => $this->ig->device->getAndroidRelease(),
                            ],
                        'clips' => [
                                'length'          => round($videoDetails->getDuration(), 1),
                                'source_type'     => '4',
                            ],
                    ];

                    if (isset($item['usertags'])) {
                        // NOTE: These usertags were validated in Timeline::uploadAlbum.
                        $videoConfig['usertags'] = json_encode(['in' => $item['usertags']]);

                        if (isset($item['invite_coauthor_user_ids'])) {
                            if (is_array($item['invite_coauthor_user_ids']) && count($item['invite_coauthor_user_ids']) > 1) {
                                $videoConfig['invite_coauthor_user_ids'] = $item['invite_coauthor_user_ids'];
                            } else {
                                $coauthor = null;
                                if (is_array($item['invite_coauthor_user_ids'])) {
                                    $videoConfig['invite_coauthor_user_id'] = $item['invite_coauthor_user_ids'][0];
                                }
                                $videoConfig['invite_coauthor_user_id'] = $item['invite_coauthor_user_ids'];
                            }
                            $videoConfig['internal_features'] = 'coauthor_post';
                        }
                    }

                    $childrenMetadata[] = $videoConfig;
                    break;
            }
        }

        // Build the request...
        $request = $this->ig->request($endpoint)
            ->addPost('timezone_offset', ($this->ig->getTimezoneOffset() !== null) ? $this->ig->getTimezoneOffset() : date('Z'))
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('client_sidecar_id', $albumUploadId)
            ->addPost('caption', $captionText)
            ->addPost('device',
                [
                    'manufacturer'      => $this->ig->device->getManufacturer(),
                    'model'             => $this->ig->device->getModel(),
                    'android_version'   => intval($this->ig->device->getAndroidVersion()),
                    'android_release'   => $this->ig->device->getAndroidRelease(),
                ])
            ->addPost('children_metadata', $childrenMetadata);

        if ($location instanceof Response\Model\Location) {
            $request
                ->addPost('location', Utils::buildMediaLocationJSON($location))
                ->addPost('geotag_enabled', '1')
                ->addPost('posting_latitude', $location->getLat())
                ->addPost('posting_longitude', $location->getLng())
                ->addPost('media_latitude', $location->getLat())
                ->addPost('media_longitude', $location->getLng())
                ->addPost('exif_latitude', 0.0)
                ->addPost('exif_longitude', 0.0);
        }

        if (isset($item['invite_coauthor_user_ids'])) {
            if (is_array($item['invite_coauthor_user_ids'])) {
                $request->addPost('invite_coauthor_user_ids', $item['invite_coauthor_user_ids']);
            } else {
                $request->addPost('invite_coauthor_user_id', $item['invite_coauthor_user_ids']);
            }
            $request->addPost('internal_features', 'coauthor_post');
        }

        $configure = $request->getResponse(new Response\ConfigureResponse());

        return $configure;
    }

    /**
     * Saves active experiments.
     *
     * @param array $mobileConfigResponse
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    protected function _saveExperimentsMobileConfig(
        $mobileConfigResponse)
    {
        //$paramsMap = fopen(__DIR__.'/../data/params_map.txt', 'r');
        //$mappedExperiments = [];
        //$found = false;

        /*
        if ($paramsMap) {
            while (($line = fgets($paramsMap)) !== false) {
                if ($found === true && $line[0] !== '*') {
                    $exps = explode(',', $line);
                    $mappedExperiments[hexdec(end($paramData))]['exps'][] = $exps[0];
                } else {
                    $found = false;
                    $line = str_replace('*', '', $line);
                    $paramData = explode(',', $line);
                    if (in_array($paramData[0], Constants::MOBILE_CONFIG_EXPERIMTENTS)) {
                        $mappedExperiments[strval(hexdec(end($paramData)))] = [
                            'name'  => $paramData[0],
                            'exps'  => [],
                        ];
                        $found = true;
                    }
                }
            }

            fclose($paramsMap);
        }
        */

        /*
        $paramsMap = json_decode(file_get_contents(__DIR__.'/../data/mobileconfig.json'));

        foreach ($paramsMap as $exp) {
            $values = explode(':', $exp);
            $mappedExperiments[$values[0]] = [
                'name'  => $values[1],
                'exps'  => [],
            ];
            foreach (array_slice($values, 2) as $k => $v) {
                if ($k % 2 === 1) {
                    $mappedExperiments[$values[0]]['exps'][] = $v;
                }
            }
        }
        */

        $experiments = [];

        foreach ($mobileConfigResponse['configs'] as $k => $v) {
            $exps = [];
            $fields = $v['fields'];
            asort($fields);
            $c = 0;
            foreach ($fields as $k2 => $v2) {
                if (isset($v2['bln'])) {
                    $val = boolval($v2['bln']);
                } elseif (isset($v2['str'])) {
                    $val = $v2['str'];
                } elseif (isset($v2['i64'])) {
                    $val = intval($v2['i64']);
                } else {
                    $val = null;
                }
                $exps[$v2['k']] = $val;
            }
            $experiments[$k] = $exps;
        }

        // Save the experiments and the last time we refreshed them.
        $this->ig->experiments = $this->ig->settings->setExperiments($experiments);
        $this->ig->settings->set('last_experiments', time());
    }

    /**
     * Get MobileConfig.
     *
     * @param bool $prelogin Indicates if the request is done before login request.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MobileConfigResponse
     */
    public function getMobileConfig(
        $prelogin)
    {
        $request = $this->ig->request('launcher/mobileconfig/')
            ->addPost('bool_opt_policy', 0)
            ->addPost('api_version', 3)
            ->addPost('device_id', $this->ig->uuid)
            ->addPost('use_case', 'STANDARD')
            ->addPost('fetch_mode', 'CONFIG_SYNC_ONLY')
            ->addPost('fetch_type', 'ASYNC_FULL');

        if ($prelogin) {
            $this->ig->isSessionless = true;
            $request
                ->setNeedsAuth(false)
                ->addPost('mobileconfigsessionless', '')
                ->addPost('unit_type', 1)
                ->addPost('query_hash', 'eba6ae08baa2eba74e1b08b8907e5e84fa80a938eff710abcd14effb270891fd')
                ->addPost('family_device_id', $this->ig->phone_id === null ? 'EMPTY_FAMILY_DEVICE_ID' : strtoupper($this->ig->phone_id));
        } else {
            $request
                ->addPost('mobileconfig', '')
                ->addPost('unit_type', 2)
                ->addPost('query_hash', '733bc1d3281f33ebabab08ee18d2d261414b78f20e17131c7754286d12cb6b3d')
                ->addPost('_uid', $this->ig->account_id)
                ->addPost('_uuid', $this->ig->uuid);
        }

        $result = $request->getResponse(new Response\MobileConfigResponse());
        $this->ig->isSessionless = false;
        if ($prelogin !== true) {
            $this->_saveExperimentsMobileConfig($result->asArray());
        }

        return $result;
    }

    /**
     * Fetch headers.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function fetchHeaders()
    {
        return $this->ig->request('si/fetch_headers')
            ->setNeedsAuth(false)
            ->addParam('guid', str_replace('-', '', $this->ig->uuid))
            ->addParam('challenge_type', 'signup')
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get decisions about device capabilities.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CapabilitiesDecisionsResponse
     */
    public function getDeviceCapabilitiesDecisions()
    {
        return $this->ig->request('device_capabilities/decisions/')
            ->addParam('signed_body', Signatures::generateSignature(json_encode((object) []).'.{}'))
            ->addParam('ig_sig_key_version', Constants::SIG_KEY_VERSION)
            ->getResponse(new Response\CapabilitiesDecisionsResponse());
    }

    /**
     * Registers advertising identifier.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function logAttribution()
    {
        return $this->ig->request('attribution/log_attribution/')
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->addPost('signed_body', Signatures::generateSignature(json_encode((object) [])).'.{}')
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * DEPRECATION CHECK.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function logResurrectAttribution()
    {
        return $this->ig->request('attribution/log_resurrect_attribution/')
            ->setIsSilentFail(true)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('adid', $this->ig->advertising_id)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Reads MSISDN header.
     *
     * @param string $usage        Desired usage, either "ig_select_app" or "default".
     * @param bool   $useCsrfToken (Optional) Decides to include a csrf token in this request.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MsisdnHeaderResponse
     */
    public function readMsisdnHeader(
        $usage,
        $useCsrfToken = false)
    {
        $request = $this->ig->request('accounts/read_msisdn_header/')
            ->setNeedsAuth(false)
            ->addHeader('X-DEVICE-ID', $this->ig->uuid)
            // UUID is used as device_id intentionally.
            ->addPost('device_id', $this->ig->uuid)
            ->addPost('mobile_subno_usage', $usage);

        /*
        if ($useCsrfToken) {
            $request->addPost('_csrftoken', $this->ig->client->getToken());
        }
        */

        return $request->getResponse(new Response\MsisdnHeaderResponse());
    }

    /**
     * Bootstraps MSISDN header.
     *
     * @param string $usage Mobile subno usage.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MsisdnHeaderResponse
     *
     * @since 10.24.0 app version.
     */
    public function bootstrapMsisdnHeader(
        $usage = 'ig_select_app')
    {
        $request = $this->ig->request('accounts/msisdn_header_bootstrap/')
            ->setNeedsAuth(false)
            ->addPost('mobile_subno_usage', $usage)
            // UUID is used as device_id intentionally.
            ->addPost('device_id', $this->ig->uuid);

        return $request->getResponse(new Response\MsisdnHeaderResponse());
    }

    /**
     * @param Response\Model\Token|null $token
     */
    protected function _saveZeroRatingToken(
        Response\Model\Token $token = null)
    {
        if ($token === null) {
            return;
        }

        $rules = [];
        foreach ($token->getRewriteRules() as $rule) {
            $rules[$rule->getMatcher()] = $rule->getReplacer();
        }
        $this->ig->client->zeroRating()->update($rules);

        try {
            $this->ig->settings->setRewriteRules($rules);
            $this->ig->settings->set('zr_token', $token->getTokenHash());
            $this->ig->settings->set('zr_expires', $token->expiresAt());
        } catch (SettingsException $e) {
            // Ignore storage errors.
        }
    }

    /**
     * Get Facebook dod resources.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FacebookDodRequestResourcesResponse
     */
    public function getFacebookDodResources()
    {
        return $this->ig->request('facebook_dod/request_dod_resources/')
            ->setNeedsAuth(false)
            ->addParam('native_build', $this->ig->getVersionCode())
            ->addParam('prefer_compressed', 'true')
            ->addParam('signed_body', 'SIGNATURE.')
            ->addParam('ota_build', $this->ig->getVersionCode())
            ->addParam('resource_flavor', $this->ig->getLocale())
            ->addParam('custom_app_id', Constants::FACEBOOK_ORCA_APPLICATION_ID)
            ->addParam('resource_name', 'fbt_language_pack.bin')
            ->getResponse(new Response\FacebookDodRequestResourcesResponse());
    }

    /**
     * Get zero rating token hash result.
     *
     * @param string $reason   One of: "token_expired", "mqtt_token_push", "token_stale", "provisioning_time_mismatch".
     * @param bool   $result   result
     * @param bool   $prelogin Prelogin
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\TokenResultResponse
     */
    public function fetchZeroRatingToken(
        $reason = 'token_expired',
        $result = true,
        $prelogin = true)
    {
        if ($result === true) {
            $endpoint = 'zr/token/result/';
        } else {
            $endpoint = 'zr/dual_tokens/';
        }
        $request = $this->ig->request($endpoint)
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->addPost('custom_device_id', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('fetch_reason', $reason)
            ->addPost('normal_token_hash', (string) $this->ig->settings->get('zr_token'));

        if ($prelogin === false) {
            $request->addPost('_uuid', $this->ig->uuid);
        }

        /** @var Response\TokenResultResponse $result */
        $result = $request->getResponse(new Response\TokenResultResponse());
        $this->_saveZeroRatingToken($result->getNormalToken());

        return $result;
    }

    /**
     * Create android Keystore.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function createAndroidKeystore()
    {
        return $this->ig->request('attestation/create_android_keystore/')
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->addPost('app_scoped_device_id', $this->ig->uuid)
            ->addPost('key_hash', '')
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get megaphone log.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MegaphoneLogResponse
     */
    public function getMegaphoneLog()
    {
        return $this->ig->request('megaphone/log/')
            ->setSignedPost(false)
            ->addPost('type', 'feed_aysf')
            ->addPost('action', 'seen')
            ->addPost('reason', '')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('uuid', md5(time()))
            ->getResponse(new Response\MegaphoneLogResponse());
    }

    /**
     * Get hidden entities for users, places and hashtags via Facebook's algorithm.
     *
     * TODO: We don't know what this function does. If we ever discover that it
     * has a useful purpose, then we should move it somewhere else.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FacebookHiddenEntitiesResponse
     */
    public function getFacebookHiddenSearchEntities()
    {
        return $this->ig->request('fbsearch/get_hidden_search_entities/')
            ->getResponse(new Response\FacebookHiddenEntitiesResponse());
    }

    /**
     * Get Facebook OTA (Over-The-Air) update information.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FacebookOTAResponse
     */
    public function getFacebookOTA()
    {
        return $this->ig->request('facebook_ota/')
            ->addParam('fields', Constants::FACEBOOK_OTA_FIELDS)
            ->addParam('custom_user_id', $this->ig->account_id)
            ->addParam('signed_body', Signatures::generateSignature('').'.')
            ->addParam('version_code', $this->ig->getVersionCode())
            ->addParam('version_name', Constants::IG_VERSION)
            ->addParam('custom_app_id', Constants::FACEBOOK_ORCA_APPLICATION_ID)
            ->addParam('custom_device_id', $this->ig->uuid)
            ->getResponse(new Response\FacebookOTAResponse());
    }

    /**
     * Fetch profiler traces config.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoomFetchConfigResponse
     *
     * @see https://github.com/facebookincubator/profilo
     */
    public function getLoomFetchConfig()
    {
        return $this->ig->request('loom/fetch_config/')
            ->getResponse(new Response\LoomFetchConfigResponse());
    }

    /**
     * Get profile "notices".
     *
     * This is just for some internal state information, such as
     * "has_change_password_megaphone". It's not for public use.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ProfileNoticeResponse
     */
    public function getProfileNotice()
    {
        return $this->ig->request('users/profile_notice/')
            ->getResponse(new Response\ProfileNoticeResponse());
    }

    /**
     * Fetch quick promotions data.
     *
     * This is used by Instagram to fetch internal promotions or changes
     * about the platform. Latest quick promotion known was the new GDPR
     * policy where Instagram asks you to accept new policy and accept that
     * you have 18 years old or more.
     *
     * @param string[] $surfaces Quick Promotion Surface.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FetchQPDataResponse
     */
    public function getQPFetch(
        $surfaces = null)
    {
        if ($surfaces !== null) {
            $qps = $this->_getQuickPromotionSurface($surfaces);
        }

        $request = $this->ig->request('qp/batch_fetch/')
            ->addPost('is_sdk', 'true')
            ->addPost('vc_policy', 'default')
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('surfaces_to_triggers', ($surfaces === null) ? Constants::BATCH_SURFACES : json_encode($qps['triggers']))
            ->addPost('surfaces_to_queries', ($surfaces === null) ? Constants::BATCH_QUERY : json_encode($qps['queries']))
            ->addPost('version', Constants::BATCH_VERSION)
            ->addPost('scale', ceil(intval(substr($this->ig->device->getDPI(), 0, -3)) / 160));

        // fdid_in_qp_context
        if ($this->ig->isExperimentEnabled('54557', 1, false)) {
            $request->addPost('trigger_context', json_encode(['family_device_id' => $this->ig->phone_id, 'app_scoped_device_id' => $this->ig->uuid]));
        }

        return $request->getResponse(new Response\FetchQPDataResponse());
    }

    /**
     * Get Arlink download info.
     *
     * DEPRECATION CHECK.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ArlinkDownloadInfoResponse
     */
    public function getArlinkDownloadInfo()
    {
        return $this->ig->request('users/arlink_download_info/')
            ->addParam('version_override', '2.2.1')
            ->getResponse(new Response\ArlinkDownloadInfoResponse());
    }

    /**
     * Get quick promotions cooldowns.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\QPCooldownsResponse
     */
    public function getQPCooldowns()
    {
        return $this->ig->request('qp/get_cooldowns/')
            ->addParam('signed_body', Signatures::generateSignature(json_encode((object) [])).'.{}')
            ->getResponse(new Response\QPCooldownsResponse());
    }

    /**
     * Report a problem.
     *
     * Tells Instagram if you think they made a mistake.
     *
     * @param string $feedbackUrl Feedback URL.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function reportProblem(
        $feedbackUrl)
    {
        return $this->ig->request($feedbackUrl)
             //->addPost('_csrftoken', $this->ig->client->getToken())
             ->addPost('_uid', $this->ig->account_id)
             ->addPost('_uuid', $this->ig->uuid)
             ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get viewable statuses.
     *
     * @param bool $includeAuthors Include authors.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GetViewableStatusesResponse
     */
    public function getViewableStatuses(
        $includeAuthors = false)
    {
        $request = $this->ig->request('status/get_viewable_statuses/');

        if ($includeAuthors === true) {
            $request->addParam('include_authors', 'true');
        }

        return $request->getResponse(new Response\GetViewableStatusesResponse());
    }

    /**
     * Store client push permissions.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function storeClientPushPermissions()
    {
        return $this->ig->request('notifications/store_client_push_permissions/')
            ->setSignedPost(false)
            ->addPost('enabled', 'true')
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('device_id', $this->ig->uuid)
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get notification settings.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getNotificationsSettings()
    {
        return $this->ig->request('notifications/get_notification_settings/')
            ->addParam('content_type', 'instagram_direct')
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * CDN RMD.
     *
     * @param mixed $interface
     * @param mixed $reason
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function cdnRmd(
        $interface = 'Unknown',
        $reason = 'SESSION_CHANGE')
    {
        $response = $this->ig->request('ti/cdn_rmd/')
            ->setAddDefaultHeaders(false)
            ->addHeader('X-IG-App-ID', Constants::FACEBOOK_ANALYTICS_APPLICATION_ID)
            ->addHeader('X-IG-Capabilities', Constants::X_IG_Capabilities)
            ->addHeader('X-Fb-Privacy-Context', '4760009080727693')
            ->addHeader('X-Tigon-Is-Retry', 'false')
            ->addHeader('X-Fb-Rmd', 'fail=NoUrlMap;v=;ip=;tkn=;reqTime=-1090386208;recvTime=-1239979508')
            ->addParam('net_iface', $interface)
            ->addParam('reason', $reason)
            ->getResponse(new Response\GenericResponse());

        $this->ig->cdn_rmd = true;

        return $response;
    }

    /**
     * Send Graph query.
     *
     * @param string $clientDoc
     * @param array  $vars
     * @param string $friendlyName
     * @param string $rootName
     * @param bool   $pretty
     * @param string $clientLibrary
     * @param string $queryEndpoint
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function sendGraph(
        $clientDoc,
        $vars,
        $friendlyName,
        $rootName,
        $pretty,
        $clientLibrary = 'graphservice',
        $queryEndpoint = false)
    {
        $endpoint = $queryEndpoint ? 'graphql/query' : 'graphql_www';
        $request = $this->ig->request("https://i.instagram.com/{$endpoint}")
            ->setSignedPost(false)
            ->setNeedsAuth(false)
            ->setIsSilentFail(true)
            ->setAddDefaultHeaders(false)
            ->addHeader('X-IG-App-ID', Constants::FACEBOOK_ANALYTICS_APPLICATION_ID)
            ->addHeader('X-IG-Capabilities', Constants::X_IG_Capabilities)
            ->addHeader('X-Graphql-Client-Library', $clientLibrary)
            ->addHeader('X-Fb-Friendly-Name', $friendlyName)
            ->addHeader('X-Root-Field-Name', $rootName)
            ->addHeader('IG-INTENDED-USER-ID', empty($this->ig->settings->get('account_id')) ? 0 : $this->ig->settings->get('account_id'))
            ->addHeader('X-Tigon-Is-Retry', 'False')
            ->addPost('client_doc_id', $clientDoc)
            ->addPost('locale', $queryEndpoint ? 'user' : $this->ig->getLocale())
            ->addPost('variables', json_encode($vars));

        if ($this->ig->cdn_rmd === true) {
            $request->addHeader('X-Fb-Rmd', 'state=URL_ELIGIBLE');
        }

        if ($clientLibrary === 'graphservice') {
            $request->addPost('fb_api_caller_class', 'graphservice')
                    ->addPost('fb_api_analytics_tags', json_encode(['GraphServices']));
        }
        if ($clientLibrary === 'pando') {
            $request->addHeader('X-Fb-Request-Analytics-Tags', '{"network_tags":{"product":"567067343352427","purpose":"none","request_category":"graphql","retry_attempt":"0"},"application_tags":"pando"}')
                    ->addPost('enable_canonical_naming', 'true')
                    ->addPost('enable_canonical_variable_overrides', 'true')
                    ->addPost('enable_canonical_naming_ambiguous_type_prefixing', 'true');
        }
        if ($clientLibrary === 'minimal') {
            $request->addPost('strip_nulls', 'true')
                    ->addPost('signed_body', 'SIGNATURE.')
                    ->addPost('vc_policy', 'default')
                    ->addPost('strip_defaults', 'true');
        }

        if ($clientLibrary === 'graphservice' || $clientLibrary === 'pando') {
            $request->addPost('fb_api_req_friendly_name', $friendlyName)
                    ->addPost('pretty', $pretty)
                    ->addPost('format', 'json')
                    ->addPost('method', 'post')
                    ->addPost('server_timestamps', 'true');
        }

        return $request->getResponse(new Response\GenericResponse());
    }

    /**
     * Starts new user flow when registering with phone number.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function startNewUserFlow()
    {
        return $this->ig->request('consent/new_user_flow_begins/')
            ->setNeedsAuth(false)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('device_id', $this->ig->uuid)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Check user age eligibility.
     *
     * @param int $day   Day of the month of your bith date.
     * @param int $month Month Number of the month of your birth date.
     * @param int $year  Year of your birth date.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AgeEligibilityResponse
     */
    public function checkAgeEligibility(
        $day,
        $month,
        $year)
    {
        return $this->ig->request('consent/check_age_eligibility/')
            ->setSignedPost(false)
            ->setNeedsAuth(false)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('day', $day)
            ->addPost('month', $month)
            ->addPost('year', $year)
            ->getResponse(new Response\AgeEligibilityResponse());
    }

    /**
     * Get consent steps for new user flow.
     *
     * @param string $waterfallId UUIDv4.
     * @param string $regMethod   Registration method. 'email' or 'phone'.
     * @param array  $seenSteps   Seen steps.
     * @param bool   $finish      Progress state has finished.
     * @param bool   $tosAccepted ToS Accepted.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getOnBoardingSteps(
        $waterfallId,
        $regMethod = 'email',
        $seenSteps = [],
        $finish = false,
        $tosAccepted = true)
    {
        if ($regMethod !== 'email' && $regMethod !== 'phone') {
            throw new \InvalidArgumentException(
                sprintf('%s registration method not valid', $regMethod));
        }

        return $this->ig->request('dynamic_onboarding/get_steps/')
            ->setNeedsAuth(false)
            ->addPost('is_secondary_account_creation', 'false')
            ->addPost('fb_connected', 'false')
            ->addPost('seen_steps', (empty($seenSteps)) ? '[]' : json_encode($seenSteps))
            ->addPost('progress_state', ($finish === false) ? 'start' : 'finish')
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('fb_installed', 'false')
            ->addPost('locale', $this->ig->getLocale())
            ->addPost('timezone_offset', ($this->ig->getTimezoneOffset() !== null) ? $this->ig->getTimezoneOffset() : date('Z'))
            ->addPost('network_type', 'WIFI-UNKNOWN')
            ->addPost('guid', $this->ig->uuid)
            ->addPost('is_ci', 'false')
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('device_id', $this->ig->uuid)
            ->addPost('waterfall_id', $waterfallId)
            ->addPost('reg_flow_taken', $regMethod)
            ->addPost('tos_accepted', $tosAccepted)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * New account nux seen.
     *
     * @param string $waterfallId Waterfall ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function newAccountNuxSeen(
        $waterfallId)
    {
        return $this->ig->request('nux/new_account_nux_seen/')
            ->addPost('is_fb4a_installed', 'false')
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('guid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('waterfall_id', $waterfallId)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get common push ndx screen.
     *
     * @param mixed $postImporter
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getCommonPushNdxScreen(
        $postImporter = false)
    {
        $request = $this->ig->request('bloks/apps/com.instagram.ndx.common.push_ig_ndx_screen/')
            ->setSignedPost(false)
            ->addPost('app_id', Constants::FACEBOOK_ANALYTICS_APPLICATION_ID)
            ->addPost('app_scoped_device_id', $this->ig->uuid)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('qp_id', ($postImporter === false) ? 3 : 0)
            ->addPost('ndx_eligible_flows', ($postImporter === false) ? json_encode(['ndx_eligible_flows']) : json_encode(['']))
            ->addPost('ig_ndx_source', 'NDX_IG_IMMERSIVE')
            ->addPost('bk_client_context', json_encode([
                'bloks_version' => Constants::BLOCK_VERSIONING_ID,
                'styles_id'     => 'instagram',
            ]))
            ->addPost('bloks_versioning_id', Constants::BLOCK_VERSIONING_ID);

        if ($postImporter === true) {
            $request->addPost('family_device_id', $this->ig->phone_id)
                    ->addPost('current_ndx_flow_index', 1)
                    ->addPost('total_ndx_steps', 1);
        }

        return $request->getResponse(new Response\GenericResponse());
    }

    /**
     * Get contact importer screen.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getContactImporterScreen()
    {
        return $this->ig->request('bloks/apps/com.instagram.ndx.contact_importer.contact_importer_screen/')
            ->setSignedPost(false)
            ->addPost('current_ndx_flow_index', 0)
            ->addPost('total_ndx_steps', 1)
            ->addPost('app_scoped_device_id', $this->ig->uuid)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('qp_id', 0)
            ->addPost('ndx_eligible_flows', json_encode(['']))
            ->addPost('ig_ndx_source', 'NDX_IG_IMMERSIVE')
            ->addPost('bk_client_context', json_encode([
                'bloks_version' => Constants::BLOCK_VERSIONING_ID,
                'styles_id'     => 'instagram',
            ]))
            ->addPost('bloks_versioning_id', Constants::BLOCK_VERSIONING_ID)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Send privacy consent prompt action.
     *
     * @param bool  $form     Form.
     * @param mixed $flowName
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function sendPrivacyConsentPromptAction(
        $flowName,
        $form = false)
    {
        $request = $this->ig->request('bloks/apps/com.bloks.www.privacy.consent.prompt.action/')
            ->setSignedPost(false)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('bk_client_context', json_encode([
                'bloks_version' => Constants::BLOCK_VERSIONING_ID,
                'styles_id'     => 'instagram',
            ]))
            ->addPost('bloks_versioning_id', Constants::BLOCK_VERSIONING_ID);

        switch ($flowName) {
            case 'new_users_meta_flow':
                if ($form === false) {
                    $request->addPost('surface', 'instagram_android')
                            ->addPost('flow_name', 'new_users_meta_flow')
                            ->addPost('source', 'source');
                } else {
                    $request->addPost('params', json_encode([
                        'server_params' => [
                            '_w_s228763'    => '',
                            'flow_name'     => 'new_users_meta_flow',
                            'source'        => 'source',
                        ], ]));
                }
                break;
            case 'user_cookie_choice':
                if ($form === false) {
                    $request->addPost('surface', 'instagram_android')
                            ->addPost('flow_name', 'user_cookie_choice')
                            ->addPost('source', 'pft_user_cookie_choice');
                } else {
                    $request->addPost('params', json_encode([
                        'server_params' => [
                            '_w_s228763'    => '',
                            'flow_name'     => 'user_cookie_choice',
                            'source'        => 'pft_user_cookie_choice',
                        ], ]));
                }
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid flow name provided: %s', $flowName));
                break;
        }

        return $request->getResponse(new Response\GenericResponse());
    }

    /**
     * Send privacy consent prompt callback.
     *
     * @param GenericResponse $response
     * @param bool            $init     Init.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function sendPrivacyConsentPromptCallback(
        $response,
        $init = false)
    {
        $re = '/[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}/m';
        preg_match_all($re, $response->asJson(), $matches, PREG_SET_ORDER, 0);

        if (empty($matches)) {
            throw new InstagramException('Invalid response provided');
        }

        $experienceId = $matches[0][0];

        $request = $this->ig->request('bloks/apps/com.bloks.www.privacy.consent.prompt.impression.callback/')
            ->setSignedPost(false)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('bk_client_context', json_encode([
                'bloks_version' => Constants::BLOCK_VERSIONING_ID,
                'styles_id'     => 'instagram',
            ]))
            ->addPost('bloks_versioning_id', Constants::BLOCK_VERSIONING_ID);

        $params = [
            'server_params' => [
                'prompt_context'    => [
                    'flow_step'         => 0,
                    'current_screen_id' => 'nsati0:2',
                    'previous_prompts'  => [],
                    'experience_id'     => $experienceId,
                    'first_screen_id'   => null,
            ],
            'source'        => 'pft_user_cookie_choice',
            'extra_params'  => [
                'attribution_events_flush'  => 'true',
            ],
            'config_enum'   => 'user_cookie_choice_french_cnil',
            ],
        ];

        if ($init === false) {
            $params['event'] = 'consent_interactions_prompt_impression';
        } else {
            $params['event_type'] = 'accept';
        }

        return $request->addPost('params', json_encode($params))
                       ->getResponse(new Response\GenericResponse());
    }

    /**
     * Starts new user flow when registering with phone number.
     *
     * @param string $email       Email for registration.
     * @param bool   $acceptedTos Accepted Instagrams' ToS.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConsentRequiredResponse
     */
    public function newUserFlow(
        $email,
        $acceptedTos = false)
    {
        $request = $this->ig->request('consent/new_user_flow/')
            ->setNeedsAuth(false)
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('gdpr_s', '')
            ->addPost('guid', $this->ig->uuid)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('device_id', $this->ig->device_id);

        if ($acceptedTos) {
            $request->addPost('current_screen_key', 'age_consent_two_button')
                    ->addPost('gdpr_s', '[0,0,0,null]')
                    ->addPost('updates', json_encode(['age_consent_state' => '2']));
        } else {
            $request->addPost('email', $email);
        }

        return $request->getResponse(new Response\ConsentRequiredResponse());
    }

    /**
     * Send consent policy to Instagram.
     *
     * @param bool $screenKey Current screen key.
     * @param int  $day       Day of the month of your bith date.
     * @param int  $month     Month Number of the month of your birth date.
     * @param int  $year      Year of your birth date.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConsentRequiredResponse
     */
    public function sendConsent(
        $screenKey = null,
        $day = null,
        $month = null,
        $year = null)
    {
        $request = $this->ig->request('consent/existing_user_flow/')
             ->setNeedsAuth(false)
             ->addPost('device_id', $this->ig->device_id)
             //->addPost('_csrftoken', $this->ig->client->getToken())
             ->addPost('_uid', $this->ig->account_id)
             ->addPost('_uuid', $this->ig->uuid);

        switch ($screenKey) {
            case 'qp_intro':
                $request->addPost('current_screen_key', 'qp_intro')
                        ->addPost('updates', json_encode(['existing_user_intro_state' => '2']));
                break;
            case 'dob':
                $request->addPost('current_screen_key', 'dob')
                        ->addPost('day', $day)
                        ->addPost('month', $month)
                        ->addPost('year', $year);
                break;
            case 'tos':
                $request->addPost('current_screen_key', 'tos')
                        ->addPost('updates', json_encode(['tos_data_policy_consent_state' => '2']));
                break;
            case 'tos_and_two_age_button':
                $request->addPost('current_screen_key', 'tos')
                        ->addPost('updates', json_encode(['age_consent_state' => '2', 'tos_data_policy_consent_state' => '2']));
                        // no break
            default:
                break;
        }

        return $request->getResponse(new Response\ConsentRequiredResponse());
    }

    /**
     * Get notes.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getNotes()
    {
        return $this->ig->request('notes/get_notes/')
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Internal helper for marking story media items as seen.
     *
     * This is used by story-related functions in other request-collections!
     *
     * @param Response\Model\Item[] $items        Array of one or more story media Items.
     * @param string|null           $sourceId     Where the story was seen from,
     *                                            such as a location story-tray ID.
     *                                            If NULL, we automatically use the
     *                                            user's profile ID from each Item
     *                                            object as the source ID.
     * @param string                $module       Module where the story was found.
     * @param Response\Model\Item[] $skippedItems Array of one or more story
     *                                            media Items.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaSeenResponse
     *
     * @see Story::markMediaSeen()
     * @see Location::markStoryMediaSeen()
     * @see Hashtag::markStoryMediaSeen()
     */
    public function markStoryMediaSeen(
        array $items,
        $sourceId = null,
        $module = 'feed_timeline',
        $skippedItems = [])
    {
        // Build the list of seen media, with human randomization of seen-time.
        $reels = [];
        $maxSeenAt = time(); // Get current global UTC timestamp.
        $seenAt = $maxSeenAt - (3 * count($items)); // Start seenAt in the past.
        foreach ($items as $item) {
            if (!$item instanceof Response\Model\Item) {
                throw new \InvalidArgumentException(
                    'All story items must be instances of \InstagramAPI\Response\Model\Item.'
                );
            }

            // Raise "seenAt" if it's somehow older than the item's "takenAt".
            // NOTE: Can only happen if you see a story instantly when posted.
            $itemTakenAt = $item->getTakenAt();
            if ($seenAt < $itemTakenAt) {
                $seenAt = $itemTakenAt + 2;
            }

            // Do not let "seenAt" exceed the current global UTC time.
            if ($seenAt > $maxSeenAt) {
                $seenAt = $maxSeenAt;
            }

            // Determine the source ID for this item. This is where the item was
            // seen from, such as a UserID or a Location-StoryTray ID.
            $itemSourceId = ($sourceId === null ? $item->getUser()->getPk() : $sourceId);

            // Key Format: "mediaPk_userPk_sourceId".
            // NOTE: In case of seeing stories on a user's profile, their
            // userPk is used as the sourceId, as "mediaPk_userPk_userPk".
            $reelId = $item->getId().'_'.$itemSourceId;

            // Value Format: ["mediaTakenAt_seenAt"] (array with single string).
            $reels[$reelId] = [$itemTakenAt.'_'.$seenAt];

            // Randomly add 1-3 seconds to next seenAt timestamp, to act human.
            $seenAt += rand(1, 3);
        }

        return $this->ig->request('media/seen/')
            ->setVersion(2)
            ->setIsBodyCompressed(true)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('container_module', $module)
            ->addPost('reels', $reels)
            ->addPost('reel_media_skipped', $skippedItems)
            ->addPost('live_vods', [])
            ->addPost('live_vods_skipped', [])
            ->addPost('nuxes', [])
            ->addPost('nuxes_skipped', [])
            ->addParam('reel', 1)
            ->addParam('live_vod', 0)
            ->getResponse(new Response\MediaSeenResponse());
    }

    /**
     * Configure media entity (album, video, ...) with retries.
     *
     * @param callable $configurator Configurator function.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \LogicException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response
     */
    public function configureWithRetries(
        callable $configurator)
    {
        $attempt = 0;
        $lastError = null;
        while (true) {
            // Check for max retry-limit, and throw if we exceeded it.
            if (++$attempt > $this->getMaxConfigureRetries()) {
                if ($lastError === null) {
                    throw new \RuntimeException('All configuration retries have failed.');
                }

                throw new \RuntimeException(sprintf(
                    'All configuration retries have failed. Last error: %s',
                    $lastError
                ));
            }

            $result = null;

            try {
                /** @var Response $result */
                $result = $configurator();
            } catch (ThrottledException $e) {
                throw $e;
            } catch (LoginRequiredException $e) {
                throw $e;
            } catch (FeedbackRequiredException $e) {
                throw $e;
            } catch (RetryUploadFlowException $e) {
                throw $e;
            } catch (ConsentRequiredException $e) {
                throw $e;
            } catch (CheckpointRequiredException $e) {
                throw $e;
            } catch (ChallengeRequiredException $e) {
                throw $e;
            } catch (InstagramException $e) {
                if ($e->hasResponse()) {
                    $result = $e->getResponse();
                }
                $lastError = $e;
                if ($this->ig->getIsDisabledAutoRetriesMediaUpload()) {
                    throw $e;
                }
            } catch (\Exception $e) {
                $lastError = $e;
                // Ignore everything else.
                if ($this->ig->getIsDisabledAutoRetriesMediaUpload()) {
                    throw $e;
                }
            }

            // We had a network error or something like that, let's continue to the next attempt.
            if ($result === null) {
                sleep(1);
                continue;
            }

            $httpResponse = $result->getHttpResponse();
            $delay = 5;
            switch ($httpResponse->getStatusCode()) {
                case 200:
                    // Instagram uses "ok" status for this error, so we need to check it first:
                    // {"message": "media_needs_reupload", "error_title": "staged_position_not_found", "status": "ok"}
                    if ($result->getMessage() !== null && strtolower($result->getMessage()) === 'media_needs_reupload') {
                        throw new \RuntimeException(sprintf(
                            'You need to reupload the media (%s).',
                            // We are reading a property that isn't defined in the class
                            // property map, so we must use "has" first, to ensure it exists.
                            ($result->hasErrorTitle() && is_string($result->getErrorTitle())
                             ? $result->getErrorTitle()
                             : 'unknown error')
                        ));
                    } elseif ($result->isOk()) {
                        return $result;
                    }
                    // Continue to the next attempt.
                    break;
                case 202:
                    // We are reading a property that isn't defined in the class
                    // property map, so we must use "has" first, to ensure it exists.
                    if ($result->hasCooldownTimeInSeconds() && $result->getCooldownTimeInSeconds() !== null) {
                        $delay = max((int) $result->getCooldownTimeInSeconds(), 1);
                    }
                    break;
                default:
            }
            sleep($delay);
        }

        // We are never supposed to get here!
        throw new \LogicException('Something went wrong during configuration.');
    }

    /**
     * Update media with PDQ hash info.
     *
     * @param string   $uploadId  Media Upload ID.
     * @param string[] $pdqHashes Array of PDQ Hashes.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function updateMediaWithPdqHashes(
        $uploadId,
        $pdqHashes)
    {
        $request = $this->ig->request('media/update_media_with_pdq_hash_info/')
             ->addPost('upload_id', $uploadId)
             //->addPost('_csrftoken', $this->ig->client->getToken())
             ->addPost('_uid', $this->ig->account_id)
             ->addPost('_uuid', $this->ig->uuid);

        $pdqHashInfo = [];
        foreach ($pdqHashes as $idx => $pdqHash) {
            $pdqHashInfo[] = [
                'pdq_hash'      => sprintf('%s:1', $pdqHash),
                'frame_time'    => round(Constants::PDQ_VIDEO_TIME_FRAMES[$idx] * 1000),
            ];
        }
        $request->addPost('pdq_hash_info', json_encode($pdqHashInfo));

        return $request->getResponse(new Response\GenericResponse());
    }

    /**
     * Performs a resumable upload of a media file, with support for retries.
     *
     * @param MediaDetails $mediaDetails
     * @param Request      $offsetTemplate
     * @param Request      $uploadTemplate
     * @param bool         $skipGet
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \LogicException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\ResumableUploadResponse
     */
    protected function _uploadResumableMedia(
        MediaDetails $mediaDetails,
        Request $offsetTemplate,
        Request $uploadTemplate,
        $skipGet)
    {
        // Open file handle.
        $handle = fopen($mediaDetails->getFilename(), 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Failed to open media file for reading.');
        }

        try {
            $length = $mediaDetails->getFilesize();

            // Create a stream for the opened file handle.
            $stream = new Stream($handle, ['size' => $length]);

            $attempt = 0;
            while (true) {
                // Check for max retry-limit, and throw if we exceeded it.
                if (++$attempt > self::MAX_RESUMABLE_RETRIES) {
                    throw new \RuntimeException('All retries have failed.');
                }

                try {
                    if ($attempt === 1 && $skipGet) {
                        // It is obvious that the first attempt is always at 0, so we can skip a request.
                        $offset = 0;
                    } else {
                        // Get current offset.
                        $offsetRequest = clone $offsetTemplate;
                        /** @var Response\ResumableOffsetResponse $offsetResponse */
                        $offsetResponse = $offsetRequest->getResponse(new Response\ResumableOffsetResponse());
                        $offset = $offsetResponse->getOffset();
                    }

                    // Resume upload from given offset.
                    $uploadRequest = clone $uploadTemplate;
                    $uploadRequest
                        ->addHeader('Offset', $offset)
                        ->setBody(new LimitStream($stream, $length - $offset, $offset));
                    /** @var Response\ResumableUploadResponse $response */
                    $response = $uploadRequest->getResponse(new Response\ResumableUploadResponse());

                    return $response;
                } catch (ThrottledException $e) {
                    throw $e;
                } catch (LoginRequiredException $e) {
                    throw $e;
                } catch (FeedbackRequiredException $e) {
                    throw $e;
                } catch (RetryUploadFlowException $e) {
                    throw $e;
                } catch (ConsentRequiredException $e) {
                    throw $e;
                } catch (CheckpointRequiredException $e) {
                    throw $e;
                } catch (ChallengeRequiredException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    // Ignore everything else.
                }
            }
        } finally {
            Utils::safe_fclose($handle);
        }

        // We are never supposed to get here!
        throw new \LogicException('Something went wrong during media upload.');
    }

    /**
     * Performs an upload of a photo file, without support for retries.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UploadPhotoResponse
     */
    protected function _uploadPhotoInOnePiece(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        // Prepare payload for the upload request.
        $request = $this->ig->request('upload/photo/')
            ->setSignedPost(false)
            ->addPost('_uuid', $this->ig->uuid)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addFile(
                'photo',
                $internalMetadata->getPhotoDetails()->getFilename(),
                'pending_media_'.Utils::generateUploadId().'.jpg'
            );

        foreach ($this->_getPhotoUploadParams($targetFeed, $internalMetadata) as $key => $value) {
            $request->addPost($key, $value);
        }
        /** @var Response\UploadPhotoResponse $response */
        $response = $request->getResponse(new Response\UploadPhotoResponse());

        return $response;
    }

    /**
     * Performs a resumable upload of a photo file, with support for retries.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \LogicException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    protected function _uploadResumablePhoto(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        $photoDetails = $internalMetadata->getPhotoDetails();

        if ($targetFeed === Constants::FEED_DIRECT) {
            $url = 'https://rupload.facebook.com/messenger_image';
        } else {
            $url = 'https://i.instagram.com/rupload_igphoto';
        }
        $endpoint = sprintf('%s/%s_%d_%d',
            $url,
            $internalMetadata->getUploadId(),
            0,
            Utils::hashCode($photoDetails->getFilename())
            //time()
        );

        $uploadParams = $this->_getPhotoUploadParams($targetFeed, $internalMetadata);
        $uploadParams = Utils::reorderByHashCode($uploadParams);

        $offsetTemplate = new Request($this->ig, $endpoint, $this->ig->customResolver);
        $offsetTemplate
            ->setAddDefaultHeaders(false)
            ->addHeader('X_FB_PHOTO_WATERFALL_ID', Signatures::generateUUID())
            ->addHeader('X-Instagram-Rupload-Params', json_encode($uploadParams))
            ->setRequestPriority(6);

        $uploadTemplate = clone $offsetTemplate;
        $uploadTemplate
            ->addHeader('Priority', $uploadTemplate->getRequestPriority())
            ->addHeader('X-Entity-Type', ($targetFeed !== Constants::FEED_DIRECT && $targetFeed !== Constants::PROFILE_PIC) ? 'image/webp' : 'image/jpeg')
            ->addHeader('X-Entity-Name', basename(parse_url($endpoint, PHP_URL_PATH)))
            ->addHeader('X-Entity-Length', $photoDetails->getFilesize())
            ->addHeader('X-FB-Connection-Type', ($this->ig->getRadioType() === 'wifi-none') ? Constants::X_IG_Connection_Type : 'MOBILE(LTE)')
            ->addHeader('X-IG-Connection-Type', ($this->ig->getRadioType() === 'wifi-none') ? Constants::X_IG_Connection_Type : 'MOBILE(LTE)')
            ->addHeader('X-IG-App-ID', Constants::FACEBOOK_ANALYTICS_APPLICATION_ID)
            ->addHeader('X-IG-Capabilities', Constants::X_IG_Capabilities)
            ->addHeader('X-FB-HTTP-Engine', Constants::X_FB_HTTP_Engine)
            ->addHeader('X-FB-Client-IP', 'True')
            ->addHeader('X-FB-Server-Cluster', 'True');

        if ($targetFeed === Constants::FEED_DIRECT) {
            $uploadTemplate->addHeader('Image_type', 'FILE_ATTACHMENT');
        }

        $this->ig->event->startIngestMedia(
            $internalMetadata->getUploadId(),
            $uploadParams['media_type'],
            $internalMetadata->getWaterfallID(),
            $internalMetadata->getIsCarousel()
        );

        $this->ig->event->startUploadAttempt(
            $internalMetadata->getUploadId(),
            $uploadParams['media_type'],
            $internalMetadata->getWaterfallID(),
            $internalMetadata->getIsCarousel()
        );

        $uploadResumableMedia = $this->_uploadResumableMedia(
            $photoDetails,
            $offsetTemplate,
            $uploadTemplate,
            true
        );

        if ($targetFeed === Constants::FEED_DIRECT) {
            $internalMetadata->setUploadId($uploadResumableMedia->getMediaId());
        }

        $this->ig->event->uploadMediaSuccess(
            $internalMetadata->getUploadId(),
            $uploadParams['media_type'],
            $internalMetadata->getWaterfallID(),
            $internalMetadata->getIsCarousel()
        );

        return $uploadResumableMedia;
    }

    /**
     * Determine whether to use resumable photo uploader based on target feed and internal metadata.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @return bool
     */
    protected function _useResumablePhotoUploader(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        switch ($targetFeed) {
            case Constants::FEED_TIMELINE_ALBUM:
                $result = $this->ig->isExperimentEnabled(
                    'ig_android_sidecar_photo_fbupload_universe',
                    'is_enabled_fbupload_sidecar_photo');
                break;
            default:
                $result = $this->ig->isExperimentEnabled(
                    'ig_android_photo_fbupload_universe',
                    'is_enabled_fbupload_photo');
        }

        return $result;
    }

    /**
     * Get the first missing range (start-end) from a HTTP "Range" header.
     *
     * @param string $ranges
     *
     * @return array|null
     */
    protected function _getFirstMissingRange(
        $ranges)
    {
        preg_match_all('/(?<start>\d+)-(?<end>\d+)\/(?<total>\d+)/', $ranges, $matches, PREG_SET_ORDER);
        if (!count($matches)) {
            return;
        }
        $pairs = [];
        $length = 0;
        foreach ($matches as $match) {
            $pairs[] = [$match['start'], $match['end']];
            $length = $match['total'];
        }
        // Sort pairs by start.
        usort($pairs, function (array $pair1, array $pair2) {
            return $pair1[0] - $pair2[0];
        });
        $first = $pairs[0];
        $second = count($pairs) > 1 ? $pairs[1] : null;
        if ($first[0] == 0) {
            $result = [$first[1] + 1, ($second === null ? $length : $second[0]) - 1];
        } else {
            $result = [0, $first[0] - 1];
        }

        return $result;
    }

    /**
     * Performs a chunked upload of a video file, with support for retries.
     *
     * Note that chunk uploads often get dropped when their server is overloaded
     * at peak hours, which is why our chunk-retry mechanism exists. We will
     * try several times to upload all chunks. The retries will only re-upload
     * the exact chunks that have been dropped from their server, and it won't
     * waste time with chunks that are already successfully uploaded.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \LogicException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UploadVideoResponse
     */
    protected function _uploadVideoChunks(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        $videoFilename = $internalMetadata->getVideoDetails()->getFilename();

        // To support video uploads to albums, we MUST fake-inject the
        // "sessionid" cookie from "i.instagram" into our "upload.instagram"
        // request, otherwise the server will reply with a "StagedUpload not
        // found" error when the final chunk has been uploaded.
        $sessionIDCookie = null;
        if ($targetFeed === Constants::FEED_TIMELINE_ALBUM) {
            $foundCookie = $this->ig->client->getCookie('sessionid', 'i.instagram.com');
            if ($foundCookie !== null) {
                $sessionIDCookie = $foundCookie->getValue();
            }
            if ($sessionIDCookie === null || $sessionIDCookie === '') { // Verify value.
                throw new \RuntimeException(
                    'Unable to find the necessary SessionID cookie for uploading video album chunks.'
                );
            }
        }

        // Verify the upload URLs.
        $uploadUrls = $internalMetadata->getVideoUploadUrls();
        if (!is_array($uploadUrls) || !count($uploadUrls)) {
            throw new \RuntimeException('No video upload URLs found.');
        }

        // Init state.
        $length = $internalMetadata->getVideoDetails()->getFilesize();
        $uploadId = $internalMetadata->getUploadId();
        $sessionId = sprintf('%s-%d', $uploadId, Utils::hashCode($videoFilename));
        $uploadUrl = array_shift($uploadUrls);
        $offset = 0;
        $chunk = min($length, self::MIN_CHUNK_SIZE);
        $attempt = 0;

        // Open file handle.
        $handle = fopen($videoFilename, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Failed to open file for reading.');
        }

        try {
            // Create a stream for the opened file handle.
            $stream = new Stream($handle);
            while (true) {
                // Check for this server's max retry-limit, and switch server?
                if (++$attempt > self::MAX_CHUNK_RETRIES) {
                    $uploadUrl = null;
                }

                // Try to switch to another server.
                if ($uploadUrl === null) {
                    $uploadUrl = array_shift($uploadUrls);
                    // Fail if there are no upload URLs left.
                    if ($uploadUrl === null) {
                        throw new \RuntimeException('There are no more upload URLs.');
                    }
                    // Reset state.
                    $attempt = 1; // As if "++$attempt" had ran once, above.
                    $offset = 0;
                    $chunk = min($length, self::MIN_CHUNK_SIZE);
                }

                // Prepare request.
                $request = new Request($this->ig, $uploadUrl->getUrl(), $this->ig->customResolver);
                $request
                    ->setAddDefaultHeaders(false)
                    ->addHeader('Content-Type', 'application/octet-stream')
                    ->addHeader('Session-ID', $sessionId)
                    ->addHeader('Content-Disposition', 'attachment; filename="video.mov"')
                    ->addHeader('Content-Range', 'bytes '.$offset.'-'.($offset + $chunk - 1).'/'.$length)
                    ->addHeader('job', $uploadUrl->getJob())
                    ->setBody(new LimitStream($stream, $chunk, $offset));

                // When uploading videos to albums, we must fake-inject the
                // "sessionid" cookie (the official app fake-injects it too).
                if ($targetFeed === Constants::FEED_TIMELINE_ALBUM && $sessionIDCookie !== null) {
                    // We'll add it with the default options ("single use")
                    // so the fake cookie is only added to THIS request.
                    $this->ig->client->fakeCookies()->add('sessionid', $sessionIDCookie);
                }

                // Perform the upload of the current chunk.
                $start = microtime(true);

                try {
                    $httpResponse = $request->getHttpResponse();
                } catch (NetworkException $e) {
                    // Ignore network exceptions.
                    continue;
                }

                // Determine new chunk size based on upload duration.
                $newChunkSize = (int) ($chunk / (microtime(true) - $start) * 5);
                // Ensure that the new chunk size is in valid range.
                $newChunkSize = min(self::MAX_CHUNK_SIZE, max(self::MIN_CHUNK_SIZE, $newChunkSize));

                $result = null;

                try {
                    /** @var Response\UploadVideoResponse $result */
                    $result = $request->getResponse(new Response\UploadVideoResponse());
                } catch (CheckpointRequiredException $e) {
                    throw $e;
                } catch (ChallengeRequiredException $e) {
                    throw $e;
                } catch (LoginRequiredException $e) {
                    throw $e;
                } catch (FeedbackRequiredException $e) {
                    throw $e;
                } catch (ConsentRequiredException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    // Ignore everything else.
                }

                // Process the server response...
                switch ($httpResponse->getStatusCode()) {
                    case 200:
                        // All chunks are uploaded, but if we don't have a
                        // response-result now then we must retry a new server.
                        if ($result === null) {
                            $uploadUrl = null;
                            break;
                        }

                        // SUCCESS! :-)
                        return $result;
                    case 201:
                        // The server has given us a regular reply. We expect it
                        // to be a range-reply, such as "0-3912399/23929393".
                        // Their server often drops chunks during peak hours,
                        // and in that case the first range may not start at
                        // zero, or there may be gaps or multiple ranges, such
                        // as "0-4076155/8152310,6114234-8152309/8152310". We'll
                        // handle that by re-uploading whatever they've dropped.
                        if (!$httpResponse->hasHeader('Range')) {
                            $uploadUrl = null;
                            break;
                        }
                        $range = $this->_getFirstMissingRange($httpResponse->getHeaderLine('Range'));
                        if ($range !== null) {
                            $offset = $range[0];
                            $chunk = min($newChunkSize, $range[1] - $range[0] + 1);
                        } else {
                            $chunk = min($newChunkSize, $length - $offset);
                        }

                        // Reset attempts count on successful upload.
                        $attempt = 0;
                        break;
                    case 400:
                    case 403:
                    case 511:
                        throw new \RuntimeException(sprintf(
                            'Instagram\'s server returned HTTP status "%d".',
                            $httpResponse->getStatusCode()
                        ));
                    case 422:
                        throw new \RuntimeException('Instagram\'s server says that the video is corrupt.');
                    default:
                }
            }
        } finally {
            // Guaranteed to release handle even if something bad happens above!
            Utils::safe_fclose($handle);
        }

        // We are never supposed to get here!
        throw new \LogicException('Something went wrong during video upload.');
    }

    /**
     * Performs a segmented upload of a video file, with support for retries.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \LogicException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    protected function _uploadSegmentedVideo(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        $videoDetails = $internalMetadata->getVideoDetails();

        // We must split the video into segments before running any requests.
        //$segments = $this->_splitVideoIntoSegments($targetFeed, $videoDetails);
        $segments = [$videoDetails]; // 1 segment, no split.

        $uploadParams = $this->_getVideoUploadParams($targetFeed, $internalMetadata);
        $uploadParams = Utils::reorderByHashCode($uploadParams);

        $ts = intval(microtime(true) * 1000);

        /*
        // This request gives us a stream identifier.
        $startRequest = new Request($this->ig, sprintf(
            'https://i.instagram.com/rupload_igvideo/%s-%d-%d-%d-%d',
            md5($segments[0]->getFilename()),
            0,
            $segments[0]->getFilesize(),
            $ts,
            $ts
        ), $this->ig->customResolver);
        $startRequest
            ->setAddDefaultHeaders(false)
            ->addHeader('X-Instagram-Rupload-Params', json_encode($uploadParams));
            // Dirty hack to make a POST request.
            //->setBody(GuzzleUtils::streamFor());
        // @var Response\SegmentedStartResponse $startResponse
        $startResponse = $startRequest->getResponse(new Response\SegmentedStartResponse());
        //$streamId = $startResponse->getStreamId(); Seems Stream ID is not longer being used.
        */

        // Upload the segments.
        try {
            $offset = 0;
            // Yep, no UUID here like in other resumable uploaders. Seems like a bug.
            $waterfallId = sprintf('%s_%s_Mixed_0', $internalMetadata->getUploadId(), bin2hex(random_bytes(6))); //Utils::generateUploadId();
            foreach ($segments as $idx => $segment) {
                $endpoint = sprintf(
                    'https://i.instagram.com/rupload_igvideo/%s-%d-%d-%d-%d',
                    md5($segment->getFilename()),
                    0,
                    $segment->getFilesize(),
                    $ts,
                    $ts
                );

                $offsetTemplate = new Request($this->ig, $endpoint, $this->ig->customResolver);
                $offsetTemplate
                    ->setAddDefaultHeaders(false)
                    ->addHeader('Segment-Start-Offset', $offset)
                    // 1 => Audio, 2 => Video, 3 => Mixed.
                    ->addHeader('Segment-Type', $segment->getAudioCodec() !== null ? 1 : 2)
                    //->addHeader('Stream-Id', $streamId)
                    ->addHeader('X_FB_VIDEO_WATERFALL_ID', $waterfallId)
                    ->addHeader('X-Instagram-Rupload-Params', json_encode($uploadParams));

                if ($offset === 0) {
                    $initRequest = clone $offsetTemplate;
                }

                $uploadTemplate = clone $offsetTemplate;
                $uploadTemplate
                    ->addHeader('X-Entity-Type', 'video/mp4')
                    ->addHeader('X-Entity-Name', basename(parse_url($endpoint, PHP_URL_PATH)))
                    ->addHeader('X-Entity-Length', $segment->getFilesize());

                $this->ig->event->startIngestMedia(
                    $internalMetadata->getUploadId(),
                    $uploadParams['media_type'],
                    $internalMetadata->getWaterfallID(),
                    $internalMetadata->getIsCarousel()
                );

                $this->ig->event->startIngestMedia(
                    $internalMetadata->getUploadId(),
                    $uploadParams['media_type'],
                    $internalMetadata->getWaterfallID(),
                    $internalMetadata->getIsCarousel()
                );

                $this->ig->event->startUploadAttempt(
                    $internalMetadata->getUploadId(),
                    $uploadParams['media_type'],
                    $internalMetadata->getWaterfallID(),
                    $internalMetadata->getIsCarousel()
                );

                $result = $this->_uploadResumableMedia($segment, $offsetTemplate, $uploadTemplate, false);
                // Offset seems to be used just for ordering the segments.
                $offset += $segment->getFilesize();
            }
        } finally {
            // Remove the segments, because we don't need them anymore.
            /*
            foreach ($segments as $segment) {
                @unlink($segment->getFilename());
            }*/
        }

        /*
        // Finalize the upload.
        $endRequest = new Request($this->ig, sprintf(
            'https://i.instagram.com/rupload_igvideo/%s?segmented=true&phase=end',
            Signatures::generateUUID()
        ), $this->ig->customResolver);
        $endRequest
            ->setAddDefaultHeaders(false)
            ->addHeader('Stream-Id', $streamId)
            ->addHeader('X-Instagram-Rupload-Params', json_encode($uploadParams))
            // Dirty hack to make a POST request.
            ->setBody(GuzzleUtils::streamFor());
        // @var Response\GenericResponse $result
        $result = $endRequest->getResponse(new Response\GenericResponse());
        */

        $this->ig->event->uploadMediaSuccess(
            $internalMetadata->getUploadId(),
            $uploadParams['media_type'],
            $internalMetadata->getWaterfallID(),
            $internalMetadata->getIsCarousel()
        );

        return $result;
    }

    /**
     * Performs a segmented upload of a video file, with support for retries using Facebook uploader.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \LogicException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    protected function _uploadSegmentedVideoFacebook(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        $videoDetails = $internalMetadata->getVideoDetails();

        // We must split the video into segments before running any requests.
        //$segments = $this->_splitVideoIntoSegments($targetFeed, $videoDetails);

        $uploadParams = $this->_getVideoUploadParams($targetFeed, $internalMetadata);
        $uploadParams = Utils::reorderByHashCode($uploadParams);

        switch ($targetFeed) {
            case Constants::FEED_DIRECT_AUDIO:
                $uploaderType = 'messenger_audio';
                break;
            default:
                $uploaderType = 'messenger_image';
        }

        // Upload the segments.
        try {
            $offset = 0;
            $endpoint = sprintf(
                'https://rupload.facebook.com/%s/%s_%d_%d',
                $uploaderType,
                $internalMetadata->getUploadId(),
                0,
                Utils::hashCode($videoDetails->getFilename()) & 0xfffffff
            );

            $offsetTemplate = new Request($this->ig, $endpoint, $this->ig->customResolver);
            if ($targetFeed === Constants::FEED_DIRECT_AUDIO) {
                $offsetTemplate->addHeader('Audio_type', 'FILE_ATTACHMENT');
            }

            // 1 => Audio, 2 => Video, 3 => Mixed.
            //->addHeader('Segment-Type', $segment->getAudioCodec() !== null ? 1 : 2)

            $uploadTemplate = clone $offsetTemplate;
            $uploadTemplate
                ->addHeader('X-Entity-Type', 'video/mp4')
                ->addHeader('X-Entity-Name', basename(parse_url($endpoint, PHP_URL_PATH)))
                ->addHeader('X-Entity-Length', $videoDetails->getFilesize());

            $response = $this->_uploadResumableMedia($videoDetails, $offsetTemplate, $uploadTemplate, false);
            // Offset seems to be used just for ordering the segments.
            $offset += $videoDetails->getFilesize();
        } catch (\Exception $e) {
        }

        return $response;
    }

    /**
     * Performs a resumable upload of a video file, with support for retries.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \LogicException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    protected function _uploadResumableVideo(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        $rurCookie = $this->ig->client->getCookie('rur', 'i.instagram.com');
        if ($rurCookie === null || $rurCookie->getValue() === '') {
            throw new \RuntimeException(
                'Unable to find the necessary "rur" cookie for uploading video.'
            );
        }

        $videoDetails = $internalMetadata->getVideoDetails();

        $endpoint = sprintf('https://i.instagram.com/rupload_igvideo/%s_%d_%d?target=%s',
            $internalMetadata->getUploadId(),
            0,
            Utils::hashCode($videoDetails->getFilename()),
            $rurCookie->getValue()
        );

        $uploadParams = $this->_getVideoUploadParams($targetFeed, $internalMetadata);
        $uploadParams = Utils::reorderByHashCode($uploadParams);

        $offsetTemplate = new Request($this->ig, $endpoint, $this->ig->customResolver);
        $offsetTemplate
            ->setAddDefaultHeaders(false)
            ->addHeader('X_FB_VIDEO_WATERFALL_ID', Signatures::generateUUID())
            ->addHeader('X-Instagram-Rupload-Params', json_encode($uploadParams));

        $uploadTemplate = clone $offsetTemplate;
        $uploadTemplate
            ->addHeader('X-Entity-Type', 'video/mp4')
            ->addHeader('X-Entity-Name', basename(parse_url($endpoint, PHP_URL_PATH)))
            ->addHeader('X-Entity-Length', $videoDetails->getFilesize());

        return $this->_uploadResumableMedia(
            $videoDetails,
            $offsetTemplate,
            $uploadTemplate,
            true
        );
    }

    /**
     * Determine whether to use segmented video uploader based on target feed and internal metadata.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @return bool
     */
    protected function _useSegmentedVideoUploader(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        // ffmpeg is required for video segmentation.
        try {
            FFmpeg::factory();
        } catch (\Exception $e) {
            return false;
        }

        // There is no need to segment short videos.
        switch ($targetFeed) {
            case Constants::FEED_DIRECT:
            case Constants::FEED_TIMELINE:
            case Constants::FEED_TIMELINE_ALBUM:
                $minDuration = (int) $this->ig->getExperimentParam(
                    'ig_android_video_segmented_upload_universe',
                    // NOTE: This typo is intentional. Instagram named it that way.
                    'segment_duration_threashold_feed',
                    0
                );
                break;
            case Constants::FEED_STORY:
            case Constants::FEED_DIRECT_STORY:
                $minDuration = (int) $this->ig->getExperimentParam(
                    'ig_android_video_segmented_upload_universe',
                    // NOTE: This typo is intentional. Instagram named it that way.
                    'segment_duration_threashold_story_raven',
                    0
                );
                break;
            case Constants::FEED_TV:
                $minDuration = 150;
                break;
            default:
                $minDuration = 31536000; // 1 year.
        }
        if ((int) $internalMetadata->getVideoDetails()->getDuration() < $minDuration) {
            return false;
        }

        // Check experiments for the target feed.
        switch ($targetFeed) {
            case Constants::FEED_TIMELINE:
                $result = $this->ig->isExperimentEnabled(
                    'ig_android_video_segmented_upload_universe',
                    'segment_enabled_feed',
                    true);
                break;
            case Constants::FEED_DIRECT:
                $result = $this->ig->isExperimentEnabled(
                    'ig_android_direct_video_segmented_upload_universe',
                    'is_enabled_segment_direct');
                break;
            case Constants::FEED_STORY:
            case Constants::FEED_DIRECT_STORY:
                $result = $this->ig->isExperimentEnabled(
                    '34393', //ig_android_reel_raven_video_segmented_upload_universe',
                    7); //'segment_enabled_story_raven');
                break;
            case Constants::FEED_TV:
                $result = true;
                break;
            default:
                $result = $this->ig->isExperimentEnabled(
                    'ig_android_video_segmented_upload_universe',
                    'segment_enabled_unknown',
                    true);
        }

        return $result;
    }

    /**
     * Determine whether to use resumable video uploader based on target feed and internal metadata.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @return bool
     */
    protected function _useResumableVideoUploader(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        switch ($targetFeed) {
            case Constants::FEED_TIMELINE_ALBUM:
                $result = $this->ig->isExperimentEnabled(
                    'ig_android_fbupload_sidecar_video_universe',
                    'is_enabled_fbupload_sidecar_video');
                break;
            case Constants::FEED_TIMELINE:
                $result = $this->ig->isExperimentEnabled(
                    'ig_android_upload_reliability_universe',
                    'is_enabled_fbupload_followers_share');
                break;
            case Constants::FEED_DIRECT:
                $result = $this->ig->isExperimentEnabled(
                    'ig_android_upload_reliability_universe',
                    'is_enabled_fbupload_direct_share');
                break;
            case Constants::FEED_STORY:
                $result = $this->ig->isExperimentEnabled(
                    'ig_android_upload_reliability_universe',
                    'is_enabled_fbupload_reel_share');
                break;
            case Constants::FEED_DIRECT_STORY:
                $result = $this->ig->isExperimentEnabled(
                    'ig_android_upload_reliability_universe',
                    'is_enabled_fbupload_story_share');
                break;
            case Constants::FEED_TV:
                $result = true;
                break;
            default:
                $result = $this->ig->isExperimentEnabled(
                    'ig_android_upload_reliability_universe',
                    'is_enabled_fbupload_unknown');
        }

        return $result;
    }

    /**
     * Get retry context for media upload.
     *
     * @return array
     */
    protected function _getRetryContext()
    {
        return [
            // TODO increment it with every fail.
            'num_step_auto_retry'   => 0,
            'num_reupload'          => 0,
            'num_step_manual_retry' => 0,
        ];
    }

    /**
     * Get params for photo upload job.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @return array
     */
    protected function _getPhotoUploadParams(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        // Common params.
        $result = [
            'upload_id'         => (string) $internalMetadata->getUploadId(),
            'retry_context'     => json_encode($this->_getRetryContext()),
            'image_compression' => '{"lib_name":"moz","lib_version":"3.1.m","quality":"93"}',
            'xsharing_user_ids' => json_encode([]),
            'media_type'        => $internalMetadata->getVideoDetails() !== null
                ? (string) Response\Model\Item::VIDEO
                : (string) Response\Model\Item::PHOTO,
        ];
        // Target feed's specific params.
        switch ($targetFeed) {
            case Constants::FEED_TIMELINE_ALBUM:
                $result['is_sidecar'] = '1';
                break;
            case Constants::FEED_STORY:
                list($hash, $quality) = PDQHasher::computeHashAndQualityFromFilename($internalMetadata->getPhotoDetails()->getFilename(), false, false);
                $result['original_photo_pdq_hash'] = sprintf('%s:%d', $hash->toHexString(), 9);
                break;
            case Constants::FEED_TV:
                if ($internalMetadata->getBroadcastId() !== null) {
                    $result['broadcast_id'] = (string) $internalMetadata->getBroadcastId();
                    $result['is_post_live_igtv'] = '1';
                }
                break;
            case Constants::FEED_REELS:
                if ($internalMetadata->getBroadcastId() !== null) {
                    $result['broadcast_id'] = (string) $internalMetadata->getBroadcastId();
                    $result['is_post_live_clips'] = '1';
                }
                break;
            default:
        }

        return $result;
    }

    /**
     * Get params for video upload job.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @return array
     */
    protected function _getVideoUploadParams(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        $videoDetails = $internalMetadata->getVideoDetails();
        // Common params.
        $result = [
            'upload_id'                => (string) $internalMetadata->getUploadId(),
            'retry_context'            => json_encode($this->_getRetryContext()),
            'xsharing_user_ids'        => json_encode([]),
            'upload_media_height'      => (string) $videoDetails->getHeight(),
            'upload_media_width'       => (string) $videoDetails->getWidth(),
            'upload_media_duration_ms' => (string) $videoDetails->getDurationInMsec(),
            'media_type'               => ($targetFeed === Constants::FEED_DIRECT_AUDIO) ? (string) Constants::FEED_DIRECT_AUDIO : (string) Response\Model\Item::VIDEO,
            'sticker_burnin_params'    => json_encode([]),
        ];
        // Target feed's specific params.
        switch ($targetFeed) {
            case Constants::FEED_TIMELINE_ALBUM:
                $result['is_sidecar'] = '1';
                break;
            case Constants::FEED_DIRECT:
                $result['direct_v2'] = '1';
                $result['rotate'] = '0';
                $result['hflip'] = 'false';
                break;
            case Constants::FEED_STORY:
                //$result['for_album'] = '1';
                //$result['content_tags'] = 'use_default_cover';
                //$result['extract_cover_frame'] = '1';
                $result['upload_engine_config_enum'] = '0';
                $result['IG-FB-Xpost-entry-point-v2'] = 'story';
                break;
            case Constants::FEED_DIRECT_STORY:
                $result['for_direct_story'] = '1';
                break;
            case Constants::FEED_TV:
                $result['is_igtv_video'] = '1';
                break;
            case Constants::FEED_DIRECT_AUDIO:
                $result['is_direct_voice'] = '1';
                break;
            case Constants::FEED_REELS:
                $result['is_clips_video'] = '1';
                $result['content_tags'] = 'use_default_cover';
                break;
            default:
        }

        return $result;
    }

    /**
     * Find the segments after ffmpeg processing.
     *
     * @param string $outputDirectory The directory to look in.
     * @param string $prefix          The filename prefix.
     *
     * @return array
     */
    protected function _findSegments(
        $outputDirectory,
        $prefix)
    {
        // Video segments will be uploaded before the audio one.
        $result = glob("{$outputDirectory}/{$prefix}.video.*.mp4");

        // Audio always goes into one segment, so we can use is_file() here.
        $audioTrack = "{$outputDirectory}/{$prefix}.audio.mp4";
        if (is_file($audioTrack)) {
            $result[] = $audioTrack;
        }

        return $result;
    }

    /**
     * Split the video file into segments.
     *
     * @param int          $targetFeed      One of the FEED_X constants.
     * @param VideoDetails $videoDetails
     * @param FFmpeg|null  $ffmpeg
     * @param string|null  $outputDirectory
     *
     * @throws \Exception
     *
     * @return VideoDetails[]
     */
    protected function _splitVideoIntoSegments(
        $targetFeed,
        VideoDetails $videoDetails,
        FFmpeg $ffmpeg = null,
        $outputDirectory = null)
    {
        if ($ffmpeg === null) {
            $ffmpeg = FFmpeg::factory();
        }
        if ($outputDirectory === null) {
            $outputDirectory = Utils::$defaultTmpPath === null ? sys_get_temp_dir() : Utils::$defaultTmpPath;
        }
        // Check whether the output directory is valid.
        $targetDirectory = realpath($outputDirectory);
        if ($targetDirectory === false || !is_dir($targetDirectory) || !is_writable($targetDirectory)) {
            throw new \RuntimeException(sprintf(
                'Directory "%s" is missing or is not writable.',
                $outputDirectory
            ));
        }

        $prefix = sha1($videoDetails->getFilename().uniqid('', true));

        try {
            // Split the video stream into a multiple segments by time.
            $ffmpeg->run(sprintf(
                '-i %s -c:v copy -an -dn -sn -f segment -segment_time %d -segment_format mp4 %s',
                Args::escape($videoDetails->getFilename()),
                $this->_getTargetSegmentDuration($targetFeed),
                Args::escape(sprintf(
                    '%s%s%s.video.%%03d.mp4',
                    $outputDirectory,
                    DIRECTORY_SEPARATOR,
                    $prefix
                ))
            ));
            if ($videoDetails->getAudioCodec() !== null) {
                // Save the audio stream in one segment.
                $ffmpeg->run(sprintf(
                    '-i %s -c:a copy -vn -dn -sn -f mp4 %s',
                    Args::escape($videoDetails->getFilename()),
                    Args::escape(sprintf(
                        '%s%s%s.audio.mp4',
                        $outputDirectory,
                        DIRECTORY_SEPARATOR,
                        $prefix
                    ))
                ));
            }
        } catch (\RuntimeException $e) {
            // Find and remove all segments (if any).
            $files = $this->_findSegments($outputDirectory, $prefix);
            foreach ($files as $file) {
                @unlink($file);
            }
            // Re-throw the exception.
            throw $e;
        }

        // Collect segments.
        $files = $this->_findSegments($outputDirectory, $prefix);
        if (empty($files)) {
            throw new \RuntimeException('Something went wrong while splitting the video into segments.');
        }
        $result = [];

        try {
            // Wrap them into VideoDetails.
            foreach ($files as $file) {
                $result[] = new VideoDetails($file);
            }
        } catch (\Exception $e) {
            // Cleanup when something went wrong.
            foreach ($files as $file) {
                @unlink($file);
            }

            throw $e;
        }

        return $result;
    }

    /**
     * Get supported capabilities.
     *
     * @return string
     */
    public function getSupportedCapabilities()
    {
        $supportedCapabilities = Constants::SUPPORTED_CAPABILITIES;
        /*
        $segmentation = $this->ig->isExperimentEnabled(
            '34393',
            7);
        $segmentationUnknown = $this->ig->isExperimentEnabled(
            '34393',
            12, false);
        */
        $v = $this->ig->getExperimentParam('31167', 0, 0);

        if ($v >= 30) {
            $supportedCapabilities[] = [
                'name'  => 'segmentation',
                'value' => 'segmentation_enabled',
            ];
        }

        $supportedCapabilities[] = [
            'name'  => 'COMPRESSION',
            'value' => 'ETC2_COMPRESSION',
        ];

        $wordTracker = $this->ig->isExperimentEnabled('32236', 0, true);
        if ($wordTracker) {
            $supportedCapabilities[] = [
                'name'  => 'world_tracker',
                'value' => 'world_tracker_enabled',
            ];
        }

        if ($this->ig->getGyroscopeEnabled()) {
            $supportedCapabilities[] = [
                'name'  => 'gyroscope',
                'value' => 'gyroscope_enabled',
            ];
        }

        return json_encode($supportedCapabilities);
    }

    /**
     * Write capabilities.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function writeSupportedCapabilities()
    {
        return $this->ig->request('creatives/write_supported_capabilities/')
            ->addPost('supported_capabilities_new', $this->getSupportedCapabilities())
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get async ndx IG steps.
     *
     * @param string $source Source.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getAsyncNdxIgSteps(
        $source)
    {
        return $this->ig->request('devices/ndx/api/async_get_ndx_ig_steps/')
            ->addParam('ndx_request_source', $source)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get bloks save credentials screen.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getBloksSaveCredentialsScreen()
    {
        return $this->ig->request('bloks/apps/com.bloks.www.caa.login.save-credentials/')
            ->setSignedPost(false)
            ->addPost('qe_device_id', $this->ig->uuid)
            ->addPost('offline_experiment_group', $this->ig->settings->get('offline_experiment'))
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('family_device_id', $this->ig->phone_id)
            ->addPost('bk_client_context', json_encode([
                'bloks_version' => Constants::BLOCK_VERSIONING_ID,
                'styles_id'     => 'instagram',
            ]))
            ->addPost('bloks_versioning_id', Constants::BLOCK_VERSIONING_ID)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get target segment duration in seconds.
     *
     * @param int $targetFeed One of the FEED_X constants.
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    protected function _getTargetSegmentDuration(
        $targetFeed)
    {
        switch ($targetFeed) {
            case Constants::FEED_DIRECT:
            case Constants::FEED_TIMELINE:
            case Constants::FEED_TIMELINE_ALBUM:
            case Constants::FEED_DIRECT_AUDIO:
                $duration = 10;
                break;
            case Constants::FEED_STORY:
            case Constants::FEED_DIRECT_STORY:
            case Constants::FEED_REELS:
                $duration = 2;
                break;
            case Constants::FEED_TV:
                $duration = 100;
                break;
            default:
                throw new \InvalidArgumentException("Unsupported feed {$targetFeed}.");
        }

        return (int) $duration;
    }

    /**
     * Get quick promotion surface.
     *
     * @param string[] $surfaces Quick Promotion Surface.
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function _getQuickPromotionSurface(
        $surfaces)
    {
        $qps = [
            'queries'    => [],
            'triggers'   => [],
        ];
        foreach ($surfaces as $surface) {
            switch ($surface) {
                case 'MEGAPHONE':
                    $qps['queries'] += ['4715' => $this->_getQuickPromotionSurfaceQueryString(true)];
                    $qps['triggers'] += ['4715' => ['instagram_feed_header', 'instagram_post_created', 'instagram_story_created']];
                    break;
                case 'TOOLTIP':
                    $qps['queries'] += ['5858' => $this->_getQuickPromotionSurfaceQueryString(false)];
                    $qps['triggers'] += ['5858' => ['instagram_feed_tool_tip', 'instagram_navigation_tooltip', 'instagram_featured_product_media_tooltip', 'instagram_feed_promote_cta_tooltip']];
                    break;
                case 'INTERSTITIAL':
                    $qps['queries'] += ['5734' => $this->_getQuickPromotionSurfaceQueryString(true)];
                    $qps['triggers'] += ['5734' => ['instagram_feed_prompt', 'instagram_branded_content_story_shared', 'instagram_shopping_enable_auto_highlight_interstitial', 'instagram_story_created']];
                    break;
                case 'STORIES_TRAY':
                    $qps['queries'] += ['6319' => $this->_getQuickPromotionSurfaceQueryString(false)];
                    break;
                case 'MESSAGE_FOOTER':
                    $qps['queries'] += ['8034' => $this->_getQuickPromotionSurfaceQueryString(false)];
                    break;
                case 'FLOATING_BANNER':
                    $qps['queries'] += ['8972' => $this->_getQuickPromotionSurfaceQueryString(false)];
                    $qps['triggers'] += ['8972' => ['instagram_feed_banner']];
                    break;
                case 'RTC_PEEK':
                    $qps['queries'] += ['9643' => $this->_getQuickPromotionSurfaceQueryString(false)];
                    break;
                case 'TWO_BY_TWO_TILE':
                    $qps['queries'] += ['9775' => $this->_getQuickPromotionSurfaceQueryString(false)];
                    break;
                case 'REELS_MIDCARD':
                    $qps['queries'] += ['10671' => $this->_getQuickPromotionSurfaceQueryString(true)];
                    break;
                case 'BOTTOMSHEET':
                    $qps['queries'] += ['11383' => $this->_getQuickPromotionSurfaceQueryString(false)];
                    $qps['triggers'] += ['11383' => ['instagram_feed_bottomsheet']];
                    break;
                case 'BARCELONA_MEGAPHONE':
                    $qps['queries'] += ['11451' => $this->_getQuickPromotionSurfaceQueryString(true)];
                    break;
                case 'LOGIN_INTERSTITIAL':
                    $qps['queries'] += ['11483' => $this->_getQuickPromotionSurfaceQueryString(false)];
                    $qps['triggers'] += ['11483' => ['app_foreground', 'session_start']];
                    break;
                default:
                    throw new \InvalidArgumentException("Unsupported surface {$surface}.");
            }
        }

        return $qps;
    }

    /**
     * Get quick promotion surface query string.
     *
     * @param bool $darkMode Dark mode.
     *
     * @return string
     */
    protected function _getQuickPromotionSurfaceQueryString(
        $darkMode)
    {
        $query = 'Query QuickPromotionSurfaceQuery: Viewer{viewer(){eligible_promotions.trigger_context_v2(<trigger_context_v2>).ig_parameters(<ig_parameters>).trigger_name(<trigger_name>).surface_nux_id(<surface>).external_gating_permitted_qps(<external_gating_permitted_qps>).supports_client_filters(true).include_holdouts(true){edges{client_ttl_seconds,log_eligibility_waterfall,is_holdout,priority,time_range{start,end},node{id,promotion_id,logging_data,is_server_force_pass,max_impressions,triggers,contextual_filters{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}}}}}},is_uncancelable,template{name,parameters{name,required,bool_value,string_value,color_value}},creatives{title{text},content{text},footer{text},social_context{text},social_context_images,primary_action{title{text},url,limit,dismiss_promotion},secondary_action{title{text},url,limit,dismiss_promotion},dismiss_action{title{text},url,limit,dismiss_promotion},bullet_list{title,subtitle,icon{uri,width,height}}image.scale(<scale>){uri,width,height}';
        if ($darkMode) {
            $query .= ',dark_mode_image.scale(<scale>){uri,width,height}';
        }
        $query .= '}}}}}}';

        return $query;
    }
}
