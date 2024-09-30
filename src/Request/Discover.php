<?php

namespace InstagramAPI\Request;

use InstagramAPI\Exception\RequestHeadersTooLargeException;
use InstagramAPI\Response;

/**
 * General content discovery functions which don't fit into any better groups.
 */
class Discover extends RequestCollection
{
    /**
     * Get Explore tab feed.
     *
     * @param string|null $clusterId       The cluster ID. Default page: 'explore_all:0', Animals: 'hashtag_inspired:1',
     *                                     Style: 'hashtag_inspired:26', Comics: 'hashtag_inspired:20', Travel: 'hashtag_inspired:28',
     *                                     Architecture: 'hashtag_inspired:18', Beauty: 'hashtag_inspired:3', DIY: 'hashtag_inspired:21',
     *                                     Auto: 'hashtag_inspired:19', Music: 'hashtag_inspired:11', Nature: 'hashtag_inspired:24',
     *                                     Decor: 'hashtag_inspired:5', Dance: 'hashtag_inspired:22'.
     * @param string      $sessionId       Session ID. UUIDv4.
     * @param string|null $maxId           Next "maximum ID", used for pagination.
     * @param bool        $isPrefetch      Whether this is the first fetch; we'll ignore maxId if TRUE.
     * @param bool        $clusterDisabled Whether this is the first fetch; we'll ignore maxId if TRUE.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\ExploreResponse
     */
    public function getExploreFeed(
        $clusterId,
        $sessionId,
        $maxId = null,
        $isPrefetch = false,
        $clusterDisabled = true,
    ) {
        $request = $this->ig->request('discover/topical_explore/')
            ->addHeader('X-IG-Prefetch-Request', 'foreground')
            ->addParam('is_prefetch', $isPrefetch)
            // ->addParam('omit_cover_media', true)
            ->addParam('is_ptr', 'false')
            ->addParam('reels_configuration', $this->ig->getExperimentParam('25215', 13) === null ? 'hide_hero' : 'default')
            ->addParam('is_nonpersonalized_explore', 'false')
            ->addParam('timezone_offset', ($this->ig->getTimezoneOffset() !== null) ? $this->ig->getTimezoneOffset() : date('Z'))
            ->addParam('session_id', $sessionId);
        // ->addParam('paging_token', json_encode((Object)[]));

        if ($this->ig->isExperimentEnabled('48862', 7, true)) {
            $request->addHeader('X-Google-AD-ID', $this->ig->advertising_id)
                    ->addHeader('X-CM-Bandwidth-KBPS', '-1.000')
                    ->addHeader('X-CM-Latency', $this->ig->client->latency)
                    ->addHeader('X-Ads-Opt-Out', '0')
                    ->addHeader('X-DEVICE-ID', $this->ig->uuid)
                    ->addParam('phone_id', $this->ig->phone_id)
                    ->addParam('is_charging', $this->ig->getIsDeviceCharging())
                    ->addParam('battery_level', $this->ig->getBatteryLevel())
                    ->addParam('will_sound_on', (int) $this->ig->getSoundEnabled())
                    ->addParam('is_dark_mode', (int) $this->ig->getIsDarkModeEnabled());
        }

        if ($clusterDisabled === false) {
            $request->addParam('cluster_id', $clusterId);
        }

        if (!$isPrefetch) {
            if ($maxId !== null) {
                $request->addParam('max_id', $maxId);
            }
            // ->addParam('module', 'explore_popular')
            // $request->addParam('is_charging', $this->ig->getIsDeviceCharging())
            //       ->addParam('will_sound_on', (int) $this->ig->getSoundEnabled())
            //        ->addParam('is_dark_mode', (int) $this->ig->getIsDarkModeEnabled());
            // ->addParam('panavision_mode', ''); // $this->ig->isExperimentEnabled('ig_android_panavision_consumption_launcher', 'is_immersive_enabled', ''));
        }

        return $request->getResponse(new Response\ExploreResponse());
    }

