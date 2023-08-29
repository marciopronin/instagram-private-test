<?php

namespace InstagramAPI\Request\Metadata;

use InstagramAPI\Constants;
use InstagramAPI\Media\Constraints\ConstraintsFactory;
use InstagramAPI\Media\Photo\PhotoDetails;
use InstagramAPI\Media\Video\VideoDetails;
use InstagramAPI\Response\Model\VideoUploadUrl;
use InstagramAPI\Response\UploadJobVideoResponse;
use InstagramAPI\Response\UploadPhotoResponse;
use InstagramAPI\Response\UploadVideoResponse;
use InstagramAPI\Utils;

final class Internal
{
    /** @var PhotoDetails */
    private $_photoDetails;

    /** @var VideoDetails */
    private $_videoDetails;

    /** @var string */
    private $_uploadId;

    /** @var string */
    private $_fbAttachmentId;

    /** @var VideoUploadUrl[] */
    private $_videoUploadUrls;

    /** @var UploadVideoResponse */
    private $_videoUploadResponse;

    /** @var UploadPhotoResponse */
    private $_photoUploadResponse;

    /** @var string */
    private $_directThreads;

    /** @var string */
    private $_directUsers;

    /** @var bool */
    private $_bestieMedia;

    /** @var string */
    private $_storyViewMode;

    /** @var string */
    private $_waterfallId;

    /** @var bool */
    private $_isCarousel = false;

    /** @var string */
    private $_broadcastId;

    /**
     * Constructor.
     *
     * @param string|null $uploadId
     */
    public function __construct(
        $uploadId = null)
    {
        if ($uploadId !== null) {
            $this->_uploadId = $uploadId;
        } else {
            $this->_uploadId = Utils::generateUploadId();
        }
        $this->_bestieMedia = false;
    }

    /**
     * @return PhotoDetails
     */
    public function getPhotoDetails()
    {
        return $this->_photoDetails;
    }

    /**
     * @return VideoDetails
     */
    public function getVideoDetails()
    {
        return $this->_videoDetails;
    }

    /**
     * Set video details from the given filename.
     *
     * @param int    $targetFeed    One of the FEED_X constants.
     * @param string $videoFilename
     *
     * @throws \InvalidArgumentException If the video file is missing or invalid, or Instagram won't allow this video.
     * @throws \RuntimeException         In case of various processing errors.
     *
     * @return VideoDetails
     */
    public function setVideoDetails(
        $targetFeed,
        $videoFilename)
    {
        // Figure out the video file details.
        // NOTE: We do this first, since it validates whether the video file is
        // valid and lets us avoid wasting time uploading totally invalid files!
        $this->_videoDetails = new VideoDetails($videoFilename);

        // Validate the video details and throw if Instagram won't allow it.
        $this->_videoDetails->validate(ConstraintsFactory::createFor($targetFeed));

        return $this->_videoDetails;
    }

    /**
     * Set photo details from the given filename.
     *
     * @param int    $targetFeed    One of the FEED_X constants.
     * @param string $photoFilename
     *
     * @throws \InvalidArgumentException If the photo file is missing or invalid, or Instagram won't allow this photo.
     * @throws \RuntimeException         In case of various processing errors.
     *
     * @return PhotoDetails
     */
    public function setPhotoDetails(
        $targetFeed,
        $photoFilename)
    {
        // Figure out the photo file details.
        // NOTE: We do this first, since it validates whether the photo file is
        // valid and lets us avoid wasting time uploading totally invalid files!
        $this->_photoDetails = new PhotoDetails($photoFilename);

        // Validate the photo details and throw if Instagram won't allow it.
        $this->_photoDetails->validate(ConstraintsFactory::createFor($targetFeed), $targetFeed);

        return $this->_photoDetails;
    }

    /**
     * @return string
     */
    public function getUploadId()
    {
        return $this->_uploadId;
    }

    /**
     * Sets upload id.
     *
     * @param string $uploadId
     */
    public function setUploadId(
        $uploadId)
    {
        $this->_uploadId = $uploadId;
    }

    /**
     * @return string
     */
    public function getFbAttachmentId()
    {
        return $this->_fbAttachmentId;
    }

    /**
     * Sets FB Attachment id.
     *
     * @param string $fbAttachmentId
     */
    public function setFbAttachmentId(
        $fbAttachmentId)
    {
        $this->_fbAttachmentId = $fbAttachmentId;
    }

