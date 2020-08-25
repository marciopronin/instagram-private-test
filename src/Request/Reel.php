<?php

namespace InstagramAPI\Request;

use InstagramAPI\Response;

/**
 * Functions related to Reels also known internally as clips.
 */
class Reel extends RequestCollection
{
    /**
     * Home reels.
     *
     * @param string|null $maxId  Next "maximum ID", used for pagination.
     * @param string      $module (optional) From which app module (page) you're accessed home reels.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserReelResponse
     */
    public function getHome(
        $maxId = null,
        $module = 'clips_tab')
    {
        $request = $this->ig->request('clips/home/')
            ->setSignedPost(false)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('container_module', $module);

        if ($maxId !== null) {
            $request->addPost('max_id', $maxId);
        }

        return $request->getResponse(new Response\UserReelsResponse());
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
     * @return \InstagramAPI\Response\UserReelResponse
     */
    public function getUserReels(
        $userId,
        $maxId = null)
    {
        $request = $this->ig->request('clips/user/')
            ->setSignedPost(false)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('target_user_id', $userId);

        if ($maxId !== null) {
            $request->addPost('max_id', $maxId);
        }

        return $request->getResponse(new Response\UserReelsResponse());
    }

    /**
     * Get music for reels. NOT FINISHED.
     *
     * @param string|null $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserReelResponse
     */
    public function getMusic(
        $maxId = null)
    {
        $request = $this->ig->request('clips/music/')
            ->setSignedPost(false)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid);

        if ($maxId !== null) {
            $request->addPost('max_id', $maxId);
        }

        return $request->getResponse(new Response\UserReelsResponse());
    }
}