    /**
     * Get mixed media.
     *
     * @param string $containerModule Container module.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\DiscoverChainingFeedResponse
     */
    public function getMixedMedia(
        $containerModule = 'clips_viewer_clips_tab',
    ) {
        $request = $this->ig->request('mixed_media/discover/')
            ->setSignedPost(false)
            ->addPost('seen_reels', json_encode([]))
            ->addPost('use_mmd_service', 'true')
            ->addPost('should_refetch_chaining_media', 'false')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost(
                'mixed_media_types',
                json_encode(
                    [
                        'carousel_with_photo_in_first_position' => true,
                        'carousel_with_video_in_first_position' => true,
                        'carousel_with_music'                   => true,
                        'photo_without_music'                   => true,
                        'photo_with_music'                      => true,
                    ]
                )
            )
            ->addPost(
                'server_driven_cache_config',
                json_encode(
                    [
                        'serve_from_server_cache'       => true,
                        'cohort_to_ttl_map'             => '',
                        'serve_on_foreground_prefetch'  => 'true',
                        'serve_on_background_prefetch'  => 'true',
                        'meta'                          => '',
                    ]
                )
            )
            ->addPost('container_module', $containerModule)
            ->getResponse(new Response\DiscoverChainingFeedResponse());
    }

    /**
     * Explore reels.
     *
     * @param string|null $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\ReelsResponse
     */
    public function getExploreReels(
        $maxId = null,
    ) {
        $request = $this->ig->request('discover/explore_clips/')
            ->setSignedPost(false)
            // ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid);

        if ($maxId !== null) {
            $request->addPost('max_id', $maxId);
        }

        return $request->getResponse(new Response\ReelsResponse());
    }

    /**
     * Get discover chaining feed.
     *
     * @param \Response\Model\Item $mediaItem       The Media Item from `getExploreFeed()`.
     * @param string               $chainingSession The chaining feed UUID. You must use the same value for all pages of the feed.
     * @param string|null          $clusterId       The cluster ID. Default page: 'explore_all:0', Animals: 'hashtag_inspired:1',
     *                                              Style: 'hashtag_inspired:26', Comics: 'hashtag_inspired:20', Travel: 'hashtag_inspired:28',
     *                                              Architecture: 'hashtag_inspired:18', Beauty: 'hashtag_inspired:3', DIY: 'hashtag_inspired:21',
     *                                              Auto: 'hashtag_inspired:19', Music: 'hashtag_inspired:11', Nature: 'hashtag_inspired:24',
     *                                              Decor: 'hashtag_inspired:5', Dance: 'hashtag_inspired:22'.
     * @param string|null          $maxId           Next "maximum ID", used for pagination.
     * @param int                  $index           Paging token index.
     * @param string               $surface         Surface.
     * @param array|null           $options         An associative array with following keys (all
     *                                              of them are optional):
     *                                              "is_charging" Wether the device is being charged
     *                                              or not. Valid values: 0 for not charging, 1 for
     *                                              charging.
     *                                              "battery_level" Sets the current device battery
     *                                              level.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\DiscoverChainingFeedResponse
     *
     * @see Signatures::generateUUID() To create the chaining session.
     */
    public function getChainingFeed(
        $mediaItem,
        $chainingSession,
        $clusterId = 'explore_all:0',
        $maxId = null,
        $index = 0,
        $surface = 'explore_media_grid',
        ?array $options = null,
    ) {
        $pagingToken = [
            'last_organic_item' => [
                'id'    => $mediaItem->getId(),
                'index' => $index, // TODO: Needs research.
            ],
        ];

        $request = $this->ig->request('discover/chaining_experience_feed/')
            ->setSignedPost(false)
            ->addPost('_uuid', $this->ig->uuid)
            // ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('surface', $surface)
            ->addPost('explore_source_token', $mediaItem->getExploreSourceToken())
            ->addPost('trigger', 'tap')
            ->addPost('media_id', $mediaItem->getId())
            ->addPost('entry_point', 'topical_explore')
            ->addPost('chaining_session_id', $chainingSession)
            ->addPost('cluster_id', $clusterId)
            ->addHeader('will_sound_on', (int) $this->ig->getSoundEnabled())
            ->addPost('author_id', $mediaItem->getUser()->getPk())
            ->addPost('media_type', $mediaItem->getMediaType())
            ->addPost('paging_token', json_encode($pagingToken))
            ->addHeader('battery_level', $this->ig->getBatteryLevel())
            ->addHeader('is_charging', $this->ig->getIsDeviceCharging());

        if ($maxId !== null) {
            $request->addPost('max_id', $maxId);
        }

        return $request->getResponse(new Response\DiscoverChainingFeedResponse());
    }

