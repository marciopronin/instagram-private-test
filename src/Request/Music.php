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
     * @return Response\MusicItemsResponse
     */
    public function getTrending(
        $browseSessionId
    ) {
        return $this->ig->request('music/trending/')
            ->setSignedPost(false)
            ->addPost('browse_session_id', $browseSessionId)
            ->addPost('product', 'story_camera_music_overlay_post_capture')
            // ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\MusicItemsResponse());
    }

    /**
     * Search for music clips from Discover/Search module.
     *
     * @param string         $query           Finds locations containing this string.
     * @param string         $browseSessionId The browse session ID (UUIDv4).
     * @param string[]|int[] $excludeList     Array of numerical location IDs (ie "17841562498105353")
     *                                        to exclude from the response, allowing you to skip locations
     *                                        from a previous call to get more results.
     * @param string|null    $rankToken       (When paginating) The rank token from the previous page's response.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\MusicItemsResponse
     *
     * @see MusicItemsResponse::getRankToken() To get a rank token from the response.
     * @see examples/paginateWithExclusion.php For an example.
     */
    public function searchAudio(
        $query,
        $browseSessionId,
        array $excludeList = [],
        $rankToken = null
    ) {
        // Do basic query validation. Do NOT use throwIfInvalidHashtag here.
        if (!is_string($query) || $query === null) {
            throw new \InvalidArgumentException('Query must be a non-empty string.');
        }
        $location = $this->_paginateWithExclusion(
            $this->ig->request('music/audio_global_search/')
                ->addParam('timezone_offset', ($this->ig->getTimezoneOffset() !== null) ? $this->ig->getTimezoneOffset() : date('Z'))
                ->addParam('query', $query)
                ->addParam('search_surface', 'audio_serp_page')
                ->addParam('count', 30)
                ->addParam('browse_session_id', $browseSessionId),
            $excludeList,
            $rankToken
        );

        try {
            /** @var Response\MusicItemsResponse $result */
            $result = $location->getResponse(new Response\MusicItemsResponse());
        } catch (RequestHeadersTooLargeException $e) {
            $result = new Response\MusicItemsResponse([
                'has_more'   => false,
                'items'      => [],
                'rank_token' => $rankToken,
            ]);
        }

        return $result;
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
     * @return Response\MusicItemsResponse
     */
    public function search(
        $query,
        $browseSessionId,
        $searchSessionId
    ) {
        return $this->ig->request('music/search/')
            ->setSignedPost(false)
            ->addPost('q', $query)
            ->addPost('browse_session_id', $browseSessionId)
            ->addPost('search_session_id', $searchSessionId)
            ->addPost('from_typeahead', 'false')
            ->addPost('product', 'story_camera_music_overlay_post_capture')
            // ->addPost('_csrftoken', $this->ig->client->getToken())
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
     * @return Response\MusicKeywordSearchResponse
     */
    public function keywordSearch(
        $query,
        $browseSessionId,
        $searchSessionId,
        $numberOfKeywords = 3
    ) {
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
     * @return Response\GetLyricsResponse
     */
    public function getLyrics(
        $trackId
    ) {
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
     * @return Response\MusicGenresResponse
     */
    public function getGenres(
        $browseSessionId
    ) {
        return $this->ig->request('music/genres/')
            // ->addPost('_csrftoken', $this->ig->client->getToken())
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
     * @return Response\GenericResponse
     */
    public function getMoods(
        $browseSessionId
    ) {
        return $this->ig->request('music/moods/')
            // ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('browse_session_id', $browseSessionId)
            ->addPost('product', 'story_camera_music_overlay_post_capture')
            ->getResponse(new Response\GenericResponse());
    }
}
