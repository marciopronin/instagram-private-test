<?php

namespace InstagramAPI\Request;

use InstagramAPI\Constants;
use InstagramAPI\Response;

/**
 * Functions related to Reels also known internally as clips.
 */
class Reel extends RequestCollection
{
    /**
     * Uploads a video to Reels (clips). EXPERIMENTAL.
     *
     * @param string $videoFilename    The video filename.
     * @param array  $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
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
        return $this->ig->internal->uploadSingleVideo(Constants::FEED_REELS, $videoFilename, null, $externalMetadata);
    }

    /**
     * Discover reels.
     *
     * @param string|null $chainingMedia Chaining media ID (Parent).
     * @param array|null  $seenReels     Seen reels.
     * @param array|null  $sessionInfo   Session info
     * @param array|null  $maxId         Max ID.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsResponse
     */
    public function discover(
        $chainingMedia = null,
        $seenReels = null,
        $sessionInfo = null,
        $maxId = null)
    {
        $request = $this->ig->request('clips/discover/')
            ->setSignedPost(false)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('server_driven_cache_config', json_encode(
                [
                    'serve_from_server_cache'       => true,
                    'cohort_to_ttl_map'             => '',
                    'serve_on_foreground_prefetch'  => 'true',
                    'serve_on_background_prefetch'  => 'true',
                    'meta'                          => '',
                ]
            ));
        if ($chainingMedia !== null) {
            $request->addPost('chaining_media_id', $chainingMedia);
        }
        if ($seenReels !== null) {
            $request->addPost('seen_reels', json_encode($seenReels, true));
        }
        if ($sessionInfo !== null) {
            $request->addPost('session_info', json_encode($sessionInfo, true));
        }
        if ($maxId !== null) {
            $request->addPost('max_id', $maxId);
        }

        if (($chainingMedia !== null) || ($seenReels !== null) || ($sessionInfo !== null)) {
            $request->addPost('container_module', 'clips_viewer_explore_popular_major_unit');
        } else {
            $request->addPost('container_module', 'clips_viewer_clips_tab');
        }

        return $request->getResponse(new Response\ReelsResponse());
    }

    /**
     * Discover stream reels.
     *
     * @param array|null $seenReels   Seen reels.
     * @param array|null $sessionInfo Session info
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsResponse
     */
    public function discoverStream(
        $seenReels = null,
        $sessionInfo = null)
    {
        $request = $this->ig->request('clips/discover/stream/')
            ->setSignedPost(false)
            ->addPost('server_driven_cache_config', json_encode([
                'serve_from_server_cache'       => true,
                'cohort_to_ttl_map'             => '',
                'serve_on_foreground_prefetch'  => 'true',
                'serve_on_background_prefetch'  => 'true',
                'meta'                          => '',
            ]))
            ->addPost('container_module', 'clips_viewer_clips_tab');

        if ($seenReels !== null) {
            $request->addPost('seen_reels', json_encode($seenReels, true));
        }
        if ($sessionInfo !== null) {
            $request->addPost('session_info', json_encode($sessionInfo, true));
        }

        return $request->getResponse(new Response\ReelsResponse());
    }

    /**
     * Send seen state.
     *
     * @param string[] $mediaIds Media IDs in PK format.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function sendSeenState(
        array $mediaIds = null)
    {
        $request = $this->ig->request('clips/write_seen_state/')
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('impressions', $mediaIds);

        return $request->getResponse(new Response\GenericResponse());
    }

    /**
     * Home reels.
     *
     * @param string|null $maxId  Next "maximum ID", used for pagination.
     * @param string      $module (optional) From which app module (page) you're accessed home reels.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsResponse
     */
    public function getHome(
        $maxId = null,
        $module = 'clips_tab')
    {
        $request = $this->ig->request('clips/home/')
            ->setSignedPost(false)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('container_module', $module);

        if ($maxId !== null) {
            $request->addPost('max_id', $maxId);
        }

        return $request->getResponse(new Response\ReelsResponse());
    }

    /**
     * Get user reels.
     *
     * @param string      $userId Numerical UserPK ID.
     * @param string|null $maxId  Next "maximum ID", used for pagination.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsResponse
     */
    public function getUserReels(
        $userId,
        $maxId = null)
    {
        $request = $this->ig->request('clips/user/')
            ->setSignedPost(false)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('target_user_id', $userId);

        if ($maxId !== null) {
            $request->addPost('max_id', $maxId);
        }

        return $request->getResponse(new Response\ReelsResponse());
    }

    /**
     * Get hashtag reels.
     *
     * @param string      $hashtag Hashtag.
     * @param string|null $maxId   Next "maximum ID", used for pagination.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsResponse
     */
    public function getHashtagReels(
        $hashtag,
        $maxId = null)
    {
        $request = $this->ig->request("clips/tags/{$hashtag}")
            ->setSignedPost(false)
            ->addPost('_uuid', $this->ig->uuid);

        if ($maxId !== null) {
            $request->addPost('max_id', $maxId);
        }

        return $request->getResponse(new Response\ReelsResponse());
    }