    /**
     * Report media in the Explore-feed.
     *
     * @param string $exploreSourceToken Token related to the Explore media.
     * @param string $userId             Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\ReportExploreMediaResponse
     */
    public function reportExploreMedia(
        $exploreSourceToken,
        $userId,
    ) {
        return $this->ig->request('discover/explore_report/')
            ->addParam('explore_source_token', $exploreSourceToken)
            ->addParam('m_pk', $this->ig->account_id)
            ->addParam('a_pk', $userId)
            ->getResponse(new Response\ReportExploreMediaResponse());
    }

    /**
     * Search for Instagram users, hashtags and places via Facebook's algorithm.
     *
     * This performs a combined search for "top results" in all 3 areas at once.
     *
     * @param string      $query       The username/full name, hashtag or location to search for.
     * @param string      $latitude    (optional) Latitude.
     * @param string      $longitude   (optional) Longitude.
     * @param array       $excludeList Array of grouped numerical entity IDs (ie "users" => ["4021088339"])
     *                                 to exclude from the response, allowing you to skip entities
     *                                 from a previous call to get more results. The following entities are supported:
     *                                 "users", "places", "tags".
     * @param string|null $rankToken   (When paginating) The rank token from the previous page's response.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\FBSearchResponse
     *
     * @see FBSearchResponse::getRankToken() To get a rank token from the response.
     * @see examples/paginateWithExclusion.php For a rank token example (but with a different type of exclude list).
     */
    public function search(
        $query,
        $latitude = null,
        $longitude = null,
        array $excludeList = [],
        $rankToken = null,
    ) {
        // Do basic query validation.
        if (!is_string($query) || $query === '') {
            throw new \InvalidArgumentException('Query must be a non-empty string.');
        }
        $request = $this->_paginateWithMultiExclusion(
            $this->ig->request('fbsearch/ig_typeahead/')
                ->addParam('search_surface', 'typeahead_search_page')
                ->addParam('timezone_offset', ($this->ig->getTimezoneOffset() !== null) ? $this->ig->getTimezoneOffset() : date('Z'))
                ->addParam('count', 30) // hardcoded
                ->addParam('query', $query)
                ->addParam('context', 'blended'),
            $excludeList,
            $rankToken
        );

        if ($latitude !== null && $longitude !== null) {
            $request
                ->addParam('lat', $latitude)
                ->addParam('lng', $longitude);
        }

        try {
            /** @var Response\FBSearchResponse $result */
            $result = $request->getResponse(new Response\FBSearchResponse());
        } catch (RequestHeadersTooLargeException $e) {
            $result = new Response\FBSearchResponse([
                'has_more'   => false,
                'hashtags'   => [],
                'users'      => [],
                'places'     => [],
                'rank_token' => $rankToken,
            ]);
        }

        return $result;
    }

    /**
     * Search for TOP media.
     *
     * @param string      $query        The username/full name, hashtag or location to search for.
     * @param string      $latitude     (optional) Latitude.
     * @param string      $longitude    (optional) Longitude.
     * @param array       $excludeList  Array of grouped numerical entity IDs (ie "users" => ["4021088339"])
     *                                  to exclude from the response, allowing you to skip entities
     *                                  from a previous call to get more results. The following entities are supported:
     *                                  "users", "places", "tags".
     * @param string|null $rankToken    (When paginating) The rank token from the previous page's response.
     * @param mixed       $hasMoreReels
     * @param mixed|null  $reelsMaxId
     * @param mixed|null  $pageIndex
     * @param mixed|null  $pageToken
     * @param mixed|null  $pagingToken
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\TopSearchResponse
     *
     * @see TopSearchResponse::getRankToken() To get a rank token from the response.
     * @see examples/paginateWithExclusion.php For a rank token example (but with a different type of exclude list).
     */
    public function topSearch(
        $query,
        $latitude = null,
        $longitude = null,
        $rankToken = null,
        $hasMoreReels = false,
        $reelsMaxId = null,
        $pageIndex = null,
        $pageToken = null,
        $pagingToken = null,
    ) {
        // Do basic query validation.
        if (!is_string($query) || $query === '') {
            throw new \InvalidArgumentException('Query must be a non-empty string.');
        }
        $request = $this->ig->request('fbsearch/top_serp/')
                ->addParam('search_surface', 'top_serp')
                ->addParam('timezone_offset', ($this->ig->getTimezoneOffset() !== null) ? $this->ig->getTimezoneOffset() : date('Z'))
                ->addParam('count', 30)
                ->addParam('query', $query);

        if ($hasMoreReels) {
            $request->addParam('has_more_reels', 'true');
        }
        if ($reelsMaxId !== null) {
            $request->addParam('reels_max_id', $reelsMaxId);
        }
        if ($pageIndex !== null) {
            $request->addParam('page_index', $pageIndex);
        }
        if ($pageToken !== null) {
            $request->addParam('page_token', $pageToken);
        }
        if ($pagingToken !== null) {
            $request->addParam('paging_token', $pagingToken);
        }

        if ($latitude !== null && $longitude !== null) {
            $request
                ->addParam('lat', $latitude)
                ->addParam('lng', $longitude);
        }

        try {
            /** @var Response\TopSearchResponse $result */
            $result = $request->getResponse(new Response\TopSearchResponse());
        } catch (RequestHeadersTooLargeException $e) {
            $result = new Response\TopSearchResponse([
                'has_more'   => false,
                'hashtags'   => [],
                'users'      => [],
                'places'     => [],
                'rank_token' => $rankToken,
            ]);
        }

        return $result;
    }

