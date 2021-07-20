<?php

namespace InstagramAPI\Request;

use InstagramAPI\Response;

/**
 * Functions related to music and lyrics.
 */
class Music extends RequestCollection
{
    /**
     * Get trending music.
     *
     * @param string $browseSessionId The browse session ID (UUIDv4).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MusicItemsResponse
     */
    public function getTrending(
        $browseSessionId)
    {
        return $this->ig->request('music/trending/')
            ->setSignedPost(false)
            ->addPost('browse_session_id', $browseSessionId)
            ->addPost('product', 'story_camera_music_overlay_post_capture')
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\MusicItemsResponse());
    }

    /**
     * Search music.
     *
     * @param string $query           The song to search for.
     * @param string $browseSessionId The browse session ID (UUIDv4).
     * @param string $searchSessionId The search session ID (UUIDv4).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MusicItemsResponse
     */
    public function search(
        $query,
        $browseSessionId,
        $searchSessionId)
    {
        return $this->ig->request('music/search/')
            ->setSignedPost(false)
            ->addPost('q', $query)
            ->addPost('browse_session_id', $browseSessionId)
            ->addPost('search_session_id', $searchSessionId)
            ->addPost('from_typeahead', 'false')
            ->addPost('product', 'story_camera_music_overlay_post_capture')
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\MusicItemsResponse());
    }

    /**
     * Keyword search.
     *
     * Returns a list of keyword to help searching for music.
     *
     * @param string $query            The song to search for.
     * @param string $browseSessionId  The browse session ID (UUIDv4).
     * @param string $searchSessionId  The search session ID (UUIDv4).
     * @param int    $numberOfKeywords Number of keywords returned.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MusicKeywordSearchResponse
     */
    public function keywordSearch(
        $query,
        $browseSessionId,
        $searchSessionId,
        $numberOfKeywords = 3)
    {
        return $this->ig->request('music/keyword_search/')
            ->addParam('num_keywords', 3)
            ->addParam('q', $query)
            ->addParam('browse_session_id', $browseSessionId)
            ->addParam('search_session_id', $searchSessionId)
            ->addParam('product', 'story_camera_music_overlay_post_capture')
            ->getResponse(new Response\MusicKeywordSearchResponse());
    }

    /**
     * Gets the phrases from lyrics of a track.
     *
     * This is used internally by Instagram app to composer videos with
     * lyrics.
     *
     * @param string $trackId The track ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GetLyricsResponse
     */
    public function getLyrics(
        $trackId)
    {
        return $this->ig->request("music/track/{$trackId}/lyrics/")
            ->getResponse(new Response\GetLyricsResponse());
    }

    /**
     * Get music genres.
     *
     * @param string $browseSessionId The browse session ID (UUIDv4).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MusicGenresResponse
     */
    public function getGenres(
        $browseSessionId)
    {
        return $this->ig->request('music/genres/')
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('browse_session_id', $browseSessionId)
            ->addPost('product', 'story_camera_music_overlay_post_capture')
            ->getResponse(new Response\MusicGenresResponse());
    }

    /**
     * Get music moods.
     *
     * @param string $browseSessionId The browse session ID (UUIDv4).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getMoods(
        $browseSessionId)
    {
        return $this->ig->request('music/moods/')
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('browse_session_id', $browseSessionId)
            ->addPost('product', 'story_camera_music_overlay_post_capture')
            ->getResponse(new Response\GenericResponse());
    }
}