    /**
     * Set upload URLs from a UploadJobVideoResponse response.
     *
     * @param UploadJobVideoResponse $response
     *
     * @return VideoUploadUrl[]
     */
    public function setVideoUploadUrls(
        UploadJobVideoResponse $response)
    {
        $this->_videoUploadUrls = [];
        if ($response->getVideoUploadUrls() !== null) {
            $this->_videoUploadUrls = $response->getVideoUploadUrls();
        }

        return $this->_videoUploadUrls;
    }

    /**
     * @return VideoUploadUrl[]
     */
    public function getVideoUploadUrls()
    {
        return $this->_videoUploadUrls;
    }

    /**
     * @return UploadVideoResponse
     */
    public function getVideoUploadResponse()
    {
        return $this->_videoUploadResponse;
    }

    /**
     * @param UploadVideoResponse $videoUploadResponse
     */
    public function setVideoUploadResponse(
        UploadVideoResponse $videoUploadResponse)
    {
        $this->_videoUploadResponse = $videoUploadResponse;
    }

    /**
     * @return UploadPhotoResponse
     */
    public function getPhotoUploadResponse()
    {
        return $this->_photoUploadResponse;
    }

    /**
     * @param UploadPhotoResponse $photoUploadResponse
     */
    public function setPhotoUploadResponse(
        UploadPhotoResponse $photoUploadResponse)
    {
        $this->_photoUploadResponse = $photoUploadResponse;
    }

    /**
     * Add Direct recipients to metadata.
     *
     * @param array $recipients
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public function setDirectRecipients(
        array $recipients)
    {
        if (isset($recipients['users'])) {
            $this->_directUsers = $recipients['users'];
            $this->_directThreads = '[]';
        } elseif (isset($recipients['thread'])) {
            $this->_directUsers = '[]';
            $this->_directThreads = $recipients['thread'];
        } else {
            throw new \InvalidArgumentException('Please provide at least one recipient.');
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDirectThreads()
    {
        return $this->_directThreads;
    }

    /**
     * @return string
     */
    public function getDirectUsers()
    {
        return $this->_directUsers;
    }

    /**
     * Set bestie media state.
     *
     * @param bool $bestieMedia
     */
    public function setBestieMedia(
        $bestieMedia)
    {
        $this->_bestieMedia = $bestieMedia;
    }

    /**
     * @return bool
     */
    public function isBestieMedia()
    {
        return $this->_bestieMedia;
    }

    /**
     * Set story view mode.
     *
     * @param string $viewMode View mode. Use STORY_VIEW_MODE_ONCE and STORY_VIEW_MODE_REPLAYABLE constants as values.
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function setStoryViewMode(
        $viewMode)
    {
        if ($viewMode !== Constants::STORY_VIEW_MODE_ONCE
            && $viewMode !== Constants::STORY_VIEW_MODE_REPLAYABLE
            && $viewMode != Constants::STORY_VIEW_MODE_PERMANENT
        ) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown view mode: %s',
                $viewMode
            ));
        }
        $this->_storyViewMode = $viewMode;

        return $this->_storyViewMode;
    }

    /**
     * Get story view mode.
     *
     * @return string
     */
    public function getStoryViewMode()
    {
        return $this->_storyViewMode;
    }

    /**
     * Set waterfall ID.
     *
     * @param string $waterfallId
     */
    public function setWaterfallID(
        $waterfallId)
    {
        $this->_waterfallId = $waterfallId;
    }

    /**
     * Get waterfall ID.
     *
     * @return string
     */
    public function getWaterfallID()
    {
        return $this->_waterfallId;
    }

    /**
     * Set isCarousel.
     *
     * @param bool $bool
     */
    public function setIsCarousel(
        $bool)
    {
        $this->_isCarousel = $bool;
    }

    /**
     * Get isCarousel.
     *
     * @return bool
     */
    public function getIsCarousel()
    {
        return $this->_isCarousel;
    }

    /**
     * Set broadcast ID.
     *
     * @param string $broadcastId
     */
    public function setBroadcastId(
        $broadcastId)
    {
        $this->_broadcastId = $broadcastId;
    }

    /**
     * Get broadcastId ID.
     *
     * @return string
     */
    public function getBroadcastId()
    {
        return $this->_broadcastId;
    }
}