    /**
     * Search results that are not profiled.
     *
     * @param string      $query       The username/full name, hashtag or location to search for.
     * @param string      $latitude    (optional) Latitude.
     * @param string      $longitude   (optional) Longitude.
     * @param array       $excludeList Array of grouped numerical entity IDs (ie "users" => ["4021088339"])
     *                                 to exclude from the response, allowing you to skip entities
     *                                 from a previous call to get more results. The following entities are supported:
     *                                 "users", "places", "tags".
     * @param string|null $rankToken   (When paginating) The rank token from the previous page's response.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\TopSearchResponse
     *
     * @see TopSearchResponse::getRankToken() To get a rank token from the response.
     * @see examples/paginateWithExclusion.php For a rank token example (but with a different type of exclude list).
     */
    public function nonProfiledSearch(
        $query,
        $latitude = null,
        $longitude = null,
        array $excludeList = [],
        $rankToken = null,
    ) {
        // Do basic query validation.
        if (!is_string($query) || $query === '') {
            throw new \InvalidArgumentException('Query must be a non-empty string.');
        }
        $request = $this->_paginateWithMultiExclusion(
            $this->ig->request('fbsearch/non_profiled_serp/')
                ->addParam('search_surface', 'popular_serp')
                ->addParam('timezone_offset', ($this->ig->getTimezoneOffset() !== null) ? $this->ig->getTimezoneOffset() : date('Z'))
                ->addParam('count', 30)
                ->addParam('query', $query),
            $excludeList,
            $rankToken
        );

        if ($latitude !== null && $longitude !== null) {
            $request
                ->addParam('lat', $latitude)
                ->addParam('lng', $longitude);
        }

        try {
            /** @var Response\TopSearchResponse $result */
            $result = $request->getResponse(new Response\TopSearchResponse());
        } catch (RequestHeadersTooLargeException $e) {
            $result = new Response\TopSearchResponse([
                'has_more'   => false,
                'hashtags'   => [],
                'users'      => [],
                'places'     => [],
                'rank_token' => $rankToken,
            ]);
        }

        return $result;
    }