    /**
     * Get music for reels.
     *
     * @param string|null $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsResponse
     */
    public function getMusic(
        $maxId = null)
    {
        $request = $this->ig->request('clips/music/')
            ->setSignedPost(false)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid);

        if ($maxId !== null) {
            $request->addPost('max_id', $maxId);
        }

        return $request->getResponse(new Response\ReelsResponse());
    }

    /**
     * Get reels from a location ID.
     *
     * @param string      $locationId Numerical UserPK ID.
     * @param string|null $maxId      Next   "maximum ID", used for pagination.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsResponse
     */
    public function getLocationReels(
        $locationId,
        $maxId = null)
    {
        $request = $this->ig->request("clips/location/{$locationId}/")
            ->setSignedPost(false)
            ->addPost('_uuid', $this->ig->uuid);

        if ($maxId !== null) {
            $request->addPost('max_id', $maxId);
        }

        return $request->getResponse(new Response\ReelsResponse());
    }

    /**
     * Get liked reels of a user.
     *
     * @param string      $userId Numerical UserPK ID.
     * @param string|null $maxId  Next   "maximum ID", used for pagination.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsResponse
     */
    public function getUserLikedReels(
        $userId,
        $maxId = null)
    {
        $request = $this->ig->request('clips/liked/')
            ->setSignedPost(false)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('target_user_id', $userId);

        if ($maxId !== null) {
            $request->addPost('max_id', $maxId);
        }

        return $request->getResponse(new Response\ReelsResponse());
    }

    /**
     * Get share to FB config.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelShareToFbConfigResponse
     */
    public function getShareToFbConfig()
    {
        return $this->ig->request('clips/user/share_to_fb_config/')
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\ReelShareToFbConfigResponse());
    }

    /**
     * Set default share to FB config.
     *
     * @param bool $enabled     Enable default share to FB.
     * @param int  $sharingMode Sharing mode.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function setDefaultShareToFbConfig(
        $enabled,
        $sharingMode = 2)
    {
        return $this->ig->request('clips/user/set_default_share_to_fb_enabled/')
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('default_share_to_fb_enabled', ($enabled === true) ? 'true' : 'false')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('enable_oa_reuse_on_fb', 'true')
            ->addPost('sharing_mode', $sharingMode)
            ->addPost('container_module', 'ShareOnFacebookSettingsFragment')
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Allow/Disallow remixes of uploaded reel. It is enabled by default on uploaded medias.
     *
     * @param string $mediaId Media ID.
     * @param bool   $enabled Enable default share to FB.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function allowRemixes(
        $mediaId,
        $enabled)
    {
        return $this->ig->request('clips/item/set_mashups_allowed/')
            ->setSignedPost(false)
            ->addPost('media_id', $mediaId)
            ->addPost('clips_media_id', $mediaId)
            ->addPost('mashups_allowed', ($enabled === true) ? 'true' : 'false')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('container_module', 'clips_viewer_clips_tab')
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * It removes the reel only from the profile grid, reel is still visible at the reels section.
     *
     * @param string $mediaId Media ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function removeFromProfile(
        $mediaId)
    {
        return $this->ig->request('feed/profile_grid/remove/')
            ->setSignedPost(false)
            ->addPost('media_id', $mediaId)
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get clips info for creation.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getClipsInfoForCreation()
    {
        return $this->ig->request('clips/clips_info_for_creation/')
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Check offensive text.
     *
     * @param string $text Text to check.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function checkOffensiveText(
        $text = '')
    {
        return $this->ig->request('warning/check_offensive_text/')
            ->addPost('text', $text)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('request_type', 'caption')
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get videos chaining.
     *
     * @param string|null $chainingMedia Chaining media ID (Parent).
     * @param array|null  $seenReels     Seen reels.
     * @param array|null  $sessionInfo   Session info
     * @param array|null  $maxId         Max ID.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsResponse
     */
    public function getVideosChaining(
        $chainingMedia = null,
        $seenReels = null,
        $sessionInfo = null,
        $maxId = null)
    {
        $request = $this->ig->request('clips/panavideochaining/')
            ->setSignedPost(false)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid);

        if ($chainingMedia !== null) {
            $request->addPost('chaining_media_id', $chainingMedia);
        }
        if ($seenReels !== null) {
            $request->addPost('seen_reels', json_encode($seenReels, true));
        }
        if ($sessionInfo !== null) {
            $request->addPost('session_info', json_encode($sessionInfo, true));
        }
        if ($maxId !== null) {
            $request->addPost('max_id', $maxId);
        }

        return $request->getResponse(new Response\ReelsResponse());
    }
}