    /**
     * Register recent search click.
     *
     * @param string $entityType One of: "hashtag", "place" or "user".
     * @param string $entityId   Entity ID. Example: '1578458962261026'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\GenericResponse
     */
    public function registerRecentSearchClick(
        $entityType,
        $entityId,
    ) {
        if (!in_array($entityType, ['user', 'hashtag', 'place'], true)) {
            throw new \InvalidArgumentException(sprintf('Unknown entity type: %s.', $entityType));
        }

        return $this->ig->request('fbsearch/register_recent_search_click/')
            ->setSignedPost(false)
            ->addPost('entity_type', $entityType)
            ->addPost('entity_id', $entityId)
            // ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get search suggestions via Facebook's algorithm.
     *
     * WARNING: THIS FUNCTION IS NOT USED ANYMORE!
     *
     * NOTE: In the app, they're listed as the "Suggested" in the "Top" tab at the "Search" screen.
     *
     * @param string $type One of: "blended", "users", "hashtags" or "places".
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\SuggestedSearchesResponse
     */
    public function getSuggestedSearches(
        $type,
    ) {
        if (!in_array($type, ['blended', 'users', 'hashtags', 'places'], true)) {
            throw new \InvalidArgumentException(sprintf('Unknown search type: %s.', $type));
        }

        return $this->ig->request('fbsearch/suggested_searches/')
            ->addParam('type', $type)
            ->getResponse(new Response\SuggestedSearchesResponse());
    }

    /**
     * Get recent searches via Facebook's algorithm.
     *
     * NOTE: In the app, they're listed as the "Recent" in the "Top" tab at the "Search" screen.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\RecentSearchesResponse
     */
    public function getRecentSearches()
    {
        return $this->ig->request('fbsearch/recent_searches/')
            ->getResponse(new Response\RecentSearchesResponse());
    }

    /**
     * Clear the search history.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\GenericResponse
     */
    public function clearSearchHistory()
    {
        return $this->ig->request('fbsearch/clear_search_history/')
            ->setSignedPost(false)
            ->addPost('_uuid', $this->ig->uuid)
            // ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get nullstate dynamic sections.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\NullstateDynamicSectionsResponse
     */
    public function getNullStateDynamicSections()
    {
        return $this->ig->request('fbsearch/nullstate_dynamic_sections/')
            ->setSignedPost(false)
            ->addParam('type', 'blended')
            ->getResponse(new Response\NullstateDynamicSectionsResponse());
    }

    /**
     * TODO.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\GenericResponse
     */
    public function markSuSeen()
    {
        return $this->ig->request('discover/mark_su_seen/')
            ->setSignedPost(false)
            ->addPost('_uuid', $this->ig->uuid)
            // ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get user suggestions based on different algorithms.
     *
     * @param string   $module             The module where the request is being called.
     * @param int|null $maxNumberToDisplay Max number of results to display.
     * @param bool     $paginate           Paginate.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\DiscoverPeopleResponse
     */
    public function getAyml(
        $module = 'following',
        $maxNumberToDisplay = 10,
        $paginate = false,
    ) {
        $request = $this->ig->request('discover/ayml/')
            ->setSignedPost(false)
            ->addPost('module', $module)
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('_uuid', $this->ig->uuid);
        // ->addPost('_csrftoken', $this->ig->client->getToken())

        if ($maxNumberToDisplay !== null) {
            $request->addPost('max_number_to_display', $maxNumberToDisplay);
        }

        if ($paginate !== false) {
            $request->addPost('paginate', 'true');
        }

        return $request->getResponse(new Response\DiscoverPeopleResponse());
    }

    /**
     * DEPRECATED.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\GenericResponse
     */
    public function profileSuBadge()
    {
        return $this->ig->request('discover/profile_su_badge/')
            ->setSignedPost(false)
            ->addPost('_uuid', $this->ig->uuid)
            // ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * TODO.
     *
     * @param $targetId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\GenericResponse
     */
    public function surfaceWithSu(
        $targetId,
    ) {
        return $this->ig->request('discover/surface_with_su/')
            ->setSignedPost(false)
            ->addPost('target_id', $targetId)
            ->addPost('mutual_followers_limit', 12)
            ->addPost('module', 'profile_social_context')
            ->addPost('_uuid', $this->ig->uuid)
            // ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get recommended users based on another user.
     *
     * @param string      $targetId Numerical UserPK ID.
     * @param string|null $module   Module.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\ChainingUsersResponse
     */
    public function getChainingUsers(
        $targetId,
        $module = null,
    ) {
        $request = $this->ig->request('discover/chaining/')
            ->setSignedPost(false)
            ->addParam('target_id', $targetId);

        if ($module !== null) {
            $request->addParam('module', $module);
        }

        return $request->getResponse(new Response\ChainingUsersResponse());
    }

    /**
     * Get recommended users for you.
     *
     * @param string $entryPoint Module entry point.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\DiscoveryAccountsResponse
     */
    public function getDiscoveryAccounts(
        $entryPoint = 'self_profile',
    ) {
        return $this->ig->request('discover/account_discovery/')
            ->setSignedPost(false)
            ->addParam('entry_point', $entryPoint)
            ->getResponse(new Response\DiscoveryAccountsResponse());
    }

    /**
     * Get recommended users for you.
     *
     * @param bool $fromNux
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\DiscoveryAccountsResponse
     */
    public function getRecommendedAccounts(
        $fromNux = false,
    ) {
        return $this->ig->request('discover/sectioned_ayml/')
            ->setSignedPost(false)
            ->addParam('request_from_nux', ($fromNux) ? 'true' : 'false')
            ->getResponse(new Response\DiscoveryAccountsResponse());
    }
}
