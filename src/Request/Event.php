<?php

namespace InstagramAPI\Request;

use InstagramAPI\Constants;
use InstagramAPI\Debug;
use InstagramAPI\Exception\NetworkException;
use InstagramAPI\Signatures;

/**
 * Functions related to Instagram's logging events.
 */
class Event extends RequestCollection
{
    /**
     * Adds the main body information to the batch data.
     *
     * @param array $batch Batch data.
     *
     * @return array
     */
    protected function _addBatchBody(
        $batch)
    {
        $body =
        [
            'seq'               => $this->ig->batchIndex,
            'app_id'            => Constants::FACEBOOK_ANALYTICS_APPLICATION_ID,
            //'app_ver'           => Constants::IG_VERSION,
            'build_num'         => Constants::VERSION_CODE,
            'device_id'         => $this->ig->uuid,
            'family_device_id'  => $this->ig->phone_id,
            'session_id'        => $this->ig->client->getPigeonSession(),
            //'channel'           => 'regular',
            //'log_type'          => 'client_event',
            //'app_uid'           => $this->ig->account_id,
            //'config_version'    => 'v2',
            //'config_checksum'   => empty($this->ig->settings->get('checksum')) ? null : $this->ig->settings->get('checksum'),
            'data'              => $batch,
        ];

        if ($this->ig->client->wwwClaim !== '') {
            $body = array_slice($body, 0, 11, true) + ['claims' => [$this->ig->client->wwwClaim]] + array_slice($body, 11, count($body) - 1, true);
        }

        return $body;
    }

    /**
     * Adds common properties to the event.
     *
     * @param array $array Graph QL event.
     * @param mixed $event
     *
     * @return array
     */
    protected function _addCommonProperties(
        $event)
    {
        $commonProperties =
        [
            'pk'                                            => isset($event['pk']) ? $event['pk'] : $this->ig->account_id,
            'release_channel'                               => 'prod',
            'radio_type'                                    => $this->ig->getRadioType(),
            'pigeon_reserved_keyword_requested_latency'     => -1, // TODO
        ];

        return array_merge($commonProperties, $event);
    }

    /**
     * Adds event body.
     *
     * @param string $name   Name of the event.
     * @param string $module Module name.
     * @param array  $extra  The event data.
     *
     * @return array
     */
    protected function _addEventBody(
        $name,
        $module,
        $extra)
    {
        $event =
        [
            'log_type'      => 'client_event',
            'bg'            => 'false',
            'name'          => $name,
            'time'          => number_format(microtime(true), 3, '.', ''),
            'sampling_rate' => 1,
            'extra'         => $this->_addCommonProperties($extra),
        ];

        if ($module !== null) {
            $event = array_slice($event, 0, 2, true) + ['module' => $module] + array_slice($event, 2, count($event) - 1, true);
        }

        if (!empty($this->_getTagsForNameAndModule($name, $module))) {
            $event = array_slice($event, 0, 3, true) + $this->_getTagsForNameAndModule($name, $module) + array_slice($event, 3, count($event) - 1, true);
        }

        return $event;
    }

    /**
     * Get module class.
     *
     * @param string $module Module.
     *
     * @return string
     */
    protected function _getModuleClass(
        $module)
    {
        switch ($module) {
            case 'feed_timeline':
                $class = '1kw';
                break;
            case 'newsfeed_you':
                $class = 'Ahc';
                break;
            case 'explore_popular':
                $class = '21M';
                break;
            case 'search':
            case 'blended_search':
            case 'search_result':
                $class = 'Cr5';
                break;
            case 'search_places':
                $class = 'Cse';
                break;
            case 'search_users':
                $class = 'CxZ';
                break;
            case 'search_tags':
                $class = 'CuP';
                break;
            case 'search_audio':
                $class = 'Crk';
                break;
            case 'blended_search_edit_recent':
                $class = 'CUT';
                break;
            case 'feed_hashtag':
                $class = 'CHJ';
                break;
            case 'feed_location':
                $class = 'CHK';
                break;
            case 'feed_contextual_chain':
                $class = 'Aud';
                break;
            case 'feed_contextual_place':
            case 'feed_contextual_location':
            case 'feed_contextual_hashtag':
            case 'feed_contextual_profile':
            case 'feed_contextual_self_profile':
                $class = 'Ar0';
                break;
            case 'profile':
            case 'self_profile': // UserDetailFragment, ProfileMediaTabFragment
                $class = 'A6J';
                break;
            case 'following_sheet':
                $class = 'ProfileFollowRelationshipFragment';
                break;
            case 'bottom_sheet_profile':
                $class = 'Abq';
                break;
            case 'settings_category_options':
                $class = '9fW';
                break;
            case 'privacy_options':
                $class = 'A3u';
                break;
            case 'unified_follow_lists':
            case 'self_unified_follow_lists':
                $class = 'UnifiedFollowFragment';
                break;
            case 'likers':
                $class = 'AdL';
                break;
            case 'tabbed_gallery_camera':
                $class = 'MediaCaptureFragment';
                break;
            case 'photo_filter':
                $class = 'Dik';
                break;
            case 'gallery_picker':
                $class = 'Df2';
                break;
            case 'quick_capture_fragment':
                $class = '155';
                break;
            case 'metadata_followers_share':
                $class = 'FollowersShareFragment';
                break;
            case 'direct_inbox':
                $class = 'Cjq';
                break;
            case 'direct_thread':
                $class = '3xM';
                break;
            case 'direct_recipient_picker':
                $class = 'Cjy';
                break;
            case 'reel_profile':
                $class = 'ReelViewerFragment';
                break;
            case 'edit_profile':
                $class = 'A2C';
                break;
            case 'personal_information':
                $class = '72Z';
                break;
            case 'profile_edit_bio':
                $class = 'EMx';
                break;
            case 'comments_v2_feed_contextual_profile':
                $class = 'CommentThreadFragment';
                break;
            case 'self_followers':
            case 'self_following':
                $class = 'AeV';
                break;
            case 'login_landing':
                $class = '7Iz';
                break;
            default:
                $class = false;
        }

        return $class;
    }

    /**
     * Generate nav chain.
     *
     * @param string $module     Module.
     * @param string $clickPoint Click point.
     *
     * @return string|null
     */
    protected function _generateNavChain(
        $module,
        $clickPoint)
    {
        $class = $this->_getModuleClass($module);

        if ($this->ig->getPrevNavChainClass() === $class) {
            $this->ig->incrementNavChainStep();

            return $this->ig->getNavChain();
        }

        if ($class === false) {
            $this->ig->incrementNavChainStep();

            return $this->ig->getNavChain();
        }

        if ($module === 'feed_timeline') {
            $this->ig->setNavChainStep(1);
            $this->ig->setNavChain('');
        } elseif ($module === 'explore_popular') {
            $this->ig->setNavChainStep(2);
            $this->ig->setNavChain('');
        } elseif ($module === 'newsfeed_you') {
            $this->ig->setNavChainStep(3);
            $this->ig->setNavChain('');
        }

        $chain = '';
        if ($this->ig->getNavChain() !== '') {
            $chain = ',';
        }

        if ($clickPoint === 'back') {
            $chain = $this->ig->getNavChain();
            $chains = explode(',', $chain);
            array_pop($chains);
            $newChain = implode(',', $chains);
            $this->ig->setNavChain('');
            $this->ig->setNavChain($newChain);
            $this->ig->setPrevNavChainClass(explode(':', end($chains))[0]);
            $this->ig->decrementNavChainStep();
        } else {
            $chain .= sprintf('%s:%s:%d', $class, $module, $this->ig->getNavChainStep());
            $this->ig->setPrevNavChainClass($class);
            $this->ig->setNavChain($chain);
            $this->ig->incrementNavChainStep();
        }

        return $this->ig->getNavChain();
    }

    /**
     * Return if tags property is used for the event.
     *
     * @param array  $event  Batch data.
     * @param string $name   The event name.
     * @param string $module Instagram module.
     */
    protected function _getTagsForNameAndModule(
        $name,
        $module)
    {
        return
        ($name === 'explore_home_impression' && $module === 'explore_popular'
           || $name === 'instagram_organic_impression' && $module === 'reel_profile'
           || $name === 'instagram_organic_sub_impression' && $module === 'reel_profile'
           || $name === 'instagram_organic_impression' && $module === 'feed_contextual_profile'
           || $name === 'instagram_organic_impression' && $module === 'feed_contextual_chain'
           || $name === 'instagram_organic_impression' && $module === 'feed_timeline'
           || $name === 'instagram_organic_time_spent' && $module === 'feed_contextual_profile'
           || $name === 'instagram_organic_time_spent' && $module === 'feed_contextual_chain'
           || $name === 'instagram_organic_time_spent' && $module === 'feed_timeline'
           || $name === 'instagram_organic_viewed_impression' && $module === 'feed_contextual_profile'
           || $name === 'instagram_organic_viewed_impression' && $module === 'feed_contextual_chain'
           || $name === 'instagram_organic_viewed_impression' && $module === 'feed_timeline'
           || $name === 'instagram_wellbeing_warning_system_success_creation' && $module === 'comments_v2'
           || $name === 'android_string_impressions' && $module === 'IgResourcesAnalyticsModule') ?
        [
            'tags'  => (
              $name === 'android_string_impressions'
              || $name === 'instagram_wellbeing_warning_system_success_creation'
              || $name === 'direct_inbox_tab_impression'
              || $name === 'ig_direct_inbox_fetch_success_rate'
              || $name === 'direct_inbox_thread_impression'
            ) ? 1 : 32,
        ]
        : [];
    }

    /**
     * Adds event to the event batch and sends it if reached 50 events.
     *
     * @param array $event Batch data.
     * @param mixed $batch
     */
    protected function _addEventData(
        $event,
        $batch = 2)
    {
        $this->ig->eventBatch[$batch][] = $event;

        foreach ($this->ig->eventBatch as $batch) {
            if (count($batch) === 50) {
                $this->_sendBatchEvents();
                $this->ig->eventBatch = [];
                $this->ig->batchIndex++;
                break;
            }
        }
    }

    /**
     * Save pending events for future sessions.
     */
    public function savePendingEvents()
    {
        $this->ig->settings->set('pending_events', json_encode($this->ig->eventBatch));
    }

    /**
     * Force send batch event.
     */
    public function forceSendBatch()
    {
        foreach ($this->ig->eventBatch as $batch) {
            if (!empty($batch)) {
                $this->_sendBatchEvents();
                $this->ig->eventBatch = [
                    [],
                    [],
                    [],
                ];
                break;
            }
        }
    }

    /**
     * Sets and updates checksum from Graph API.
     *
     * @param mixed $response
     */
    protected function _updateChecksum(
        $response)
    {
        if (!empty($response['checksum'])) {
            $this->ig->settings->set('checksum', $response['checksum']);
        }
    }

    /**
     * Send the generated event batch to Facebook's Graph API.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    protected function _sendBatchEvents()
    {
        $batchFilename = sprintf('%s_%s_regular.batch.gz', Signatures::generateUUID(), $this->ig->batchIndex);

        $batches = [];
        foreach ($this->ig->eventBatch as $batch) {
            if (!empty($batch)) {
                $batches[] = $this->_addBatchBody($batch);
            }
        }

        $request = $this->ig->request(Constants::GRAPH_API_URL)
          ->setSignedPost(false)
          ->setNeedsAuth(false)
          ->addHeader('X-IG-Connection-Type', Constants::X_IG_Connection_Type)
          ->addHeader('X-IG-Capabilities', Constants::IOS_X_IG_Capabilities)
          ->addHeader('X-IG-APP-ID', Constants::FACEBOOK_ANALYTICS_APPLICATION_ID)
          ->addHeader('X-FB-HTTP-Engine', Constants::X_FB_HTTP_Engine)
          ->addPost('access_token', Constants::FACEBOOK_ANALYTICS_APPLICATION_ID.'|'.Constants::GRAPH_API_ACCESS_TOKEN)
          ->addPost('format', 'json')
          ->addPost('sent_time', round(microtime(true), 3))
          ->setAddDefaultHeaders(false);

        switch ($this->ig->getEventsCompressedMode()) {
            case 0:
                $batch = json_encode($batches);
                $request->addPost('cmethod', 'deflate')
                        ->addFileData(
                            'cmsg',
                            gzdeflate($batch),
                            $batchFilename
                        );
                        // no break
            case 1:
                $message = [
                    'request_info'  => [
                        'tier'              => 'micro_batch',
                        'carrier'           => 'Android',
                        'conn'              => Constants::X_IG_Connection_Type,
                    ],
                    'config'        => [
                        'config_checksum'   => empty($this->ig->settings->get('checksum')) ? null : $this->ig->settings->get('checksum'),
                        'config_version'    => 'v2',
                        'app_uid'           => $this->ig->account_id,
                        'app_ver'           => Constants::IG_VERSION,
                    ],
                    'batches'       => [
                        $batches,
                    ],
                ];
                $request->addPost('compressed', 0)
                        ->addPost('multi_batch', 1)
                        ->addPost('message', json_encode($message));
                        // no break
            case 2:
                if (count($batches) > 1) {
                    $message = [
                        'request_info'  => [
                            'tier'              => 'micro_batch',
                            'sent_time'         => round(microtime(true), 3),
                            'carrier'           => 'Android',
                            'conn'              => Constants::X_IG_Connection_Type,
                        ],
                        'config'        => [
                            'config_checksum'   => empty($this->ig->settings->get('checksum')) ? null : $this->ig->settings->get('checksum'),
                            'config_version'    => 'v2',
                            'app_uid'           => $this->ig->account_id,
                            'app_ver'           => Constants::IG_VERSION,
                        ],
                        'batches'       => [
                            $batches,
                        ],
                    ];
                    $request->addPost('compressed', 1)
                            ->addPost('multi_batch', 1)
                            ->addPost('message', base64_encode(gzdeflate(json_encode($message))));
                } else {
                    $batches[0]['tier'] = 'micro_batch';
                    $batches[0]['sent_time'] = round(microtime(true), 3);
                    $batches[0]['carrier'] = 'Android';
                    $batches[0]['conn'] = Constants::X_IG_Connection_Type;
                    $batches[0]['config_checksum'] = empty($this->ig->settings->get('checksum')) ? null : $this->ig->settings->get('checksum');
                    $batches[0]['config_version'] = 'v2';
                    $batches[0]['app_uid'] = $this->ig->account_id;
                    $batches[0]['app_ver'] = Constants::IG_VERSION;

                    $request->addPost('compressed', 1)
                            ->addPost('message', base64_encode(gzdeflate(json_encode($batches[0]), -1, ZLIB_ENCODING_DEFLATE)));
                }
        }

        try {
            $response = $request->getDecodedResponse();
        } catch (NetworkException $e) {
            // Ignore network exceptions.
            return;
        } finally {
            // TODO: put batch.gz in queue or retry multiple times before discarding batch.
        }

        $path = Debug::$debugLogPath;
        if ($this->ig->settings->getStorage() instanceof \InstagramAPI\Settings\Storage\File) {
            if ($path === null) {
                $path = $this->ig->settings->getUserPath($this->ig->username);
            }
        }
        Debug::printEvent($batches, $path, $this->ig->debug);

        $this->_updateChecksum($response);
    }

    /**
     * Send login/register steps events.
     *
     * REGISTER:
     *
     * 1) register_full_name_focused
     * 2) register_password_focused
     * 3) next_button_tapped
     * 4) contacts_import_opt_in
     * 5) valid_password
     * 6) step_view_loaded
     *
     *  LOGIN:
     *
     * 1) step_view_loaded
     * 2) landing_created
     * 3) log_in_username_focus
     * 4) log_in_password_focus
     * 5) log_in_attempt
     * 6) sim_card_state
     * At this point we call login()
     * 7) log_in
     *
     * @param string $step        Step.
     * @param string $name        Name of the event.
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param array  $options     Options.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendFlowSteps(
        $step,
        $name,
        $waterfallId,
        $startTime,
        array $options = [])
    {
        $currentTime = round(microtime(true) * 1000);

        $extra = [
            'start_time'        => $startTime,
            'waterfall_id'      => $waterfallId,
            'os_version'        => $this->ig->device->getAndroidVersion(),
            'elapsed_time'      => $currentTime - $startTime,
            'guid'              => $this->ig->uuid,
            'step'              => $step,
            'current_time'      => $currentTime,
            'pk'                => '0',
        ];

        if ($name === 'log_in_attempt') {
            $extra['keyboard'] = false;
            $extra['log_in_token'] = $this->ig->username;
        } elseif ($name === 'sim_card_state') {
            $extra['has_permission'] = false;
            $extra['sim_state'] = 'absent';
        } elseif ($name === 'log_in') {
            $extra['instagram_id'] = $this->ig->account_id;
        } elseif ($name === 'register_full_name_focused' || $name === 'register_password_focused') {
            $extra['flow'] = isset($options['flow']) ? $options['flow'] : 'email';
        } elseif ($name === 'reg_field_interacted') {
            $extra['field_name'] = $options['field_name'];
            $extra['interaction_type'] = 'tapped';
        } elseif ($name === 'next_button_tapped') {
            $extra['keyboard'] = false;
        } elseif ($name === 'contacts_import_opt_in') {
            $extra['fb_family_device_id'] = $this->ig->phone_id;
            $extra['is_ci_opt_in'] = false;
            $extra['event_tag'] = [
                'REGISTRATION',
                'one_page_registration',
            ];
        } elseif ($name === 'valid_password') {
            $extra['contains_only_ascii'] = isset($options['contains_only_ascii']) ? $options['contains_only_ascii'] : true;
        } elseif ($name === 'ig_dynamic_onboarding_updated_steps_from_server') {
            $extra['update_duration'] = mt_rand(120, 300);
        } elseif ($name === 'register_with_ci_option') {
            $extra['username_suggestion_avail'] = false;
            $extra['username_suggestion_changed_by_user'] = false;
            $extra['is_opted_in'] = false;
            $extra['event_tag'] = [
                'REGISTRATION',
                'username',
            ];
        } elseif ($name === 'register_account_request_submitted') {
            $extra['fb_family_device_id'] = $this->ig->phone_id;
            $extra['chosen_signup_type'] = isset($options['flow']) ? $options['flow'] : 'email';
            $extra['retry_strategy'] = 'none';
            $extra['attempt_count'] = 1;
        } elseif ($name === 'register_account_created') {
            $extra['reg_type'] = 'consumer';
            $extra['instagram_id'] = $options['instagram_id'];
            $extra['chosen_signup_type'] = isset($options['flow']) ? $options['flow'] : 'email';
            $extra['retry_strategy'] = 'none';
            $extra['attempt_count'] = 1;
        } elseif ($name === 'step_view_loaded') {
            $extra['is_facebook_app_installed'] = isset($options['is_facebook_app_installed']) ? $options['is_facebook_app_installed'] : (bool) random_int(0, 1);
            $extra['messenger_installed'] = isset($options['messenger_installed']) ? $options['messenger_installed'] : (bool) random_int(0, 1);
            $extra['whatsapp_installed'] = isset($options['whatsapp_installed']) ? $options['whatsapp_installed'] : (bool) random_int(0, 1);
            $extra['fb_lite_installed'] = isset($options['fb_lite_installed']) ? $options['fb_lite_installed'] : (bool) random_int(0, 1);
            $extra['source'] = null;
            $extra['flow'] = null;
            $extra['cp_type_given'] = null;
        } elseif ($name === 'landing_created') {
            $extra['is_facebook_app_installed'] = isset($options['is_facebook_app_installed']) ? $options['is_facebook_app_installed'] : (bool) random_int(0, 1);
            $extra['did_facebook_sso'] = false;
            $extra['did_log_in'] = false;
            $extra['network_type'] = Constants::X_IG_Connection_Type;
            $extra['app_lang'] = $this->ig->getLocale();
            $extra['device_lang'] = $this->ig->getLocale();
            $extra['funnel_name'] = 'landing';
        }

        $event = $this->_addEventBody($name, 'waterfall_log_in', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send funnel registration.
     *
     * TODO: Relative organic time.
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $instanceId  TODO.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendFunnelRegistration(
        $waterfallId,
        $startTime,
        $instanceId)
    {
        $actions =
        [
            [
                'relative_time' => mt_rand(80, 120),
                'name'          => 'landing:step_loaded',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(420, 460),
                'name'          => 'landing:sim_card_state',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'landing:switch_to_log_in',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'landing:step_loaded',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'landing:first_party_token_acquired',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'landing:first_party_token_acquired',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'login:text_field_focus',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'login:text_field_focus',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'login:next_tapped',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'login:sim_card_state',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'login:log_in_success',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'funnel_end',
                'tag'           => 'explicit',
            ],
        ];

        $extra = [
            'start_time'        => $startTime,
            'waterfall_id'      => $waterfallId,
            'sampling_rate'     => 1,
            'instance_id'       => $instanceId,
            'app_device_id'     => $this->ig->uuid,
            'funnel_id'         => '8539',
            'actions'           => json_encode($actions),
            'tags'              => json_encode(['waterfallId:'.$waterfallId, 'is_not_add_account']),
            'pseudo_end'        => true,
            'name'              => 'IG_REGISTRATION_FUNNEL',
            'pk'                => '0',
            'release_channel'   => null,
            'radio_type'        => $this->ig->getRadioType(),
        ];

        $event = $this->_addEventBody('ig_funnel_analytics', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send Thumbnail impression or thumbnail click.
     *
     * @param string                            $type        'instagram_thumbnail_impression' to send a view impression on a thumbnail.
     *                                                       'instagram_thumbnail_click' to send a click event on a thumbnail.
     * @param \InstagramAPI\Response\Model\Item $item        The item object.
     * @param string                            $module      'profile', 'feed_timeline' or 'feed_hashtag'.
     * @param string|null                       $hashtagId   The hashtag ID. Only used when 'feed_hashtag' is used as module.
     * @param string|null                       $hashtagName The hashtag name. Only used when 'feed_hashtag' is used as module.
     * @param array                             $options     Options to configure the event.
     *                                                       'position', string, the media position.
     *                                                       'following', string, 'following' or 'not_following'.
     *                                                       'feed_type', string, 'top', 'recent'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendThumbnailImpression(
        $type,
        $item,
        $module,
        $hashtagId = null,
        $hashtagName = null,
        array $options = [])
    {
        if ($type !== 'instagram_thumbnail_impression' && $type !== 'instagram_thumbnail_click') {
            throw new \InvalidArgumentException(sprintf('%s is not a valid event name.', $type));
        }

        if ($module === 'profile' || $module === 'self_profile') {
            $extra = [
                'id'                        => $item->getId(),
                'm_pk'                      => $item->getId(),
                'position'                  => isset($options['position']) ? $options['position'] : '["0", "0"]',
                'media_type'                => $item->getMediaType(),
                'entity_type'               => 'user',
                'entity_id'                 => $item->getUser()->getPk(),
                'entity_name'               => $item->getUser()->getUsername(),
                'entity_page_name'          => $item->getUser()->getUsername(),
                'entity_page_id'            => $item->getUser()->getPk(),
                'media_thumbnail_section'   => 'grid',
            ];
        } elseif ($module === 'feed_timeline') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => 'following',
                'inventory_source'          => 'media_or_ad',
                'm_ix'                      => 0,
                'imp_logger_ver'            => 16,
                'is_eof'                    => false,
                'timespent'                 => mt_rand(1, 4),
                'avgViewPercent'            => 1,
                'maxViewPercent'            => 1,
            ];
        } elseif ($module === 'feed_location') {
            $extra = [
                'id'                        => $item->getId(),
                'm_pk'                      => $item->getId(),
                'position'                  => isset($options['position']) ? $options['position'] : '["0", "0"]',
                'media_type'                => $item->getMediaType(),
                'type'                      => isset($options['type']) ? $options['type'] : '0',
                'entity_type'               => 'place',
                'entity_id'                 => $item->getLocation()->getPk(),
                'entity_name'               => $item->getLocation()->getName(),
                'entity_page_name'          => $item->getLocation()->getName(),
                'entity_page_id'            => $item->getLocation()->getPk(),
                'feed_type'                 => isset($options['feed_type']) ? $options['feed_type'] : 'top',
                'tab_index'                 => isset($options['tab_index']) ? $options['tab_index'] : 0,
                'media_thumbnail_section'   => 'grid',
            ];
        } elseif ($module === 'feed_hashtag') {
            if ($hashtagId === null) {
                throw new \InvalidArgumentException('No hashtag ID provided.');
            }
            if ($hashtagName === null) {
                throw new \InvalidArgumentException('No hashtag name provided.');
            }
            $extra = [
                'id'                        => $item->getId(),
                'm_pk'                      => $item->getId(),
                'hashtag_id'                => $hashtagId,
                'hashtag_name'              => $hashtagName,
                'hashtag_follow_status'     => isset($options['following']) ? 'following' : 'not_following',
                'hashtag_feed_type'         => isset($options['feed_type']) ? $options['feed_type'] : 'top',
                'tab_index'                 => isset($options['tab_index']) ? $options['tab_index'] : 0,
                'source_of_action'          => 'feed_contextual_hashtag',
                'session_id'                => $this->ig->client->getPigeonSession(),
                'media_type'                => $item->getMediaType(),
                'type'                      => 0,
                'section'                   => 0,
                'position'                  => isset($options['position']) ? $options['position'] : '["0","0"]',
            ];
        } else {
            throw new \InvalidArgumentException('Module not supported.');
        }

        $event = $this->_addEventBody($type, $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send organic time spent.
     *
     * This event tells Instagram how much time do you spent on each module.
     *
     * @param \InstagramAPI\Response\Model\Item $item                The item object.
     * @param string                            $followingUserStatus Following status. 'following' or 'not_following'.
     * @param string                            $timespent           Time spent in milliseconds.
     * @param string                            $module              The current module you are. 'feed_contextual_profile',
     *                                                               'feed_contextual_self_profile',
     *                                                               'feed_contextual_chain',
     * @param array                             $clusterData         Cluster data used in 'feed_contextual_chain' module.
     *                                                               'feed_position' zero based position of the media in the feed.
     *                                                               'chaining_session_id' UUIDv4.
     *                                                               'topic_cluster_id' 'explore_all:0' (More info on Discover class).
     *                                                               'topic_cluster_title' 'For You' (More info on Discover class).
     *                                                               'topic_cluster_type' 'explore_all' (More info on Discover class).
     *                                                               'topic_cluster_session_id' UUIDv4.
     * @param array|null                        $options
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicTimespent(
        $item,
        $followingUserStatus,
        $timespent,
        $module,
        array $clusterData = [],
        array $options = null)
    {
        if ($module === 'feed_contextual_profile' || $module === 'feed_contextual_self_profile' || $module === 'feed_short_url') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => $followingUserStatus,
                'm_ix'                      => 1,
                'timespent'                 => $timespent,
                'avgViewPercent'            => 1,
                'maxViewPercent'            => 1,
                'media_thumbnail_section'   => 'grid',
                'entity_page_name'          => $item->getUser()->getUsername(),
                'entity_page_id'            => $item->getUser()->getPk(),
            ];
        } elseif ($module === 'feed_contextual_chain') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => $followingUserStatus,
                'connection_id'             => '180',
                'imp_logger_ver'            => 16,
                'timespent'                 => $timespent,
                'avgViewPercent'            => 1,
                'maxViewPercent'            => 1,
                'chaining_position'         => $clusterData['feed_position'],
                'chaining_session_id'       => $clusterData['chaining_session_id'],
                'm_ix'                      => 0,
                'topic_cluster_id'          => $clusterData['topic_cluster_id'], // example: 'explore_all:0'
                'topic_cluster_title'       => $clusterData['topic_cluster_title'], // example: 'For You'
                'topic_cluster_type'        => $clusterData['topic_cluster_type'], // example: 'explore_all'
                'topic_cluster_debug_info'	 => null,
                'topic_cluster_session_id'	 => $clusterData['topic_cluster_session_id'],
            ];
        } elseif ($module === 'feed_contextual_hashtag') {
            $extra = [
                'id'                        => $item->getId(),
                'm_pk'                      => $item->getId(),
                'hashtag_id'                => $options['hashtag_id'],
                'hashtag_name'              => $options['hashtag_name'],
                'hashtag_follow_status'     => isset($options['following']) ? 'following' : 'not_following',
                'hashtag_feed_type'         => isset($options['feed_type']) ? $options['feed_type'] : 'top',
                'tab_index'                 => isset($options['tab_index']) ? $options['tab_index'] : 0,
                'source_of_action'          => $module,
                'timespent'                 => $timespent,
                'session_id'                => $this->ig->client->getPigeonSession(),
                'media_type'                => $item->getMediaType(),
                'type'                      => 0,
                'section'                   => 0,
                'position'                  => isset($options['position']) ? $options['position'] : '["0","0"]',
            ];
        } elseif ($module === 'feed_timeline') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => 'following',
                'inventory_source'          => 'media_or_ad',
                'm_ix'                      => 0,
                'imp_logger_ver'            => 16,
                'is_eof'                    => false,
                'timespent'                 => $timespent,
            ];
        } elseif (
            $module === 'reel_feed_timeline'
            || $module === 'reel_profile'
            || $module === 'reel_follow_list'
            || $module === 'reel_liker_list'
            || $module === 'reel_hashtag_feed'
            || $module === 'reel_location_feed'
            || $module === 'reel_comment') {
            $extra = [
                'm_pk'                          => $item->getId(),
                'a_pk'                          => $item->getUser()->getPk(),
                'm_ts'                          => (int) $item->getTakenAt(),
                'm_t'                           => $item->getMediaType(),
                'tracking_token'                => $item->getOrganicTrackingToken(),
                'action'					                   => 'webclick',
                'source_of_action'              => $module,
                'follow_status'                 => isset($options['following']) ? 'following' : 'not_following',
                'viewer_session_id'             => $options['viewer_session_id'],
                'tray_session_id'               => $options['tray_session_id'],
                'reel_id'                       => $item->getId(),
                'reel_position'                 => isset($options['reel_position']) ? $options['reel_position'] : 0,
                'reel_viewer_position'          => isset($options['reel_viewer_position']) ? $options['reel_viewer_position'] : 0,
                'reel_type'                     => 'story',
                'reel_size'                     => isset($options['reel_size']) ? $options['reel_size'] : 1,
                'is_video_to_carousel'          => false,
                'tray_position'                 => isset($options['tray_position']) ? $options['tray_position'] : 1,
                'session_reel_counter'          => isset($options['session_reel_counter']) ? $options['session_reel_counter'] : 1,
                'time_elapsed'                  => isset($options['time_elapsed']) ? $options['time_elapsed'] : mt_rand(1, 2),
                'timespent'                     => $timespent,
                'elapsed_time_since_last_item'  => -1,
                'reel_start_position'           => 0,
                'is_acp_delivered'              => false,
            ];
        } else {
            throw new \InvalidArgumentException(sprintf('%s module is not supported.'));
        }

        $event = $this->_addEventBody('instagram_organic_time_spent', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send organic reel/story impression.
     *
     * @param \InstagramAPI\Response\Model\Item $item                The item object.
     * @param string                            $viewerSessionId     UUIDv4.
     * @param string                            $traySessionId       UUIDv4.
     * @param string                            $rankingToken        UUIDv4.
     * @param string                            $followingUserStatus Following status. 'following' or 'not_following'.
     * @param string                            $source              Source of action. 'reel_feed_timeline'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicReelImpression(
        $item,
        $viewerSessionId,
        $traySessionId,
        $rankingToken,
        $followingUserStatus,
        $source = 'reel_feed_timeline')
    {
        $extra = [
            'm_pk'                      => $item->getId(),
            'a_pk'                      => $item->getUser()->getPk(),
            'm_ts'                      => (int) $item->getTakenAt(),
            'm_t'                       => $item->getMediaType(),
            'tracking_token'            => $item->getOrganicTrackingToken(),
            'action'                    => 'webclick',
            'source_of_action'          => $source,
            'follow_status'             => ($source === 'reel_feed_timeline') ? 'following' : $followingUserStatus,
            'viewer_session_id'         => $viewerSessionId,
            'tray_session_id'           => $traySessionId,
            'reel_id'                   => $item->getUser()->getPk(),
            'is_pride_reel'             => false,
            'is_besties_reel'           => false,
            'story_ranking_token'       => $rankingToken,
        ];

        $event = $this->_addEventBody('instagram_organic_reel_impression', $source, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send reel tray impression.
     *
     * @param \InstagramAPI\Response\Model\Item $item          The item object.
     * @param string                            $traySessionId UUIDv4.
     * @param string                            $rankingToken  UUIDv4.
     * @param array                             $options       Options.
     * @param string                            $source        Source of action. 'feed_timeline'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendReelTrayImpression(
        $item,
        $traySessionId,
        $rankingToken,
        array $options = [],
        $source = 'feed_timeline')
    {
        $extra = [
            'has_my_reel'                      => isset($options['has_my_reel']) ? strval($options['has_my_reel']) : '0',
            'has_my_replay_reel'               => isset($options['has_my_replay_reel']) ? strval($options['has_my_replay_reel']) : '0',
            'viewed_reel_count'                => isset($options['viewed_reel_count']) ? $options['viewed_reel_count'] : 0,
            'new_reel_count'                   => isset($options['new_reel_count']) ? $options['new_reel_count'] : 0,
            'live_reel_count'                  => isset($options['live_reel_count']) ? $options['live_reel_count'] : 0,
            'muted_replay_reel_count'          => isset($options['muted_replay_reel_count']) ? $options['muted_replay_reel_count'] : 0,
            'unfetched_reel_count'             => isset($options['unfetched_reel_count']) ? $options['unfetched_reel_count'] : 0,
            'tray_position'                    => isset($options['tray_position']) ? $options['tray_position'] : 1,
            'tray_session_id'                  => $traySessionId,
            'viewer_session_id'                => null,
            'is_live_reel'                     => isset($options['is_live_reel']) ? strval($options['is_live_reel']) : '0',
            'is_live_questions_reel'           => isset($options['is_live_questions_reel']) ? strval($options['is_live_questions_reel']) : '0',
            'is_new_reel'                      => isset($options['is_new_reel']) ? strval($options['is_new_reel']) : '0',
            'reel_type'                        => 'story',
            'story_ranking_token'              => $rankingToken,
            'reel_id'                          => $item->getUser()->getPk(),
            'is_besties_reel'                  => isset($options['is_besties_reel']) ? strval($options['is_besties_reel']) : '0',
            'a_pk'                             => $item->getUser()->getPk(),
        ];

        $event = $this->_addEventBody('reel_tray_impression', $source, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send feed item inserted.
     *
     * @param \InstagramAPI\Response\Model\Item $item      The item object.
     * @param string                            $requestId UUIDv4.
     * @param string                            $sessionId UUIDv4.
     * @param array                             $options   Options.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendFeedItemInserted(
        $item,
        $requestId,
        $sessionId,
        array $options = [])
    {
        $extra = [
            'request_id'                      => $requestId,
            'session_id'                      => $sessionId,
            'request_type'                    => isset($options['request_type']) ? $options['request_type'] : 'cold_start_fetch',
            'view_info_count'                 => isset($options['view_info_count']) ? $options['view_info_count'] : 0,
            'feed_item_type'                  => 'media',
            'media_id'                        => $item->getPk(),
            'delivery_flags'                  => 'n',
            'is_ad'                           => isset($options['is_ad']) ? $options['is_ad'] : false,
            'expected_position',
        ];

        $event = $this->_addEventBody('instagram_feed_item_inserted', 'feed_timeline', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send newsfeed story impression.
     *
     * @param Response\Model\Story $newsfeedItem The newsfeed object.
     * @param string               $section      Section.
     * @param int                  $position     Position.
     * @param string               $tab          Tab.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendNewsfeedStoryImpression(
        $newsfeedItem,
        $section,
        $position,
        $tab = 'You')
    {
        $extra = [
            'story_id'                  => $newsfeedItem->getPk(),
            'story_type'                => $newsfeedItem->getStoryType(),
            'tuuid'                     => $newsfeedItem->getArgs()->getTuuid(),
            'section'                   => $section,
            'position'                  => $position,
            'tab'                       => $tab,
        ];

        $event = $this->_addEventBody('newsfeed_story_impression', 'newsfeed_you', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send organic number of likes.
     *
     * @param \InstagramAPI\Response\Model\Item $item    The item object.
     * @param string                            $module  Module.
     * @param array                             $options Options.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicNumberOfLikes(
        $item,
        $module,
        array $options = [])
    {
        $extra = [
            'm_pk'                          => $item->getId(),
            'a_pk'                          => $item->getUser()->getPk(),
            'm_ts'                          => (int) $item->getTakenAt(),
            'm_t'                           => $item->getMediaType(),
            'tracking_token'                => $item->getOrganicTrackingToken(),
            'elapsed_time_since_last_item'  => -1,
            'source_of_action'              => $module,
            'follow_status'                 => isset($options['follow_status']) ? $options['follow_status'] : 'not_following',
            'entity_follow_status'          => isset($options['entity_follow_status']) ? $options['entity_follow_status'] : 'not_following',
            'entity_type'                   => 'user',
            'entity_id'                     => $item->getUser()->getPk(),
            'entity_page_id'                => $item->getUser()->getPk(),
            'entity_name'                   => $item->getUser()->getUsername(),
            'entity_page_name'              => $item->getUser()->getUsername(),
            'media_thumbnail_section'       => 'grid',
        ];

        $event = $this->_addEventBody('instagram_organic_number_of_likes', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send organic action menu.
     *
     * @param string                            $module        Module.
     * @param \InstagramAPI\Response\Model\Item $item          The item object.
     * @param string                            $feedSessionId UUIDv4.
     * @param array                             $options       Options.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicActionMenu(
        $module,
        $item,
        $feedSessionId,
        $options)
    {
        $extra = [
            'm_pk'                          => $item->getId(),
            'a_pk'                          => $item->getUser()->getPk(),
            'm_ts'                          => (int) $item->getTakenAt(),
            'm_t'                           => $item->getMediaType(),
            'tracking_token'                => $item->getOrganicTrackingToken(),
            'source_of_action'              => $module,
            'follow_status'                 => ($module === 'feed_timeline') ? 'following' : $options['follow_status'],
            'm_ix'                          => 1,
            'inventory_source'              => 'media_or_ad',
            'feed_request_id'               => $feedSessionId,
            'elapsed_time_since_last_item'  => -1,
            'connection_id'                 => mt_rand(100, 200),
        ];

        if ($item->hasMezqlToken()) {
            $extra['mezql_token'] = $item->getMezqlToken();
        }

        $event = $this->_addEventBody('instagram_organic_action_menu', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Reel tray refresh.
     *
     * @param array  $options     Options.
     * @param string $refreshType Refresh type: 'disk' or 'network',
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function reelTrayRefresh(
        $options,
        $refreshType)
    {
        $requiredKeys = ['tray_refresh_time', 'tray_session_id'];
        $missingKeys = array_diff($requiredKeys, array_keys($options));
        if (!empty($missingKeys)) {
            throw new \InvalidArgumentException(sprintf('Missing keys "%s" for options.', implode('", "', $missingKeys)));
        }

        $extra = [
            'has_my_reel'               => isset($options['has_my_reel']) ? $options['has_my_reel'] : '0',
            'has_my_replay_reel'        => isset($options['has_my_replay_reel']) ? $options['has_my_replay_reel'] : '0',
            'viewed_reel_count'         => isset($options['viewed_reel_count']) ? $options['viewed_reel_count'] : 0,
            'new_reel_count'            => isset($options['new_reel_count']) ? $options['new_reel_count'] : 0,
            'live_reel_count'           => isset($options['live_reel_count']) ? $options['live_reel_count'] : 0,
            'new_replay_reel_count'     => isset($options['new_replay_reel_count']) ? $options['new_replay_reel_count'] : 0,
            'viewed_replay_reel_count'  => isset($options['viewed_replay_reel_count']) ? $options['viewed_replay_reel_count'] : 0,
            'muted_reel_count'          => isset($options['muted_reel_count']) ? $options['muted_reel_count'] : 0,
            'muted_live_reel_count'     => isset($options['muted_live_reel_count']) ? $options['muted_live_reel_count'] : 0,
            'muted_replay_reel_count'   => isset($options['muted_replay_reel_count']) ? $options['muted_replay_reel_count'] : 0,
            'suggested_reel_count'      => isset($options['suggested_reel_count']) ? $options['suggested_reel_count'] : 0,
            'unfetched_reel_count'      => isset($options['unfetched_reel_count']) ? $options['unfetched_reel_count'] : 0,
            'tray_refresh_time'         => $options['tray_refresh_time'], // secs with millis. 0.335
            'tray_session_id'           => $options['tray_session_id'],
            'was_successful'            => isset($options['was_successful']) ? $options['was_successful'] : true,
            'story_ranking_token'       => null,
        ];

        if ($refreshType === 'disk') {
            $extra['tray_refresh_type'] = 'disk';
            $extra['tray_refresh_reason'] = 'cold_start';
            $module = 'feed_timeline';
        } elseif ($refreshType === 'network') {
            $extra['tray_refresh_type'] = 'network';
            $extra['tray_refresh_reason'] = 'profile_stories';
            $module = 'profile';
        } else {
            throw new \InvalidArgumentException(sprintf('Refresh type %s not supported.', $refreshType));
        }

        $event = $this->_addEventBody('reel_tray_refresh', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send stories request.
     *
     * @param string $traySessionId UUIDv4.
     * @param string $requestId     UUIDv4.
     * @param string $requestType   Request type.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendStoriesRequest(
        $traySessionId,
        $requestId,
        $requestType = 'auto_refresh')
    {
        $extra = [
            'tray_session_id'        => $traySessionId,
            'request_id'             => $requestId,
            'request_type'           => $requestType,
            'app_session_id'         => $this->ig->client->getPigeonSession(),
        ];

        $event = $this->_addEventBody('instagram_stories_request_sent', 'reel_feed_timeline', $extra);
        $this->_addEventData($event);
    }

    /**
     * Reel in feed tray hide.
     *
     * @param string $traySessionId UUIDv4.
     * @param string $hideReason    Hide reason.
     * @param string $trayId        Tray ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function reelInFeedTrayHide(
        $traySessionId,
        $hideReason,
        $trayId)
    {
        $extra = [
            'tray_session_id'   => $traySessionId,
            'hide_reason'       => $hideReason,
            'tray_id'           => $trayId,
        ];

        $event = $this->_addEventBody('reel_in_feed_tray_hide', 'feed_timeline', $extra);
        $this->_addEventData($event);
    }

    /**
     * Main feed request began.
     *
     * @param int    $mediaDepth Medias loaded so far.
     * @param string $reason     Reason.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendStartMainFeedRequest(
        $mediaDepth,
        $reason = 'pagination')
    {
        $extra = [
            'reason'                    => $reason,
            'is_background'             => false,
            'last_navigation_module'    => 'feed_timeline',
            'nav_in_transit'            => false,
            'media_depth'               => $mediaDepth,
            'view_info_count'           => 20,
            'fetch_action'              => 'load_more',
        ];

        $event = $this->_addEventBody('ig_main_feed_request_began', 'feed_timeline', $extra);
        $this->_addEventData($event);
    }

    /**
     * Loading more (Pagination) on main feed.
     *
     * @param int $paginationTime Time when requested pagination.
     * @param int $position       Media position when requested pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendMainFeedLoadingMore(
        $paginationTime,
        $position)
    {
        $extra = [
            'position'                  => $position,
            'last_feed_update_time'     => $paginationTime,
        ];

        $event = $this->_addEventBody('main_feed_loading_more', 'feed_timeline', $extra);
        $this->_addEventData($event);
    }

    /**
     * Main feed request end.
     *
     * @param int    $mediaDepth Medias loaded so far.
     * @param string $reason     Reason.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendEndMainFeedRequest(
        $mediaDepth,
        $reason = 'pagination')
    {
        $extra = [
            'reason'                    => $reason,
            'is_background'             => false,
            'last_navigation_module'    => 'feed_timeline',
            'nav_in_transit'            => false,
            'media_depth'               => $mediaDepth,
            'view_info_count'           => 20,
            'num_of_items'              => 20,
            'interaction_events'        => ['scroll'],
            'new_items_delivered'       => true,
            'request_duration'          => mt_rand(1000, 1500),
        ];

        $event = $this->_addEventBody('ig_main_feed_request_succeeded', 'feed_timeline', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send organic like.
     *
     * @param \InstagramAPI\Response\Model\Item $item        The item object.
     * @param string                            $module      'profile', 'feed_contextual_hashtag', 'feed_short_url', 'feed_timeline'.
     * @param string|null                       $hashtagId   The hashtag ID. Only used when 'feed_contextual_hashtag' is used as module.
     * @param string|null                       $hashtagName The hashtag name. Only used when 'feed_contextual_hashtag' is used as module.
     * @param string|null                       $sessionId   Timeline session ID.
     * @param array                             $options     Options to configure the event.
     *                                                       'follow_status', string, 'following' or 'not_following'.
     *                                                       'hashtag_follow_status', string, 'following' or 'not_following'.
     *                                                       'hashtag_feed_type', string, 'top', 'recent'.
     * @param bool                              $unlike      Wether to send organic like or unlike.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicLike(
        $item,
        $module,
        $hashtagId = null,
        $hashtagName = null,
        $sessionId = null,
        array $options = [],
        $unlike = false)
    {
        if ($module === 'feed_contextual_profile' || $module === 'profile' || $module === 'feed_short_url' || $module === 'feed_contextual_location') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'session_id'                => $sessionId,
                'source_of_action'          => $module,
                'follow_status'             => isset($options['follow_status']) ? $options['follow_status'] : 'not_following',
                'm_ix'                      => isset($options['m_ix']) ? $options['m_ix'] : 7,
                'source_of_like'            => isset($options['source_of_like']) ? $options['source_of_like'] : 'button',
                'entity_page_id'            => $item->getUser()->getPk(),
                'entity_page_name'          => $item->getUser()->getUsername(),
                'media_thumbnail_section'   => 'grid',
                'is_acp_delivered'          => false,
            ];
        } elseif ($module === 'feed_contextual_hashtag') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => isset($options['following']) ? 'following' : 'not_following',
                'm_ix'                      => 30, // ?
                'source_of_like'            => isset($options['source_of_like']) ? $options['source_of_like'] : 'button',
                'hashtag_follow_status'     => isset($options['hashtag_follow']) ? 'following' : 'not_following',
                'hashtag_feed_type'         => isset($options['feed_type']) ? $options['feed_type'] : 'top',
                'tab_index'                 => isset($options['tab_index']) ? $options['tab_index'] : 0,
                'hashtag_id'                => $hashtagId,
                'hashtag_name'              => $hashtagName,
            ];
        } elseif ($module === 'feed_timeline') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'session_id'                => $sessionId,
                'source_of_action'          => $module,
                'follow_status'             => 'following',
                'm_ix'                      => isset($options['m_ix']) ? $options['m_ix'] : 2, // ?
                'inventory_source'          => 'media_or_ad',
                'source_of_like'            => isset($options['source_of_like']) ? $options['source_of_like'] : 'button',
                'is_eof'                    => false,
            ];
        } elseif ($module === 'feed_contextual_chain') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => isset($options['following']) ? 'following' : 'not_following',
                'connection_id'             => '180',
                'imp_logger_ver'            => 16,
                'timespent'                 => $options['timespent'],
                'avgViewPercent'            => 1,
                'maxViewPercent'            => 1,
                'chaining_position'         => $options['feed_position'],
                'chaining_session_id'       => $options['chaining_session_id'],
                'm_ix'                      => 0,
                'topic_cluster_id'          => $options['topic_cluster_id'], // example: 'explore_all:0'
                'topic_cluster_title'       => $options['topic_cluster_title'], // example: 'For You'
                'topic_cluster_type'        => $options['topic_cluster_type'], // example: 'explore_all'
                'topic_cluster_debug_info'	 => null,
                'topic_cluster_session_id'	 => $options['topic_cluster_session_id'],
            ];
        } else {
            throw new \InvalidArgumentException('Module not supported.');
        }

        if ($unlike === false) {
            $name = 'instagram_organic_like';
        } else {
            $name = 'instagram_organic_unlike';
        }

        $event = $this->_addEventBody($name, $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send organic comment.
     *
     * NOTE: After using this event you need to send comment impression on your own comment.
     *       Use sendCommentImpression().
     *
     * @param \InstagramAPI\Response\Model\Item $item            The item object.
     * @param bool                              $isFollowingUser If you are following the user that owns the media.
     * @param int                               $composeDuration The time in milliseconds it took to compose the comment.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendOrganicComment(
        $item,
        $isFollowingUser,
        $composeDuration)
    {
        $extra = [
            'm_pk'                      => $item->getId(),
            'a_pk'                      => $item->getUser()->getPk(),
            'm_ts'                      => (int) $item->getTakenAt(),
            'm_t'                       => $item->getMediaType(),
            'tracking_token'            => $item->getOrganicTrackingToken(),
            'source_of_action'          => 'comments_v2',
            'follow_status'             => $isFollowingUser ? 'following' : 'not_following',
            'comment_compose_duration'  => $composeDuration,
            'media_thumbnail_section'   => 'grid',
            'entity_page_name'          => $item->getUser()->getUsername(),
            'entity_page_id'            => $item->getUser()->getPk(),
        ];

        $event = $this->_addEventBody('instagram_organic_comment', 'comments_v2', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send organic comment like.
     *
     * @param \InstagramAPI\Response\Model\Item $item      The item object.
     * @param string                            $userId    User ID of account who made the comment in Instagram's internal format.
     * @param string                            $commentId Comment ID in Instagram's internal format.
     * @param string                            $sessionId UUID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendOrganicCommentLike(
        $item,
        $userId,
        $commentId,
        $sessionId)
    {
        $extra = [
            'm_pk'                      => $item->getId(),
            'a_pk'                      => $item->getUser()->getPk(),
            'm_ts'                      => (int) $item->getTakenAt(),
            'm_t'                       => $item->getMediaType(),
            'c_pk'                      => $commentId,
            'ca_pk'                     => $userId,
            'inventory_source'          => null,
            'is_media_organic'          => true,
            'session_id'                => $sessionId,
            'm_x'                       => 0,
        ];

        $event = $this->_addEventBody('instagram_organic_comment_like', 'comments_v2', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send comment create.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendCommentCreate()
    {
        $extra = [
            'source_of_action'  => 'comment_create',
            'text_language'     => null,
            'is_offensive'      => false,
        ];

        $event = $this->_addEventBody('instagram_wellbeing_warning_system_success_creation', 'comments_v2', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send comment impression.
     *
     * Whenever you see a comment, a comment impression is sent.
     *
     * @param \InstagramAPI\Response\Model\Item $item             The item object.
     * @param string                            $userId           User ID of account who made the comment in Instagram's internal format.
     * @param string                            $commentId        Comment ID in Instagram's internal format.
     * @param int                               $commentLikeCount The number of likes the comment has.
     * @param string                            $module           Module.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendCommentImpression(
        $item,
        $userId,
        $commentId,
        $commentLikeCount,
        $module = 'comments_v2')
    {
        $extra = [
            'm_pk'              => $item->getId(),
            'a_pk'              => $item->getUser()->getPk(),
            'c_pk'              => $commentId,
            'like_count'        => $commentLikeCount,
            'ca_pk'             => $userId,
            'is_media_organic'  => true,
            'imp_logger_ver'    => 16,
        ];

        if ($module === 'feed_timeline') {
            $extra['session_id'] = $this->ig->client->getPigeonSession();
        }

        $event = $this->_addEventBody('comment_impression', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send profile action.
     *
     * @param string $action   'follow', 'unfollow', 'tap_follow_sheet', 'mute_feed_posts',
     *                         'unmute_feed_posts', 'mute_stories', 'unmute_stories'.
     * @param string $userId   User ID in Instagram's internal format.
     * @param array  $navstack Array to tell Instagram how we reached the user profile.
     *                         You should set your own navstack. As an example it is added
     *                         a navstack that emulates going from feed_timeline to the explore module,
     *                         search for a user and click on the result.
     * @param bool   $options  Options.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendProfileAction(
        $action,
        $userId,
        $navstack,
        array $options = [])
    {
        $actions = [
            'follow',
            'unfollow',
            'edit_profile',
            'tap_followers',
            'tap_follow_sheet',
            'tap_follow_details',
            'mute_feed_posts',
            'unmute_feed_posts',
            'mute_stories',
            'unmute_stories',
            'tap_grid_post',
            'about_this_account',
            'tap_suggested_user_profile',
            'turn_on_post_notifications',
            'turn_off_post_notifications',
            'turn_on_story_notifications',
            'turn_off_story_notifications',
            'tap_profile_pic',
            'notifications_entry_point_impression',
            'block_tap',
            'block_confirm',
        ];

        if (!in_array($action, $actions)) {
            throw new \InvalidArgumentException(sprintf('%s action is not valid.', $action));
        }

        $extra = [];

        switch ($action) {
            case 'tap_profile_pic':
                $followStatus = empty($options['follow_status']) ? 'not_following' : 'following';
                $module = 'profile';
                $clickpoint = 'user_profile_header';
                $extra['media_id_attribution'] = null;
                $extra['media_tracking_token_attribution'] = null;
                break;
            case 'follow':
                $followStatus = 'not_following';
                $clickpoint = isset($options['click_point']) ? $options['click_point'] : 'button_tray';
                $module = 'profile';
                break;
            case 'unfollow':
                $followStatus = 'following';
                $clickpoint = isset($options['click_point']) ? $options['click_point'] : 'button_tray';
                $module = 'profile';
                break;
            case 'edit_profile':
                $followStatus = 'self';
                $clickpoint = 'user_profile_header';
                $module = 'self_profile';
                break;
            case 'mute_feed_posts':
            case 'unmute_feed_posts':
            case 'mute_stories':
            case 'unmute_stories':
                $followStatus = 'following';
                $clickpoint = 'following_sheet';
                $module = 'media_mute_sheet';
                break;
            case 'tap_grid_post':
                $followStatus = isset($options['follow_status']) ? $options['follow_status'] : 'not_following';
                $clickpoint = 'grid_tab';
                $module = 'profile';
                break;
            case 'about_this_account':
                $followStatus = isset($options['follow_status']) ? $options['follow_status'] : 'not_following';
                $clickpoint = 'more_menu';
                $module = 'profile';
                break;
            case 'tap_suggested_user_profile':
                $followStatus = isset($options['follow_status']) ? $options['follow_status'] : 'not_following';
                $clickpoint = 'suggested_users_unit';
                $module = 'profile';
                break;
            case 'tap_follow_details':
                $followStatus = isset($options['follow_status']) ? $options['follow_status'] : 'not_following';
                $clickpoint = 'user_profile_header';
                $module = ($options['module'] === 'self') ? 'self_profile' : 'profile';
                break;
            case 'tap_followers':
                $followStatus = isset($options['follow_status']) ? $options['follow_status'] : 'not_following';
                $clickpoint = 'swipe';
                $module = ($options['module'] === 'self') ? 'self_unified_follow_lists' : 'unified_follow_lists';
                break;
            case 'tap_follow_sheet':
                $clickpoint = 'button_tray';
                $module = 'profile';
                $followStatus = 'following';
                if (isset($options['media_id_attribution']) && isset($options['media_tracking_token_attribution'])) {
                    $extra['media_id_attribution'] = $options['media_id_attribution'];
                    $extra['media_tracking_token_attribution'] = $options['media_tracking_token_attribution'];
                }
                break;
            case 'turn_on_post_notifications':
            case 'turn_off_post_notifications':
            case 'turn_on_story_notifications':
            case 'turn_off_story_notifications':
                $followStatus = 'following';
                $clickpoint = 'following_sheet';
                $module = 'media_notifications_sheet';
                break;
            case 'notifications_entry_point_impression':
                $followStatus = 'following';
                $clickpoint = 'user_profile_header';
                $module = 'profile';
                break;
            case 'block_tap':
            case 'block_confirm':
                $followStatus = 'following';
                $clickpoint = 'profile';
                $module = 'profile';
                $extra['request_id'] = $options['request_id'];
                $extra['direct_thread_id'] = null;
                $extra['profile_user_type'] = 0;
                break;
        }

        $extra['action'] = $action;
        $extra['follow_status'] = $followStatus;
        $extra['profile_user_id'] = $userId;
        $extra['navstack'] = json_encode($navstack);
        $extra['click_point'] = $clickpoint;

        $event = $this->_addEventBody('ig_profile_action', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send report user event (block).
     *
     * @param string $userId User ID of account who made the comment in Instagram's internal format.
     * @param string $action 'open_user_overflow', 'block_or_unblock_user'
     * @param string $module Module.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendUserReport(
        $userId,
        $action,
        $module = 'profile')
    {
        $extra = [
            'actor_id'         => $this->ig->account_id,
            'action'           => $action,
            'target_id'        => $userId,
        ];

        switch ($action) {
            case 'open_user_overflow':
                break;
            case 'block_or_unblock_user':
                $extra['follow_status'] = 'followstatusnotfollowing';
                $extra['nav_stack_depth'] = -1;
                $extra['nav_stack'] = null;
                break;
        }

        $event = $this->_addEventBody('report_user', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send Phone ID.
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param string $type        Type. 'request' or 'response'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendPhoneId(
        $waterfallId,
        $startTime,
        $type)
    {
        if (($type !== 'request') && ($type !== 'response')) {
            throw new \InvalidArgumentException(sprintf('Invalid request type %s.', $type));
        }

        $name = ($type === 'request') ? 'send_phone_id_request' : 'phone_id_response_received';
        $currentTime = round(microtime(true) * 1000);
        $extra = [
             'waterfall_id'         => $waterfallId,
             'start_time'           => $startTime,
             'current_time'         => $currentTime,
             'elapsed_time'         => $currentTime - $startTime,
             'os_version'           => $this->ig->device->getAndroidVersion(),
             'fb_family_device_id'  => $this->ig->phone_id,
             'guid'                 => $this->ig->uuid,
             'prefill_type'         => 'both',
         ];

        $event = $this->_addEventBody($name, 'waterfall_log_in', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send IGTV notification preference.
     *
     * @param bool $enable Enable or disable IGTV notifications.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendIGTvNotificationPreference(
       $enable = true)
    {
        $extra = [
            'elapsed_time_since_last_item'  => -1,
        ];

        $name = $enable ? 'igtv_notification_add' : 'igtv_notification_remove';

        $event = $this->_addEventBody($name, 'media_notifications_sheet', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send unfollow successful.
     *
     * @param string $userId        User ID of account who made the comment in Instagram's internal format.
     * @param bool   $userIsPrivate User is private.
     * @param string $module        Module.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendUnfollowSuccessful(
        $userId,
        $userIsPrivate,
        $module)
    {
        $extra = [
            'target_id'             => $userId,
            'target_is_private'     => $userIsPrivate,
            'entity_id'             => $userId,
            'entity_type'           => 'user',
            'entity_follow_status'  => 'not_following',
        ];

        $event = $this->_addEventBody('unfollow_successful', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send remove follower.
     *
     * @param string $userId User ID of account who made the comment in Instagram's internal format.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendRemoveFollowerConfirmed(
        $userId)
    {
        $extra = [
            'target_id'             => $userId,
        ];

        $event = $this->_addEventBody('remove_follower_dialog_confirmed', 'self_followers', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send organic media impression.
     *
     * @param \InstagramAPI\Response\Model\Item $item    The item object.
     * @param string                            $module  'profile', 'reel_feed_timeline', 'feed_short_url', 'feed_contextual_profile'.
     * @param array                             $options Options to configure the event.
     *                                                   'following', string, 'following' or 'not_following'.'.
     *                                                   'story_ranking_token' UUIDv4. Used on module 'reel_feed_timeline'.
     *                                                   'viewer_session_id' UUIDv4. Used on module 'reel_feed_timeline'.
     *                                                   'tray_session_id' UUIDv4. Used on module 'reel_feed_timeline'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicMediaImpression(
        $item,
        $module,
        array $options = [])
    {
        if ($module === 'profile' || $module === 'feed_short_url'
            || $module === 'feed_contextual_profile' || $module === 'feed_contextual_self_profile' || $module === 'feed_timeline' || $module === 'reel_follow_list') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => isset($options['following']) ? 'following' : 'not_following',
                'm_ix'                      => 3, // ?
                'imp_logger_ver'            => 16,
                'is_app_backgrounded'       => 'false',
                'nav_in_transit'            => 0,
                'is_acp_delivered'          => false,
            ];
            if ($module === 'feed_short_url' || $module === 'feed_contextual_profile') {
                $extra['media_thumbnail_section'] = 'grid';
                $extra['entity_page_name'] = $item->getUser()->getUsername();
                $extra['entity_page_id'] = $item->getUser()->getPk();
            } elseif ($module === 'feed_timeline') {
                $extra['delivery_flags'] = 'n,c';
                $extra['session_id'] = $this->ig->client->getPigeonSession();
                if (isset($options['feed_request_id'])) {
                    $extra['feed_request_id'] = $options['feed_request_id'];
                }
            }
        } elseif ($module === 'feed_contextual_hashtag') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'hashtag_id'                => $options['hashtag_id'],
                'hashtag_name'              => $options['hashtag_name'],
                'hashtag_follow_status'     => isset($options['following']) ? 'following' : 'not_following',
                'hashtag_feed_type'         => isset($options['feed_type']) ? $options['feed_type'] : 'top',
                'tab_index'                 => isset($options['tab_index']) ? $options['tab_index'] : 0,
                'source_of_action'          => $module,
                'session_id'                => $this->ig->client->getPigeonSession(),
                'media_type'                => $item->getMediaType(),
                'type'                      => 0,
                'section'                   => 0,
                'position'                  => isset($options['position']) ? $options['position'] : '["0","0"]',
            ];
        } elseif ($module === 'feed_contextual_location') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => isset($options['following']) ? 'following' : 'not_following',
                'm_ix'                      => 3, // ?
                'imp_logger_ver'            => 16,
                'is_app_backgrounded'       => 'false',
                'nav_in_transit'            => 0,
                'entity_type'               => 'place',
                'entity_name'               => $item->getUser()->getUsername(),
                'entity_page_name'          => $item->getUser()->getUsername(),
                'entity_page_id'            => $item->getUser()->getPk(),
                'entity_id'                 => $item->getUser()->getPk(),
                'is_acp_delivered'          => false,
            ];
        } elseif (
            $module === 'reel_feed_timeline'
            || $module === 'reel_profile'
            || $module === 'reel_follow_list'
            || $module === 'reel_liker_list'
            || $module === 'reel_hashtag_feed'
            || $module === 'reel_location_feed'
            || $module === 'reel_comment') {
            if (!isset($options['story_ranking_token']) && !isset($options['tray_session_id']) && !isset($options['viewer_session_id'])) {
                throw new \InvalidArgumentException('Required options were not set.');
            }
            $extra = [
                'm_pk'                          => $item->getId(),
                'a_pk'                          => $item->getUser()->getPk(),
                'm_ts'                          => (int) $item->getTakenAt(),
                'm_t'                           => $item->getMediaType(),
                'tracking_token'                => $item->getOrganicTrackingToken(),
                'action'                        => 'webclick',
                'source_of_action'              => 'reel_feed_timeline',
                'follow_status'                 => isset($options['following']) ? $options['following'] : 'not_following',
                'elapsed_time_since_last_item'  => -1,
                'viewer_session_id'             => $options['viewer_session_id'],
                'tray_session_id'               => $options['tray_session_id'],
                'reel_id'                       => $item->getUser()->getPk(),
                'is_pride_reel'                 => false,
                'is_besties_reel'               => false,
                'reel_position'                 => 0,
                'reel_viewer_position'          => 0,
                'reel_type'                     => 'story',
                'reel_size'                     => isset($options['reel_size']) ? $options['reel_size'] : 0,
                'tray_position'                 => 0,
                'is_video_to_carousel'          => false,
                'session_reel_counter'          => 1,
                'time_elapsed'                  => 0,
                'reel_start_position'           => 0,
                'is_dark_mode'                  => 0,
                'dark_mode_state'               => -1,
                'is_acp_delivered'              => false,
            ];

            if (!empty($options['story_ranking_token'])) {
                $extra['story_ranking_token'] = $options['story_ranking_token'];
            }
        } else {
            throw new \InvalidArgumentException('Module not supported.');
        }

        if ($item->getMediaType() === 8) {
            $event = 'instagram_organic_carousel_viewed_impression';
            $extra['carousel_index'] = isset($options['carousel_index']) ? $options['carousel_index'] : 0;
            $extra['carousel_media_id'] = isset($options['carousel_index']) ? $item->getCarouselMedia()[$options['carousel_index']]->getId() : $item->getCarouselMedia()[0]->getId();
            $extra['carousel_m_t'] = isset($options['carousel_index']) ? $item->getCarouselMedia()[$options['carousel_index']]->getMediaType() : $item->getCarouselMedia()[0]->getMediaType();
            $extra['carousel_size'] = $item->getCarouselMediaCount();
        }

        $extra['nav_in_transit'] = 0;
        $extra['last_navigation_module'] = $module;
        $extra['nav_chain'] = $this->ig->getNavChain();

        $event = $this->_addEventBody('instagram_organic_impression', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send organic media sub impression.
     *
     * @param \InstagramAPI\Response\Model\Item $item    The item object.
     * @param array                             $options Options to configure the event.
     *                                                   'following', string, 'following' or 'not_following'.'.
     *                                                   'story_ranking_token' UUIDv4. Used on module 'reel_feed_timeline'.
     *                                                   'viewer_session_id' UUIDv4. Used on module 'reel_feed_timeline'.
     *                                                   'tray_session_id' UUIDv4. Used on module 'reel_feed_timeline'.
     * @param mixed                             $module
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicMediaSubImpression(
        $item,
        array $options = [],
        $module = 'reel_profile')
    {
        if (!isset($options['tray_session_id']) && !isset($options['viewer_session_id'])) {
            throw new \InvalidArgumentException('Required options were not set.');
        }

        $extra = [
            'm_pk'                         => $item->getId(),
            'a_pk'                         => $item->getUser()->getPk(),
            'm_ts'                         => (int) $item->getTakenAt(),
            'm_t'                          => $item->getMediaType(),
            'tracking_token'               => $item->getOrganicTrackingToken(),
            'source_of_action'             => $module,
            'follow_status'                => empty($options['following']) ? 'not_following' : 'following',
            'elapsed_time_since_last_item' => -1,
            'viewer_session_id'            => $options['viewer_session_id'],
            'tray_session_id'              => $options['tray_session_id'],
            'reel_id'                      => $item->getUser()->getPk(),
            'reel_position'                => isset($options['reel_position']) ? $options['reel_position'] : 0,
            'reel_viewer_position'         => 0,
            'reel_type'                    => 'story',
            'reel_size'                    => isset($options['reel_size']) ? $options['reel_size'] : 0,
            'is_video_to_carousel'         => false,
            'tray_position'                => 1,
            'session_reel_counter'         => 1,
            'time_elapsed'                 => mt_rand(5, 6) + mt_rand(100, 900) * 0.001,
            'reel_start_position'          => 0,
            'is_dark_mode'                 => 0,
            'dark_mode_state'              => -1,
            'is_acp_delivered'             => false,
        ];

        $event = $this->_addEventBody('instagram_organic_sub_impression', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send organic media Vpvd impression.
     *
     * @param \InstagramAPI\Response\Model\Item $item    The item object.
     * @param array                             $options Options to configure the event.
     *                                                   'following', string, 'following' or 'not_following'.'.
     *                                                   'story_ranking_token' UUIDv4. Used on module 'reel_feed_timeline'.
     *                                                   'viewer_session_id' UUIDv4. Used on module 'reel_feed_timeline'.
     *                                                   'tray_session_id' UUIDv4. Used on module 'reel_feed_timeline'.
     * @param mixed                             $module
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicVpvdImpression(
        $item,
        array $options = [],
        $module = 'reel_profile')
    {
        if (!isset($options['tray_session_id']) && !isset($options['viewer_session_id'])) {
            throw new \InvalidArgumentException('Required options were not set.');
        }

        $max = mt_rand(1000, 2500);

        $extra = [
            'm_pk'                      => $item->getId(),
            'reel_id'                   => $item->getUser()->getPk(),
            'tray_position'             => 1,
            'reel_size'                 => isset($options['reel_size']) ? $options['reel_size'] : 0,
            'reel_position'             => isset($options['reel_position']) ? $options['reel_position'] : 0,
            'reel_type'                 => 'story',
            'tracking_token'            => $item->getOrganicTrackingToken(),
            'm_t'                       => $item->getMediaType(),
            'time_elapsed'              => isset($options['time_elapsed']) ? $options['time_elapsed'] : 0,
            'time_remaining'            => mt_rand(1, 2),
            'time_paused'               => 0,
            'client_sub_impression'     => isset($options['client_sub_impression']) ? true : false,
            'is_media_loaded'           => true,
            'is_highlights_sourced'     => false,
            'story_ranking_token'       => null,
            'max_duration_ms'           => $max,
            'sum_duration_ms'           => $max,
            'legacy_duration_ms'        => $max,
            'imp_logger_ver'            => 16,
            'time_to_load'              => 0,
        ];

        $event = $this->_addEventBody('instagram_organic_vpvd_imp', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send organic carousel impression.
     *
     * @param \InstagramAPI\Response\Model\Item $item      The item object.
     * @param string                            $requestId UUID.
     * @param array                             $options   Options to configure the event.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicCarouselImpression(
        $item,
        $requestId,
        array $options = [])
    {
        $extra = [
            'm_pk'                              => $item->getId(),
            'a_pk'                              => $item->getUser()->getPk(),
            'm_ts'                              => (int) $item->getTakenAt(),
            'm_t'                               => $item->getMediaType(),
            'tracking_token'                    => $item->getOrganicTrackingToken(),
            'source_of_action'                  => isset($options['module']) ? $options['module'] : 'feed_timeline',
            'follow_status'                     => isset($options['following']) ? 'following' : 'not_following',
            'm_ix'                              => 0,
            'carousel_index'                    => isset($options['carousel_index']) ? $options['carousel_index'] : 0,
            'carousel_media_id'                 => isset($options['carousel_index']) ? $item->getCarouselMedia()[$options['carousel_index']]->getId() : $item->getCarouselMedia()[0]->getId(),
            'carousel_m_t'                      => isset($options['carousel_index']) ? $item->getCarouselMedia()[$options['carousel_index']]->getMediaType() : $item->getCarouselMedia()[0]->getMediaType(),
            'carousel_size'                     => $item->getCarouselMediaCount(),
            'inventory_source'                  => 'media_or_ad',
            'feed_request_id'                   => $requestId,
            'delivery_flags'                    => 'n',
            'elapsed_time_since_last_item'      => -1.0,
            'is_eof'                            => false,
            'imp_logger_ver'                    => 24,
            'is_acp_delivered'                  => false,
        ];

        $event = $this->_addEventBody('instagram_organic_carousel_impression', 'feed_timeline', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send reel playback navigation.
     *
     * @param \InstagramAPI\Response\Model\Item $item            The item object.
     * @param string                            $viewerSessionId UUID.
     * @param string                            $traySessionId   UUID.
     * @param string                            $rankingToken    UUID.
     * @param string                            $module          Module.
     * @param array                             $options         Options to configure the event.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendReelPlaybackNavigation(
        $item,
        $viewerSessionId,
        $traySessionId,
        $rankingToken,
        $module = 'reel_feed_timeline',
        array $options = [])
    {
        $extra = [
            'm_pk'                              => $item->getId(),
            'a_pk'                              => $item->getUser()->getPk(),
            'm_ts'                              => (int) $item->getTakenAt(),
            'm_t'                               => $item->getMediaType(),
            'tracking_token'                    => $item->getOrganicTrackingToken(),
            'action'					                       => isset($options['action']) ? $options['action'] : 'tap_forward',
            'elapsed_time_since_last_item'      => isset($options['elapsed_time_since_last_item']) ? $options['elapsed_time_since_last_item'] : -1,
            'source_of_action'                  => $module,
            'follow_status'                     => isset($options['following']) ? 'following' : 'not_following',
            'viewer_session_id'                 => $viewerSessionId,
            'tray_session_id'                   => $traySessionId,
            'reel_tray_resorted_on_client_side' => false,
            'has_media_loaded'                  => isset($options['has_media_loaded']) ? $options['has_media_loaded'] : false,
            'tap_x_position'                    => isset($options['tap_x_position']) ? $options['tap_x_position'] : 1201.1658935546875,
            'tap_y_position'                    => isset($options['tap_y_position']) ? $options['tap_y_position'] : 1081.6331787109375,
            'reel_id'                           => $item->getId(),
            'reel_position'                     => isset($options['reel_position']) ? $options['reel_position'] : 1,
            'reel_viewer_position'              => isset($options['reel_viewer_position']) ? $options['reel_viewer_position'] : 0,
            'reel_type'                         => 'story',
            'reel_size'                         => isset($options['reel_size']) ? $options['reel_size'] : 1,
            'tray_position'                     => isset($options['tray_position']) ? $options['tray_position'] : 1,
            'session_reel_counter'              => isset($options['session_reel_counter']) ? $options['session_reel_counter'] : 1,
            'time_elapsed'                      => isset($options['time_elapsed']) ? $options['time_elapsed'] : 0,
            'reel_start_position'               => isset($options['reel_start_position']) ? $options['reel_start_position'] : 0,
            'profile_tap_counter'               => 0,
            'election_tap_counter'              => 0,
            'anti_bully_tap_counter'            => 0,
            'source'                            => 1,
            'story_ranking_token'               => $rankingToken,
            'first_view'                        => isset($options['first_view']) ? $options['first_view'] : '1',
            'source_module'                     => isset($options['source_module']) ? $options['source_module'] : 'reel_feed_timeline',
            'dest_module'                       => isset($options['dest_module']) ? $options['dest_module'] : 'reel_feed_timeline',
            'a_i'                               => 'organic',
            'is_dark_mode'                      => 0,
            'dark_mode_state'                   => -1,
            'is_acp_delivered'                  => false,
        ];

        if ($item->getMediaType() == 2) {
            $extra['has_playable_audio'] = $item->getHasAudio();
            $extra['viewer_volume_on'] = true;
        }

        if (isset($options['action']) && $options['action'] === 'tap_exit') {
            $name = 'reel_playback_exit';
        } else {
            $name = 'reel_playback_navigation';
        }

        $event = $this->_addEventBody($name, $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send reel session summary.
     *
     * This event must be sent after 'reel_playback_navigation' event.
     *
     * @param \InstagramAPI\Response\Model\Item $item            The item object.
     * @param string                            $viewerSessionId UUID.
     * @param string                            $traySessionId   UUID.
     * @param string                            $rankingToken    UUID.
     * @param string                            $module          Module.
     * @param array                             $options         Options to configure the event.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendReelSessionSummary(
        $item,
        $viewerSessionId,
        $traySessionId,
        $rankingToken,
        $module = 'reel_feed_timeline',
        array $options = [])
    {
        $extra = [
            'a_pk'                                  => $item->getUser()->getPk(),
            'action'					                           => isset($options['action']) ? $options['action'] : 'tap_forward',
            'elapsed_time_since_last_item'          => isset($options['elapsed_time_since_last_item']) ? $options['elapsed_time_since_last_item'] : -1,
            'source_of_action'                      => $module,
            'follow_status'                         => isset($options['following']) ? 'following' : 'not_following',
            'viewer_session_id'                     => $viewerSessionId,
            'tray_session_id'                       => $traySessionId,
            'story_ranking_token'                   => $rankingToken,
            'reel_type'                             => 'story',
            'reel_size'                             => isset($options['reel_size']) ? $options['reel_size'] : 1,
            'tray_position'                         => isset($options['tray_position']) ? $options['tray_position'] : 1,
            'session_reel_counter'                  => isset($options['session_reel_counter']) ? $options['session_reel_counter'] : 1,
            'pause_duration'                        => isset($options['pause_duration']) ? $options['pause_duration'] : 0,
            'time_elapsed'                          => isset($options['time_elapsed']) ? $options['time_elapsed'] : 0,
            'ad_pause_duration'                     => 0,
            'ad_time_elapsed'                       => 0,
            'viewer_session_media_consumed'         => isset($options['viewer_session_media_consumed']) ? $options['viewer_session_media_consumed'] : 0,
            'viewer_session_reels_consumed'         => isset($options['viewer_session_reels_consumed']) ? $options['viewer_session_reels_consumed'] : 0,
            'photos_consumed'                       => isset($options['photos_consumed']) ? $options['photos_consumed'] : 0,
            'videos_consumed'                       => isset($options['videos_consumed']) ? $options['videos_consumed'] : 0,
            'viewer_session_ad_media_consumed'      => 0,
            'viewer_session_ad_reels_consumed'      => 0,
            'viewer_session_netego_reels_consumed'  => 0,
            'viewer_session_replay_videos_consumed' => isset($options['viewer_session_replay_videos_consumed']) ? $options['viewer_session_replay_videos_consumed'] : 0,
            'viewer_session_live_reels_consumed'    => isset($options['viewer_session_live_reels_consumed']) ? $options['viewer_session_live_reels_consumed'] : 0,
            'viewer_session_replay_reels_consumed'  => isset($options['viewer_session_replay_reels_consumed']) ? $options['viewer_session_replay_reels_consumed'] : 0,
            'ad_photos_consumed'                    => 0,
            'ad_videos_consumed'                    => 0,
            'replay_videos_consumed'                => isset($options['replay_videos_consumed']) ? $options['replay_videos_consumed'] : 0,
            'live_videos_consumed'                  => isset($options['live_videos_consumed']) ? $options['live_videos_consumed'] : 0,
            'viewer_volume_on'                      => isset($options['viewer_volume_on']) ? $options['viewer_volume_on'] : false,
            'viewer_volume_toggled'                 => isset($options['viewer_volume_toggled']) ? $options['viewer_volume_toggled'] : false,
            'is_last_reel'                          => isset($options['is_last_reel']) ? $options['is_last_reel'] : false,
            'is_acp_delivered'                      => isset($options['is_acp_delivered']) ? $options['is_acp_delivered'] : false,
        ];

        $event = $this->_addEventBody('reel_session_summary', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send reel playback entry.
     *
     * @param string $userId          User ID.
     * @param string $viewerSessionId UUID.
     * @param string traySessionId      UUID.
     * @param mixed $traySessionId
     * @param mixed $module
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendReelPlaybackEntry(
        $userId,
        $viewerSessionId,
        $traySessionId,
        $module = 'reel_profile')
    {
        $extra = [
            'a_pk'                                  => $userId,
            'source_of_action'                      => $module,
            'elapsed_time_since_last_item'          => -1,
            'is_live_streaming'                     => 0,
            'is_live_questions'                     => 0,
            'viewer_session_id'                     => $viewerSessionId,
            'tray_session_id'                       => $traySessionId,
            'reel_id'                               => $userId,
            'is_besties_reel'                       => false,
            'reel_type'                             => 'story',
            'tray_position'                         => 0,
            'has_my_reel'                           => 0,
            'new_reel_count'                        => 1,
            'viewed_reel_count'                     => 0,
            'live_reel_count'                       => 0,
            'client_position'                       => 0,
            'viewer_launch_duration'                => mt_rand(1000, 1500),
            'viewer_launch_preload_success'         => 1,
            'is_acp_delivered'                      => false,
        ];

        $event = $this->_addEventBody('reel_playback_entry', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send explore home impression.
     *
     * @param \InstagramAPI\Response\Model\Item $item    The item object.
     * @param array                             $options Options to configure the event.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendExploreHomeImpression(
        $item,
        array $options = [])
    {
        $extra = [
            'm_pk'                      => $item->getId(),
            'media_type'                => $item->getMediaType(),
            'event_id'                  => $item->getId(),
            'tracking_token'            => $item->getOrganicTrackingToken(),
            'connection_id'             => isset($options['connection_id']) ? $options['connection_id'] : 180,
            'position'                  => $options['position'], // [\"24\",\"1\"] (row, column).
            'algorithm'                 => isset($options['algorithm']) ? $options['algorithm'] : 'edge_dedupe_unicorn',
            'type'                      => 1,
            'size'                      => $options['size'], // [\"2\",\"2\"] Size in the media grid.
            'topic_cluster_id'          => $options['topic_cluster_id'], // example: 'explore_all:0'
            'topic_cluster_title'       => $options['topic_cluster_title'], // example: 'For You'
            'topic_cluster_type'        => $options['topic_cluster_type'], // example: 'explore_all'
            'topic_cluster_debug_info'	 => null,
        ];

        if ($item->hasMezqlToken()) {
            $extra['mezql_token'] = $item->getMezqlToken();
        }

        $event = $this->_addEventBody('explore_home_impression', 'explore_popular', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send explore switch.
     *
     * @param string $sessionId UUIDv4.
     * @param array  $options   Options to configure the event.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendExploreSwitch(
        $sessionId,
        array $options = [])
    {
        $extra = [
            'topic_nav_order'                      => isset($options['topic_nav_order']) ? $options['topic_nav_order'] : 0,
            'dest_topic_cluster_position'          => isset($options['dest_topic_cluster_position']) ? $options['dest_topic_cluster_position'] : 0,
            'dest_topic_cluster_debug_info'        => null,
            'dest_topic_cluster_type'              => isset($options['dest_topic_cluster_type']) ? $options['dest_topic_cluster_type'] : 'explore_all',
            'dest_topic_cluster_title'             => isset($options['dest_topic_cluster_title']) ? $options['dest_topic_cluster_title'] : 'For+You',
            'dest_topic_cluster_id'	               => isset($options['dest_topic_cluster_id']) ? $options['dest_topic_cluster_id'] : 'explore_all:0',
            'action'                               => 'load',
            'session_id'                           => $sessionId,
        ];

        $event = $this->_addEventBody('explore_topic_switch', 'explore_popular', $extra);
        $this->_addEventData($event);
    }

    /**
     * Prepare and send perf and impressions events.
     *
     * @param \InstagramAPI\Response\Model\Item $item   The item object.
     * @param string                            $module Module.
     * @param mixed                             $items
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function preparePerfWithImpressions(
        $items,
        $module)
    {
        foreach ($items as $item) {
            if ($item->getMediaType() === 1) {
                $imageResponse = $this->ig->request($item->getImageVersions2()->getCandidates()[0]->getUrl());

                if (isset($imageResponse->getHttpResponse()->getHeaders()['x-encoded-content-length'])) {
                    $imageSize = $imageResponse->getHttpResponse()->getHeaders()['x-encoded-content-length'][0];
                } elseif (isset($imageResponse->getHttpResponse()->getHeaders()['Content-Length'])) {
                    $imageSize = $imageResponse->getHttpResponse()->getHeaders()['Content-Length'][0];
                } elseif (isset($imageResponse->getHttpResponse()->getHeaders()['content-length'])) {
                    $imageSize = $imageResponse->getHttpResponse()->getHeaders()['content-length'][0];
                } else {
                    continue;
                }

                $options = [
                    'is_grid_view'                      => true,
                    'rendered'                          => true,
                    'did_fallback_render'               => false,
                    'is_carousel'                       => false,
                    'image_size_kb'                     => $imageSize,
                    'estimated_bandwidth'               => mt_rand(1000, 4000),
                    'estimated_bandwidth_totalBytes_b'  => $this->ig->client->totalBytes,
                    'estimated_bandwidth_totalTime_ms'  => $this->ig->client->totalTime,
                ];

                $this->sendPerfPercentPhotosRendered($module, $item->getId(), $options);
            }
            $this->sendThumbnailImpression('instagram_thumbnail_impression', $item, $module);
        }
    }

    /**
     * Prepare and send thumbnail impressions.
     *
     * @param string      $module      'profile', 'feed_timeline' or 'feed_hashtag'.
     * @param string|null $hashtagId   The hashtag ID. Only used when 'feed_hashtag' is used as module.
     * @param string|null $hashtagName The hashtag name. Only used when 'feed_hashtag' is used as module.
     * @param array       $options     Options to configure the event.
     *                                 'position', string, the media position.
     *                                 'following', string, 'following' or 'not_following'.
     *                                 'feed_type', string, 'top', 'recent'.
     * @param mixed       $sections
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function prepareAndSendThumbnailImpression(
        $module,
        $sections,
        $hashtagId = null,
        $hashtagName = null,
        array $options = [])
    {
        $row = 1;
        $column = 1;
        foreach ($sections as $section) {
            if (!$section instanceof \InstagramAPI\Response\Model\Section) {
                throw new \InvalidArgumentException('Not a valid instance of section.');
            }
            switch ($section->getLayoutType()) {
                case 'media_grid':
                    if ($section->getFeedType() === 'media') {
                        foreach ($section->getLayoutContent()->getMedias() as $media) {
                            $options['position'] = json_encode([strval($row - 1), strval($column - 1)]);
                            $option['size'] = json_encode(['1', '1']);
                            $this->sendThumbnailImpression('instagram_thumbnail_impression', $media->getMedia(), $module, $hashtagId, $hashtagName, $options);

                            if ($column % 3 === 0) {
                                $row++;
                                $column = 1;
                            } else {
                                $column++;
                            }
                        }
                    }
                    break;
                case 'two_by_two_right':
                case 'two_by_two_left':
                    if ($section->getLayoutType() === 'two_by_two_right') {
                        $column = 1;
                        $cOffset = 1;
                    }
                    if ($section->getLayoutType() === 'two_by_two_left') {
                        $column = 3;
                        $cOffset = 0;
                    }
                    if ($section->getFeedType() === 'media' || $section->getFeedType() === 'channel') {
                        foreach ($section->getLayoutContent()->getFillItems() as $item) {
                            $options['position'] = json_encode([strval($row - 1), strval($column - 2)]);
                            $option['size'] = json_encode(['1', '1']);
                            $this->sendThumbnailImpression('instagram_thumbnail_impression', $item->getMedia(), $module, $hashtagId, $hashtagName, $options);
                            $row++;
                        }
                        $options['position'] = json_encode([strval($row - 3), strval($cOffset)]);
                        $option['size'] = json_encode(['2', '2']);
                        $this->sendThumbnailImpression('instagram_thumbnail_impression', $section->getLayoutContent()->getTwoByTwoItem()->getChannel()->getMedia(), $module, $hashtagId, $hashtagName, $options);
                        $column = 1;
                    }
                    break;
                case 'one_by_two_right':
                case 'one_by_two_left':
                    if ($section->getLayoutType() === 'one_by_two_right') {
                        $column = 1;
                        $cOffset = 2;
                    }
                    if ($section->getLayoutType() === 'one_by_two_left') {
                        $column = 2;
                        $cOffset = 0;
                    }
                    if ($section->getFeedType() === 'media') {
                        foreach ($section->getLayoutContent()->getFillItems() as $item) {
                            $options['position'] = json_encode([strval($row - 1), strval($column - 2)]);
                            $option['size'] = json_encode(['1', '1']);
                            $this->sendThumbnailImpression('instagram_thumbnail_impression', $item->getMedia(), $module, $hashtagId, $hashtagName, $options);
                            if ($section->getLayoutType() === 'one_by_two_right') {
                                if ($column % 2 === 0) {
                                    $row++;
                                    $column = 1;
                                } else {
                                    $column++;
                                }
                            } else {
                                if ($column % 3 === 0) {
                                    $row++;
                                    $column = 1;
                                } else {
                                    $column++;
                                }
                            }
                        }
                        if ($section->getLayoutContent()->getOneByTwoItem()->getStories() !== null) {
                            $oneByTwoItem = $section->getLayoutContent()->getOneByTwoItem()->getStories()->getSeedReel()->getItems()[0];
                        } else {
                            $oneByTwoItem = $section->getLayoutContent()->getOneByTwoItem()->getClips()->getItems()[0]->getMedia();
                        }
                        $options['position'] = json_encode([strval($row - 1), strval($cOffset)]);
                        $option['size'] = json_encode(['2', '1']);
                        $this->sendThumbnailImpression('instagram_thumbnail_impression', $item->getMedia(), $module, $hashtagId, $hashtagName, $options);
                        $column = 1;
                    }
                    break;
                default:
                    throw new \InstagramAPI\Exception\InstagramException(sprintf('Layout type "%s" not implemented. Data: %s', $section->getLayoutType(), base64_encode($section->asJson())));
            }
        }
    }

    /**
     * Prepare and send explore impressions.
     *
     * @param string $clusterId The cluster ID.
     * @param string $sessionId The session ID.
     * @param array  $sections  Explore module sections.
     *                          Array of \InstagramAPI\Response\Model\Section.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function prepareAndSendExploreImpression(
        $clusterId,
        $sessionId,
        $sections)
    {
        $clusters = [
            'For You'       => 'explore_all:0',
            'Animals'       => 'hashtag_inspired:1',
            'Style'         => 'hashtag_inspired:26',
            'Comics'        => 'hashtag_inspired:20',
            'Travel'        => 'hashtag_inspired:28',
            'Architecture'  => 'hashtag_inspired:18',
            'Beauty'        => 'hashtag_inspired:3',
            'DIY'           => 'hashtag_inspired:21',
            'Auto'          => 'hashtag_inspired:19',
            'Music'         => 'hashtag_inspired:11',
            'Nature'        => 'hashtag_inspired:24',
            'Decor'         => 'hashtag_inspired:5',
            'Dance'         => 'hashtag_inspired:22',
        ];

        if (!in_array($clusterId, $clusters)) {
            throw new \InvalidArgumentException('Not a valid instance of cluster ID.');
        }

        $row = 1;
        $column = 1;
        foreach ($sections as $section) {
            if (!$section instanceof \InstagramAPI\Response\Model\Section) {
                throw new \InvalidArgumentException('Not a valid instance of section.');
            }
            switch ($section->getLayoutType()) {
                case 'media_grid':
                    if ($section->getFeedType() === 'media') {
                        foreach ($section->getLayoutContent()->getMedias() as $media) {
                            $this->sendExploreHomeImpression($media->getMedia(), [
                                'position'              => json_encode([strval($row - 1), strval($column - 1)]),
                                'size'                  => json_encode(['1', '1']),
                                'topic_cluster_id'      => $clusterId,
                                'topic_cluster_title'   => array_search($clusterId, $clusters),
                                'topic_cluster_type'    => explode($clusterId, ':')[0],
                            ]);
                            if ($column % 3 === 0) {
                                $row++;
                                $column = 1;
                            } else {
                                $column++;
                            }
                        }
                    }
                    break;
                case 'two_by_two_right':
                case 'two_by_two_left':
                    if ($section->getLayoutType() === 'two_by_two_right') {
                        $column = 1;
                        $cOffset = 1;
                    }
                    if ($section->getLayoutType() === 'two_by_two_left') {
                        $column = 3;
                        $cOffset = 0;
                    }
                    if ($section->getFeedType() === 'media' || $section->getFeedType() === 'channel') {
                        foreach ($section->getLayoutContent()->getFillItems() as $item) {
                            $this->sendExploreHomeImpression($item->getMedia(), [
                                'position'              => json_encode([strval($row - 1), strval($column - 1)]),
                                'size'                  => json_encode(['1', '1']),
                                'topic_cluster_id'      => $clusterId,
                                'topic_cluster_title'   => array_search($clusterId, $clusters),
                                'topic_cluster_type'    => explode($clusterId, ':')[0],
                            ]);
                            $row++;
                        }
                        if ($section->getFeedType() === 'media') {
                            $item = $section->getLayoutContent()->getTwoByTwoItem()->getMedia();
                        } else {
                            $item = $section->getLayoutContent()->getTwoByTwoItem()->getChannel()->getMedia();
                        }
                        $this->sendExploreHomeImpression($item, [
                            'position'              => json_encode([strval($row - 3), strval($cOffset)]),
                            'size'                  => json_encode(['2', '2']),
                            'topic_cluster_id'      => $clusterId,
                            'topic_cluster_title'   => array_search($clusterId, $clusters),
                            'topic_cluster_type'    => explode($clusterId, ':')[0],
                        ]);
                        $column = 1;
                    }
                    break;
                case 'one_by_two_right':
                case 'one_by_two_left':
                    if ($section->getLayoutType() === 'one_by_two_right') {
                        $column = 1;
                        $cOffset = 2;
                    }
                    if ($section->getLayoutType() === 'one_by_two_left') {
                        $column = 2;
                        $cOffset = 0;
                    }
                    if ($section->getFeedType() === 'media') {
                        foreach ($section->getLayoutContent()->getFillItems() as $item) {
                            $this->sendExploreHomeImpression($item->getMedia(), [
                                'position'              => json_encode([strval($row - 1), strval($column - 1)]),
                                'size'                  => json_encode(['1', '1']),
                                'topic_cluster_id'      => $clusterId,
                                'topic_cluster_title'   => array_search($clusterId, $clusters),
                                'topic_cluster_type'    => explode($clusterId, ':')[0],
                            ]);
                            if ($section->getLayoutType() === 'one_by_two_right') {
                                if ($column % 2 === 0) {
                                    $row++;
                                    $column = 1;
                                } else {
                                    $column++;
                                }
                            } else {
                                if ($column % 3 === 0) {
                                    $row++;
                                    $column = 1;
                                } else {
                                    $column++;
                                }
                            }
                        }
                        if ($section->getLayoutContent()->getOneByTwoItem()->getStories() !== null) {
                            $oneByTwoItem = $section->getLayoutContent()->getOneByTwoItem()->getStories()->getSeedReel()->getItems()[0];
                        } else {
                            $oneByTwoItem = $section->getLayoutContent()->getOneByTwoItem()->getClips()->getItems()[0]->getMedia();
                        }
                        $this->sendExploreHomeImpression($oneByTwoItem, [
                            'position'              => json_encode([strval($row - 1), strval($cOffset)]),
                            'size'                  => json_encode(['2', '1']),
                            'topic_cluster_id'      => $clusterId,
                            'topic_cluster_title'   => array_search($clusterId, $clusters),
                            'topic_cluster_type'    => explode($clusterId, ':')[0],
                        ]);
                        $column = 1;
                    }
                    break;
                case 'three_by_four':
                    if ($section->getFeedType() === 'media' || $section->getFeedType() === 'channel') {
                        $item = $section->getLayoutContent()->getThreeByFourItem()->getClips()->getItems()[0]->getMedia();
                        $this->sendExploreHomeImpression($item, [
                            'position'              => json_encode([strval(0), strval(0)]),
                            'size'                  => json_encode(['4', '3']),
                            'topic_cluster_id'      => $clusterId,
                            'topic_cluster_title'   => array_search($clusterId, $clusters),
                            'topic_cluster_type'    => explode($clusterId, ':')[0],
                        ]);
                    }
                    break;
                default:
                    throw new \InstagramAPI\Exception\InstagramException(sprintf('Layout type "%s" not implemented. Data: %s', $section->getLayoutType(), base64_encode($section->asJson())));
            }
        }
    }

    /**
     * Send similar user impression.
     *
     * @param string $userId            User ID.
     * @param string $recommendedUserId Recommended user ID.
     * @param string $module            Module
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendSimilarUserImpression(
        $userId,
        $recommendedUserId,
        $module = 'profile')
    {
        $extra = [
            'uid'           => $recommendedUserId,
            'uid_based_on'  => $userId,
            'view'          => $module,
        ];

        $event = $this->_addEventBody('similar_user_impression', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send recommended user impression.
     *
     * @param Item   $item     Item.
     * @param int    $position Position.
     * @param string $module   Module.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendRecommendedUserImpression(
        $item,
        $position,
        $module = 'newsfeed_you')
    {
        $extra = [
            'position'           => $position,
            'view'               => 'fullscreen',
            'uid'                => explode('|', $item->getUuid())[1],
            'impression_uuid'    => $item->getUuid(),
            'algorithm'          => $item->getAlgorithm(),
            'social_context'     => $item->getSocialContext(),
            'inlined'            => false,
        ];

        $event = $this->_addEventBody('recommended_user_impression', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send similar entity impression.
     *
     * @param string $userId            User ID.
     * @param string $recommendedUserId Recommended user ID.
     * @param string $entityType        Entity type.
     * @param string $module            Module
     * @param array  $options           Options to configure the event.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendSimilarEntityImpression(
        $userId,
        $recommendedUserId,
        $entityType = 'user',
        $module = 'profile',
        array $options = [])
    {
        $extra = [
            'entity_type'               => $entityType,
            'entity_id'                 => $recommendedUserId,
            'based_on_id'               => $userId,
            'based_on_type'             => $entityType,
            'entity_follow_status'      => isset($options['entity_follow_status']) ? $options['entity_follow_status'] : 'not_following',
            'entity_ix'                 => 2,
        ];

        $event = $this->_addEventBody('similar_entity_impression', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send organic viewed impression.
     *
     * @param \InstagramAPI\Response\Model\Item $item            The item object.
     * @param string                            $module          'feed_contextual_profile', 'reel_profile'.
     * @param string                            $viewerSessionId UUIDv4.
     * @param string                            $traySessionId   UUIDv4.
     * @param string                            $rankingToken    UUIDv4.
     * @param array                             $options         Options to configure the event.
     *                                                           'following', string, 'following' or 'not_following'.'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicViewedImpression(
        $item,
        $module,
        $viewerSessionId = null,
        $traySessionId = null,
        $rankingToken = null,
        array $options = [])
    {
        if ($module === 'feed_contextual_profile') {
            $event = 'instagram_organic_viewed_impression';
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => isset($options['following']) ? 'following' : 'not_following',
                'm_ix'                      => 17, // ?
                'imp_logger_ver'            => 21,
                'media_thumbnail_section'   => 'grid',
                'entity_page_name'          => $item->getUser()->getUsername(),
                'entity_page_id'            => $item->getUser()->getPk(),
            ];
        } elseif ($module === 'reel_feed_timeline' || $module === 'reel_profile' || $module === 'reel_follow_list' ||
                  $module === 'reel_liker_list' || $module === 'reel_hashtag_feed' || $module === 'reel_location_feed' || $module === 'reel_comment') {
            $event = 'instagram_organic_reel_viewed_impression';
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'action'					               => 'webclick',
                'source_of_action'          => $module,
                'follow_status'             => isset($options['following']) ? 'following' : 'not_following',
                'viewer_session_id'         => $viewerSessionId,
                'tray_session_id'           => $traySessionId,
                'reel_id'                   => $item->getId(),
                'is_pride_reel'             => false,
                'is_besties_reel'           => false,
                'reel_position'             => 0,
                'reel_viewer_position'      => 0,
                'reel_type'                 => 'story',
                'reel_size'                 => 1,
                'tray_position'             => 1,
                'session_reel_counter'      => 1,
                'time_elapsed'              => 0,
                'media_time_elapsed'        => 0,
                'media_time_remaining'      => mt_rand(1, 2),
                'media_dwell_time'          => mt_rand(5, 6) + mt_rand(100, 900) * 0.001,
                'media_time_paused'         => 0,
                'media_time_to_load'        => mt_rand(35, 67) * 0.01,
                'reel_start_position'       => 0,
                'story_ranking_token'       => $rankingToken,
            ];
        } elseif ($module === 'feed_contextual_hashtag') {
            $event = 'instagram_organic_viewed_impression';
            $extra = [
                'id'                        => $item->getId(),
                'm_pk'                      => $item->getId(),
                'hashtag_id'                => $options['hashtag_id'],
                'hashtag_name'              => $options['hashtag_name'],
                'hashtag_follow_status'     => isset($options['following']) ? 'following' : 'not_following',
                'hashtag_feed_type'         => isset($options['feed_type']) ? $options['feed_type'] : 'top',
                'tab_index'                 => isset($options['tab_index']) ? $options['tab_index'] : 0,
                'source_of_action'          => $module,
                'session_id'                => $this->ig->client->getPigeonSession(),
                'media_type'                => $item->getMediaType(),
                'type'                      => 0,
                'section'                   => 0,
                'position'                  => isset($options['position']) ? $options['position'] : '["0","0"]',
            ];
        } elseif ($module === 'feed_timeline') {
            $event = 'instagram_organic_viewed_impression';
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => 'following',
                'inventory_source'          => 'media_or_ad',
                'm_ix'                      => 0,
                'imp_logger_ver'            => 16,
                'is_eof'                    => false,
                'delivery_flags'            => 'n,c',
                'session_id'                => $this->ig->client->getPigeonSession(),
            ];
            if (isset($options['feed_request_id'])) {
                $extra['feed_request_id'] = $options['feed_request_id'];
            }
        } elseif ($module === 'feed_short_url') {
            $event = 'instagram_organic_viewed_impression';
            $extra = [
                'id'                        => $item->getId(),
                'm_pk'                      => $item->getId(),
                'position'                  => isset($options['position']) ? $options['position'] : '["0", "0"]',
                'media_type'                => $item->getMediaType(),
                'entity_type'               => 'user',
                'entity_name'               => $item->getUser()->getUsername(),
                'entity_page_name'          => $item->getUser()->getUsername(),
                'entity_page_id'            => $item->getUser()->getPk(),
                'media_thumbnail_section'   => 'grid',
            ];
        } else {
            throw new \InvalidArgumentException('Module not supported.');
        }

        if ($item->getMediaType() === 8) {
            $event = 'instagram_organic_carousel_viewed_impression';
            $extra['carousel_index'] = isset($options['carousel_index']) ? $options['carousel_index'] : 0;
            $extra['carousel_media_id'] = isset($options['carousel_index']) ? $item->getCarouselMedia()[$options['carousel_index']]->getId() : $item->getCarouselMedia()[0]->getId();
            $extra['carousel_m_t'] = isset($options['carousel_index']) ? $item->getCarouselMedia()[$options['carousel_index']]->getMediaType() : $item->getCarouselMedia()[0]->getMediaType();
            $extra['carousel_size'] = $item->getCarouselMediaCount();
        }

        $event = $this->_addEventBody($event, $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send organic viewed sub impression.
     *
     * @param \InstagramAPI\Response\Model\Item $item            The item object.
     * @param string                            $viewerSessionId UUIDv4.
     * @param string                            $traySessionId   UUIDv4.
     * @param array                             $options         Options to configure the event.
     *                                                           'following', string, 'following' or 'not_following'.'.
     * @param mixed                             $module
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicViewedSubImpression(
        $item,
        $viewerSessionId = null,
        $traySessionId = null,
        array $options = [],
        $module = 'reel_profile')
    {
        $extra = [
            'm_pk'                         => $item->getId(),
            'a_pk'                         => $item->getUser()->getPk(),
            'm_ts'                         => (int) $item->getTakenAt(),
            'm_t'                          => $item->getMediaType(),
            'tracking_token'               => $item->getOrganicTrackingToken(),
            'source_of_action'             => $module,
            'follow_status'                => empty($options['following']) ? 'not_following' : 'following',
            'elapsed_time_since_last_item' => -1,
            'viewer_session_id'            => $viewerSessionId,
            'tray_session_id'              => $traySessionId,
            'reel_id'                      => $item->getId(),
            'is_highlights_sourced'        => false,
            'reel_position'                => isset($options['reel_position']) ? $options['reel_position'] : 0,
            'reel_viewer_position'         => 0,
            'reel_type'                    => 'story',
            'reel_size'                    => isset($options['reel_size']) ? $options['reel_size'] : 1,
            'tray_position'                => 1,
            'session_reel_counter'         => 1,
            'time_elapsed'                 => isset($options['time_elapsed']) ? $options['time_elapsed'] : 0,
            'media_time_elapsed'           => isset($options['time_elapsed']) ? $options['time_elapsed'] : 0,
            'media_time_remaining'         => mt_rand(1, 2),
            'media_dwell_time'             => mt_rand(5, 6) + mt_rand(100, 900) * 0.001,
            'media_time_paused'            => 0,
            'media_time_to_load'           => 0,
            'reel_start_position'          => 0,
            'is_acp_delivered'             => false,
        ];

        $event = $this->_addEventBody('instagram_organic_sub_viewed_impression', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send IGTV preview end.
     *
     * @param \InstagramAPI\Response\Model\Item $item   The item object.
     * @param string                            $module Module.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendIgtvPreviewEnd(
        $item,
        $module = 'feed_contextual_profile')
    {
        $extra = [
            'm_pk'                          => $item->getId(),
            'elapsed_time_since_last_item'  => -1,
            'is_acp_delivered'              => false,
        ];

        $event = $this->_addEventBody('igtv_preview_end', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send IGTV viewer actions.
     *
     * @param string                            $eventname  Event name
     * @param \InstagramAPI\Response\Model\Item $item       The item object.
     * @param string                            $action     Action to be made. 'igtv_viewer_entry', 'igtv_viewer_exit'.
     * @param string                            $module     Module.
     * @param array                             $options    Options to configure the event.
     * @param mixed                             $entrypoint
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendIgtvViewerAction(
        $eventname,
        $item,
        $action,
        $entrypoint,
        $module = 'igtv_preview_feed_contextual_profile',
        array $options = [])
    {
        $extra = [
            'm_pk'                         => $item->getId(),
            'a_pk'                         => $item->getUser()->getPk(),
            'm_ts'                         => (int) $item->getTakenAt(),
            'm_t'                          => $item->getMediaType(),
            'tracking_token'               => $item->getOrganicTrackingToken(),
            'source_of_action'             => $module,
            'follow_status'                => empty($options['following']) ? 'not_following' : 'following',
            'action'                       => $action,
            'elapsed_time_since_last_item' => -1,
            'entry_point'                  => $entrypoint,
            'guide_open_status'            => false,
            'igtv_viewer_session_id'       => isset($options['viewer_session_id']) ? $options['viewer_session_id'] : null,
            'is_igtv'                      => 1,
            'is_ad'                        => false,
            'is_acp_delivered'             => false,
        ];

        if ($eventname === 'igtv_viewer_entry') {
            $extra['host_video_should_request_ads'] = false;
        }

        if (!empty($options['time_elapsed'])) {
            $extra['time_elapsed'] = $options['time_elapsed'];
        }

        $event = $this->_addEventBody($eventname, $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send video action.
     *
     * @param string                            $action  Action to be made. 'video_displayed', 'video_should_start', 'video_buffering_started',
     *                                                   'video_started_playing', 'video_paused', 'video_exited'.
     * @param \InstagramAPI\Response\Model\Item $item    The item object.
     * @param string                            $module  'feed_contextual_profile'.
     * @param array                             $options Options to configure the event.
     *                                                   'following', string, 'following' or 'not_following'.'.
     *                                                   'viewer_session_id', string. UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendVideoAction(
        $action,
        $item,
        $module,
        array $options = [])
    {
        if ($module === 'feed_contextual_profile') {
            $extra = [
                'video_type'                  => $item->getProductType(),
                'm_pk'                        => $item->getId(),
                'a_pk'                        => $item->getUser()->getPk(),
                'm_ts'                        => (int) $item->getTakenAt(),
                'm_t'                         => $item->getMediaType(),
                'tracking_token'              => $item->getOrganicTrackingToken(),
                'source_of_action'            => $module,
                'follow_status'               => isset($options['following']) ? 'following' : 'not_following',
                'm_ix'                        => 14, // ?
                'is_dash_eligible'            => 1,
                'video_codec'                 => $item->getVideoCodec(),
                'playback_format'             => 'dash',
                'a_i'                         => 'organic',
            ];
        } elseif ($module === 'feed_short_url') {
            $extra = [
                'id'                        => $item->getId(),
                'm_pk'                      => $item->getId(),
                'position'                  => isset($options['position']) ? $options['position'] : '["0", "0"]',
                'media_type'                => $item->getMediaType(),
                'entity_type'               => 'user',
                'entity_name'               => $item->getUser()->getUsername(),
                'entity_page_name'          => $item->getUser()->getUsername(),
                'entity_page_id'            => $item->getUser()->getPk(),
                'media_thumbnail_section'   => 'grid',
            ];
        } elseif ($module === 'feed_contextual_hashtag') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'hashtag_id'                => $options['hashtag_id'],
                'hashtag_name'              => $options['hashtag_name'],
                'hashtag_follow_status'     => isset($options['following']) ? 'following' : 'not_following',
                'hashtag_feed_type'         => isset($options['feed_type']) ? $options['feed_type'] : 'top',
                'tab_index'                 => isset($options['tab_index']) ? $options['tab_index'] : 0,
                'source_of_action'          => $module,
                'session_id'                => $this->ig->client->getPigeonSession(),
                'media_type'                => $item->getMediaType(),
                'type'                      => 0,
                'section'                   => 0,
                'position'                  => isset($options['position']) ? $options['position'] : '["0","0"]',
            ];
        } elseif ($module === 'feed_timeline') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => 'following',
                'inventory_source'          => 'media_or_ad',
                'm_ix'                      => 0,
                'imp_logger_ver'            => 16,
                'is_eof'                    => false,
            ];
        } else {
            throw new \InvalidArgumentException('Module not supported.');
        }

        if ($action === 'video_displayed') {
            $extra['initial'] = 1;
            $extra['media_thumbnail_section'] = 'grid';
            $extra['entity_page_name'] = $item->getUser()->getUsername();
            $extra['entity_page_id'] = $item->getUser()->getPk();
        } elseif ($action === 'video_should_start') {
            $extra['reason'] = 'start';
            $extra['viewer_session_id'] = $options['viewer_session_id'];
            $extra['seq_num'] = $options['seq'];
        } elseif ($action === 'video_buffering_started') {
            $extra['reason'] = 'start';
            $extra['viewer_session_id'] = $options['viewer_session_id'];
            $extra['seq_num'] = $options['seq'];
            $extra['time'] = 0;
            $extra['duration'] = $item->getVideoDuration();
            $extra['timeAsPercent'] = 0;
            $extra['playing_audio'] = '0';
            $extra['lsp'] = 0;
            $extra['loop_count'] = 0;
            $extra['video_width'] = 0;
        } elseif ($action === 'video_started_playing') {
            $extra['duration'] = $item->getVideoDuration();
            $extra['playing_audio'] = '0';
            $extra['viewer_session_id'] = $options['viewer_session_id'];
            $extra['seq_num'] = $options['seq'];
            $extra['reason'] = 'autoplay';
            $extra['start_delay'] = mt_rand(100, 500);
            $extra['cached'] = false;
            $extra['warmed'] = false;
            $extra['streaming'] = true;
            if ($item->getVideoVersions() !== null) {
                $extra['video_width'] = $item->getVideoVersions()[0]->getWidth();
                $extra['video_heigth'] = $item->getVideoVersions()[0]->getHeight();
                $extra['view_width'] = $item->getVideoVersions()[0]->getWidth();
                $extra['view_height'] = $item->getVideoVersions()[0]->getHeight();
            }
            $extra['app_orientation'] = 'portrait';
        } elseif ($action === 'video_paused') {
            $extra['duration'] = $item->getVideoDuration();
            $extra['time'] = $item->getVideoDuration() - mt_rand(1, 3);
            $extra['playing_audio'] = '0';
            $extra['viewer_session_id'] = $options['viewer_session_id'];
            $extra['seq_num'] = $options['seq'];
            $extra['original_start_reason'] = 'autoplay';
            $extra['reason'] = 'fragment_paused';
            $extra['lsp'] = 0;
            $extra['loop_count'] = mt_rand(2, 5);
            if ($item->getVideoVersions() !== null) {
                $extra['video_width'] = $item->getVideoVersions()[0]->getWidth();
                $extra['video_heigth'] = $item->getVideoVersions()[0]->getHeight();
                $extra['view_width'] = $item->getVideoVersions()[0]->getWidth();
                $extra['view_height'] = $item->getVideoVersions()[0]->getHeight();
            }
        }

        $event = $this->_addEventBody($action, $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send profile view.
     *
     * @param string $userId        User ID of account who made the comment in Instagram's internal format.
     * @param string $mediaId       Instagram's media ID.
     * @param string $trackingToken Media tracking token.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendProfileView(
        $userId,
        $mediaId = null,
        $trackingToken = null)
    {
        $extra = [
                'm_ix'                          => 0,
                'carousel_index'                => 0,
                'elapsed_time_since_last_item'  => -1.0,
                'target_id'                     => $userId,
                'actor_id'                      => $this->ig->account_id,
                'is_acp_delivered'              => false,
        ];

        if (($mediaId !== null) && ($trackingToken !== null)) {
            $extra['m_pk'] = $mediaId;
            $extra['media_id_attribution'] = $mediaId;
            $extra['tracking_token'] = $trackingToken;
        }

        $event = $this->_addEventBody('profile_view', 'profile', $extra);
        $this->_addEventData($event);
    }

    /**
     * Get nav depth.
     *
     * @param string $fromModule Source module.
     * @param string $toModule   Destination module.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    protected function _getNavDepthForModules(
        $fromModule,
        $toModule)
    {
        $navDepth = null;

        switch ($fromModule) {
            case 'feed_timeline':
            case 'direct_thread_toggle':
            case 'tabbed_gallery_camera':
            case 'self_profile':
            case 'login':
            case 'explore_popular':
                $navDepth = 0;
                break;
            case 'direct_inbox':
                if ($toModule === 'feed_timeline') {
                    $navDepth = 1;
                } elseif ($toModule === 'direct_thread') {
                    $navDepth = 0;
                }
                break;
            case 'profile':
                if ($toModule === 'feed_contextual_profile' || $toModule === 'feed_timeline'
                    || $toModule === 'explore_popular' || $toModule === 'unified_follow_lists') {
                    $navDepth = 2;
                } elseif ($toModule === 'comments_v2' || $toModule === 'likers') {
                    $navDepth = 1;
                }
                break;
            case 'feed_contextual_chain':
                if ($toModule === 'feed_contextual_profile' || $toModule === 'comments_v2') {
                    $navDepth = 1;
                }
                break;
            case 'feed_contextual_profile':
                if ($toModule === 'comments_v2' || $toModule === 'feed_timeline' || $toModule === 'profile' || $toModule === 'self_profile') {
                    $navDepth = 3;
                } elseif ($toModule === 'explore_popular') {
                    $navDepth = 2;
                } elseif ($toModule === 'likers') {
                    $navDepth = 4;
                }
                break;
            case 'feed_hashtag':
                if ($toModule === 'feed_contextual_hashtag') {
                    $navDepth = 2;
                }
                break;
            case 'search':
            case 'search_users':
            case 'blended_search':
                $navDepth = 1;
                break;
            case 'comments_v2':
            case 'likers':
                if ($toModule === 'feed_contextual_profile') {
                    $navDepth = 4;
                }
                break;
            case 'replay_feed_timeline':
                if ($toModule === 'feed_timeline') {
                    $navDepth = 1;
                }
                break;
            case 'unified_follow_lists':
                if ($toModule === 'profile') {
                    $navDepth = 1;
                } elseif ($toModule === 'unified_follow_lists') {
                    $navDepth = 3;
                }
                break;
            default:
                $navDepth = 1; // TODO: A major update is required.
        }

        $navDepth = 1; // TODO: A major update is required.

        if ($navDepth === null) {
            throw new \InvalidArgumentException(sprintf('Don\'t know navDepth when navigating from %s to %s.', $fromModule, $toModule));
        }

        return $navDepth;
    }

    /**
     * Validate navigation options.
     *
     * @param string $fromModule Source module.
     * @param string $toModule   Destination module.
     * @param array  $options    Options.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    protected function _validateNavigationOptions(
        $fromModule,
        $toModule,
        array $options = [])
    {
        if ($fromModule === 'feed_timeline' && $toModule === 'explore_popular'
            || $fromModule === 'explore_popular' && $toModule === 'explore_popular'
            || $fromModule === 'profile' && $toModule === 'explore_popular') {
            $requiredOptions = [
                'topic_cluster_title',
                'topic_cluster_id',
                'topic_cluster_type',
                'topic_cluster_session_id',
                'topic_nav_order',
            ];
        } elseif ($fromModule === 'direct_inbox' && $toModule === 'direct_thread') {
            $requiredOptions = [];
        /*
        $requiredOptions = [
            'user_id',
        ];
        */
        } elseif ($fromModule === 'feed_hashtag' && $toModule === 'feed_contextual_hashtag') {
            $requiredOptions = [
                'hashtag_id',
                'hashtag_name',
            ];
        } elseif ($fromModule === 'blended_search' && $toModule === 'profile') {
            $requiredOptions = [
                'rank_token',
                'query_text',
                'search_session_id',
                'selected_type',
                'position',
                'username',
                'user_id',
            ];
        } elseif ($fromModule === 'search_users' && $toModule === 'profile') {
            $requiredOptions = [
                'rank_token',
                'query_text',
                'search_session_id',
                'selected_type',
                'position',
                'username',
                'user_id',
            ];
        } elseif ($fromModule === 'feed_contextual_profile' && $toModule === 'comments_v2') {
            $requiredOptions = [
                'user_id',
            ];
        } elseif ($fromModule === 'feed_contextual_profile' && $toModule === 'profile'
            || $fromModule === 'feed_timeline' && $toModule === 'profile'
            || $fromModule === 'reel_liker_list' && $toModule === 'profile'
            || $fromModule === 'reel_hashtag_feed' && $toModule === 'profile'
            || $fromModule === 'reel_follow_list' && $toModule === 'profile'
            || $fromModule === 'reel_comment' && $toModule === 'profile') {
            $requiredOptions = [
                'username',
                'user_id',
            ];
        } else {
            $requiredOptions = [];
        }

        foreach ($requiredOptions as $optionName) {
            if (!array_key_exists($optionName, $options)) {
                throw new \InvalidArgumentException(sprintf('%s option should be set when navigating from %s to %s.', $optionName, $fromModule, $toModule));
            }
        }
    }

    /**
     * Validate navigation path.
     *
     * @param string $fromModule Source module.
     * @param string $toModule   Destination module.
     * @param string $clickPoint Options.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    protected function _validateNavigationPath(
        $fromModule,
        $toModule,
        $clickPoint)
    {
        if (in_array($clickPoint, ['main_home', 'main_search', 'main_inbox', 'main_camera', 'main_profile'])) {
            return;
        }

        $navigation = [
            'one_page_registration' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'add_birthday',
                ],
            ],
            'add_birthday'  => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'username_sign_up',
                ],
            ],
            'feed_short_url'  => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
            ],
            'feed_timeline' => [
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'on_launch_direct_inbox',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'media_owner',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'media_location',
                    'dest_module'   => 'feed_location',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'comments_v2',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'discover_people',
                ],
                [
                    'clickpoint'    => 'media_caption_hashtag',
                    'dest_module'   => 'feed_hashtag',
                ],
                [
                    'clickpoint'    => 'media_header_hashtag',
                    'dest_module'   => 'feed_hashtag',
                ],
                [
                    'clickpoint'    => 'media_likes',
                    'dest_module'   => 'likers',
                ],
                [
                    'clickpoint'    => 'main_camera',
                    'dest_module'   => 'tabbed_gallery_camera',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'reel_feed_timeline',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'reel_feed_timeline_item_header',
                ],
                [
                    'clickpoint'    => 'user_mention',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'BottomSheetConstants.FRAGMENT_TAG',
                ],
                [
                    'clickpoint'    => 'your_story_dialog_option',
                    'dest_module'   => 'quick_capture_fragment',
                ],
                [
                    'clickpoint'    => 'inferred_source',
                    'dest_module'   => 'profile',
                ],
            ],
            'reel_feed_timeline_item_header'    => [
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'self_unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'comments_v2',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'likers',
                ],
            ],
            'reel_feed_timeline'    => [
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'feed_timeline',
                ],
            ],
            'newsfeed_you' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'main_camera',
                    'dest_module'   => 'tabbed_gallery_camera',
                ],
                [
                    'clickpoint'    => 'on_launch_direct_inbox',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'discover_people',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'feed_hashtag',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'self_comments_v2',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'follow_requests',
                ],
            ],
            'tabbed_gallery_camera' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'photo_filter',
                ],
            ],
            'tabbed_gallery_camera' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'photo_filter',
                ],
            ],
            'photo_filter' => [
                [
                    'clickpoint'    => 'next',
                    'dest_module'   => 'metadata_followers_share',
                ],
            ],
            'metadata_followers_share' => [
                [
                    'clickpoint'    => 'next',
                    'dest_module'   => 'feed_timeline',
                ],
            ],
            'follow_requests' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'newsfeed_you',
                ],
            ],
            'self_profile' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_camera',
                    'dest_module'   => 'tabbed_gallery_camera',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'feed_contextual_self_profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'self_unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'edit_profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'self_following',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'self_followers',
                ],
            ],
            'edit_profile'  => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile_edit_bio',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'self_profile',
                ],
            ],
            'profile_edit_bio'  => [
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'edit_profile',
                ],
            ],
            'feed_contextual_self_profile' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'self_unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'media_likes',
                    'dest_module'   => 'likers',
                ],
                [
                    'clickpoint'    => 'user_mention',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'BottomSheetConstants.FRAGMENT_TAG',
                ],
            ],
            'self_unified_follow_lists' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'following',
                    'dest_module'   => 'self_unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'followers',
                    'dest_module'   => 'self_unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'reel_follow_list',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'media_mute_sheet',
                ],
            ],
            'unified_follow_lists' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'following',
                    'dest_module'   => 'unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'followers',
                    'dest_module'   => 'unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'reel_follow_list',
                ],
            ],
            'reel_composer_preview' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'story_stickers_tray',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'reel_composer_camera',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'self_profile',
                ],
            ],
            'story_stickers_tray' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'reel_composer_preview',
                ],
            ],
            'explore_popular' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'search',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'on_launch_direct_inbox',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'feed_contextual_chain',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'explore_topic_load',
                    'dest_module'   => 'explore_popular',
                ],
            ],
            'search' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'blended_search',
                ],
            ],
            'blended_search' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'blended_search',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'feed_hashtag',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'search_tags',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'search_places',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'search_users',
                ],
            ],
            'search_tags'  => [
                [
                    'clickpoint'    => 'search_result',
                    'dest_module'   => 'feed_hashtag',
                ],
            ],
            'search_places'  => [
                [
                    'clickpoint'    => 'search_result',
                    'dest_module'   => 'feed_location',
                ],
            ],
            'search_users' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'search_result',
                    'dest_module'   => 'profile',
                ],
            ],
            'feed_hashtag' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'blended_search',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'feed_contextual_hashtag',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'reel_hashtag_feed',
                ],
            ],
            'feed_location' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'feed_contextual_location',
                ],
            ],
            'reel_hashtag_feed' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'feed_hashtag',
                ],
            ],
            'feed_contextual_chain' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'on_launch_direct_inbox',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'media_owner',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'media_location',
                    'dest_module'   => 'feed_location',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'comments_v2',
                ],
                [
                    'clickpoint'    => 'media_caption_hashtag',
                    'dest_module'   => 'feed_hashtag',
                ],
                [
                    'clickpoint'    => 'media_likes',
                    'dest_module'   => 'likers',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'comments_v2_feed_contextual_chain',
                ],
            ],
            'reel_feed_contextual_post_item_header' => [
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'feed_contextual_chain',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'feed_contextual_hashtag',
                ],
            ],
            'feed_contextual_hashtag' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'media_owner',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'media_location',
                    'dest_module'   => 'feed_location',
                ],
                [
                    'clickpoint'    => 'media_likes',
                    'dest_module'   => 'likers',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'feed_hashtag',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'reel_feed_contextual_post_item_header',
                ],
                [
                    'clickpoint'    => 'user_mention',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'BottomSheetConstants.FRAGMENT_TAG',
                ],
            ],
            'feed_contextual_location' => [
                [
                    'clickpoint'    => 'media_owner',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'user_mention',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'BottomSheetConstants.FRAGMENT_TAG',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'reel_location_feed',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'feed_location',
                ],
            ],
            'reel_location_feed' => [
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'feed_contextual_location',
                ],
            ],
            'profile' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'blended_search',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'feed_contextual_profile',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'feed_contextual_hashtag',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'self_unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'on_launch_direct_inbox',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'media_location',
                    'dest_module'   => 'feed_location',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'reel_profile',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'likers',
                ],
                [
                   'clickpoint'    => 'back',
                   'dest_module'   => 'feed_contextual_chain',
                ],
                [
                    'clickpoint'   => 'followers',
                    'dest_module'  => 'unified_follow_lists',
                ],
                [
                    'clickpoint'   => 'back',
                    'dest_module'  => 'reel_hashtag_feed',
                ],
                [
                    'clickpoint'   => 'back',
                    'dest_module'  => 'reel_liker_list',
                ],
                [
                    'clickpoint'   => 'back',
                    'dest_module'  => 'reel_comment',
                ],
                [
                    'clickpoint'   => 'back',
                    'dest_module'  => 'comments_v2',
                ],
                [
                    'clickpoint'   => 'button',
                    'dest_module'  => 'media_mute_sheet',
                ],
                [
                    'clickpoint'   => 'back',
                    'dest_module'  => 'search',
                ],
                [
                    'clickpoint'   => 'back',
                    'dest_module'  => 'newsfeed_you',
                ],
            ],
            'reel_profile' => [
                [
                    'clickpoint'   => 'back',
                    'dest_module'  => 'profile',
                ],
            ],
            'reel_follow_list' => [
                [
                    'clickpoint'   => 'back',
                    'dest_module'  => 'unified_follow_lists',
                ],
                [
                    'clickpoint'   => 'back',
                    'dest_module'  => 'self_unified_follow_lists',
                ],
            ],
            'reel_comment' => [
                [
                    'clickpoint'   => 'button',
                    'dest_module'  => 'profile',
                ],
                [
                    'clickpoint'   => 'back',
                    'dest_module'  => 'comments_v2_feed_contextual_profile',
                ],
                [
                    'clickpoint'   => 'back',
                    'dest_module'  => 'comments_v2_feed_contextual_chain',
                ],
            ],
            'feed_contextual_profile' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'on_launch_direct_inbox',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'media_location',
                    'dest_module'   => 'feed_location',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'comments_v2',
                ],
                [
                    'clickpoint'    => 'media_caption_hashtag',
                    'dest_module'   => 'feed_hashtag',
                ],
                [
                    'clickpoint'    => 'media_likes',
                    'dest_module'   => 'likers',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'comments_v2_feed_contextual_profile',
                ],
                [
                    'clickpoint'    => 'user_mention',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'BottomSheetConstants.FRAGMENT_TAG',
                ],
            ],
            'likers' => [
                [
                   'clickpoint'    => 'back',
                   'dest_module'   => 'feed_contextual_profile',
                ],
                [
                   'clickpoint'    => 'back',
                   'dest_module'   => 'feed_contextual_self_profile',
                ],
                [
                   'clickpoint'    => 'back',
                   'dest_module'   => 'feed_timeline',
                ],
                [
                   'clickpoint'    => 'back',
                   'dest_module'   => 'feed_contextual_hashtag',
                ],
                [
                   'clickpoint'    => 'back',
                   'dest_module'   => 'feed_contextual_chain',
                ],
                [
                   'clickpoint'    => 'button',
                   'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'reel_liker_list',
                ],
            ],
            'reel_liker_list' => [
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'likers',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
            ],
            'comments_v2' => [
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'on_launch_direct_inbox',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'media_owner',
                    'dest_module'   => 'profile',
                ],
                [
                     'clickpoint'    => 'back',
                     'dest_module'   => 'feed_contextual_profile',
                ],
                [
                     'clickpoint'    => 'back',
                     'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'reel_feed_timeline_item_header',
                ],
            ],
            'direct_inbox' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'direct_thread',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'pending_inbox',
                ],
                [
                    'clickpoint'    => 'inbox',
                    'dest_module'   => 'direct_thread',
                ],
            ],
            'direct_thread_toggle' => [
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'pending_inbox',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'direct_inbox',
                ],
            ],
            'pending_inbox' => [
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'direct_thread',
                ],
            ],
            'direct_thread' => [
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'pending_inbox',
                ],
            ],
            'login' => [
                [
                    'clickpoint'    => 'cold start',
                    'dest_module'   => 'feed_timeline',
                ],
            ],
            'replay_feed_timeline' => [
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'feed_timeline',
                ],
            ],
            'media_mute_sheet' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'self_unified_follow_lists',
                ],
            ],
            'quick_capture_fragment' => [
                [
                    'clickpoint'    => 'story_posted_from_camera',
                    'dest_module'   => 'feed_timeline',
                ],
            ],
        ];

        $found = false;
        if (!isset($navigation[$fromModule])) {
            throw new \InvalidArgumentException(sprintf('Unknown fromModule %s.', $fromModule));
        }

        foreach ($navigation[$fromModule] as $nav) {
            if ($clickPoint === 'back' && $nav['dest_module'] === $fromModule) {
                $found = true;
                break;
            } elseif ($nav['clickpoint'] === $clickPoint && $nav['dest_module'] === $toModule) {
                $found = true;
                break;
            }
        }

        if ($found === false) {
            throw new \InvalidArgumentException(sprintf('Invalid navigation provided (from %s to %s, via %s).', $fromModule, $toModule, $clickPoint));
        }
    }

    /**
     * Send navigation. It tells Instagram how you reached specific modules.
     *
     * Modules: 'login': When performing login.
     *          'profile': User profile.
     *          'self_profile': Self user profile.
     *          'feed_contextual_profile': Feed from user profile.
     *          'feed_contextual_self_profile': Self feed profile.
     *          'feed_contextual_chain': Chained feed from user profile.
     *          'comments_v2': Comments.
     *          'feed_timeline': Main page, feed timeline.
     *          'direct_inbox': Main page on direct.
     *          'direct_thread': When clicking on a thread on direct.
     *          'direct_thread_toggle': When exiting a thread and going to back to direct_inbox.
     *
     * @param string      $clickPoint  Button or context that made the navigation.
     *                                 'cold start': When doing a clean/cold login (no sessions stored) from 'login' to 'feed_timeline'.
     *                                 'on_launch_direct_inbox': clicking on the airplane (direct) icon. from 'feed_timeline' to 'direct_inbox'.
     *                                 'back': when going from 'direct_inbox' to 'feed_timeline'.
     *                                 when going back from 'direct_thread_toggle' to 'direct_inbox'.
     *                                 'button': when going from 'direct_inbox' to 'direct_thread'.
     *                                 when going from the user profile ('profile') to the user feed 'feed_contextual_profile'.
     *                                 when going from the chained feed ('feed_contextual_chain') to the comments module ('comments_v2').
     * @param string      $fromModule  The module you are coming from.
     * @param string      $toModule    The module you are going to.
     * @param string|null $hashtagId   The hashtag ID. Only used when 'feed_hashtag' is used as module.
     * @param string|null $hashtagName The hashtag name. Only used when 'feed_hashtag' is used as module.
     * @param array       $options     Options to configure the event.
     *                                 'user_id' when going from direct_inbox to direct_thread.
     *                                 'topic_cluster_id' (example: 'hashtag_inspired:23') when going from explore_popular to specific topic.
     *                                 'topic_cluster_title' (example: 'Food') when going from explore_popular to specific topic.
     *                                 'topic_cluster_session_id' (UUIDv4) when going from explore_popular to specific topic.
     *                                 'topic_nav_order' (place of the tab, 3 would be for Food, count starts at 1) when going from explore_popular to specific topic.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendNavigation(
        $clickPoint,
        $fromModule,
        $toModule,
        $hashtagId = null,
        $hashtagName = null,
        array $options = [])
    {
        if ($hashtagId !== null) {
            $options['hashtag_id'] = $hashtagId;
        }
        if ($hashtagName !== null) {
            $options['hashtag_name'] = $hashtagName;
        }

        $this->_validateNavigationPath($fromModule, $toModule, $clickPoint);
        $this->_validateNavigationOptions($fromModule, $toModule, $options);

        if ($this->ig->getIsAndroid()) {
            //$navChain = $this->sendUpdateSessionChain($toModule, $clickPoint);
            $navChain = $this->_generateNavChain($toModule, $clickPoint);
        }

        $navDepth = $this->_getNavDepthForModules($fromModule, $toModule);

        $extra = [
            'click_point'               => $clickPoint,
            'source_module'             => $fromModule,
            'dest_module'               => $toModule,
            'seq'                       => $this->ig->navigationSequence,
            'nav_time_taken'            => mt_rand(200, 350),
        ];

        if ($this->ig->getIsAndroid()) {
            $extra['nav_chain'] = $navChain;
        }

        switch ($fromModule) {
            case 'feed_timeline':
                if ($toModule === 'explore_popular') {
                    $extra['nav_depth'] = $navDepth;
                    $extra['topic_cluster_title'] = $options['topic_cluster_title'];
                    $extra['topic_cluster_id'] = $options['topic_cluster_id'];
                    $extra['topic_cluster_type'] = $options['topic_cluster_type'];
                    $extra['topic_cluster_debug_info'] = null;
                    $extra['topic_cluster_session_id'] = $options['topic_cluster_session_id'];
                    $extra['topic_nav_order'] = $options['topic_nav_order'];
                }
                break;
            case 'direct_thread_toggle':
            case 'tabbed_gallery_camera':
                $extra['nav_depth'] = $navDepth;
                break;
            case 'direct_inbox':
                if ($toModule === 'feed_timeline') {
                    $extra['nav_depth'] = $navDepth;
                } elseif ($toModule === 'direct_thread') {
                    /*
                    if (!isset($options['user_id']) && !isset($options['thread_id'])) {
                        throw new \InvalidArgumentException('User ID or Thread ID not provided.');
                    }
                    */

                    if (isset($options['user_id'])) {
                        $extra['user_id'] = $options['user_id'];
                    }

                    if (isset($options['thread_id'])) {
                        $extra['thread_id'] = $options['thread_id'];
                    }

                    $extra['nav_depth'] = $navDepth;
                }
                break;
            case 'profile':
            case 'feed_contextual_chain':
                if ($toModule === 'feed_contextual_profile' || $toModule === 'comments_v2') {
                    $extra['nav_depth'] = $navDepth;
                } elseif ($toModule === 'unified_follow_lists') {
                    $extra['nav_depth'] = $navDepth;
                }
                break;
            case 'self_profile':
            case 'login':
                if ($toModule === 'feed_contextual_self_profile' || $toModule === 'feed_timeline' || $toModule === 'edit_profile') {
                    $extra['nav_depth'] = $navDepth;
                    $extra['user_id'] = $this->ig->account_id;
                }
                break;
            case 'feed_hashtag':
                if ($toModule === 'feed_contextual_hashtag') {
                    $extra['nav_depth'] = $navDepth;
                    $extra['hashtag_id'] = $hashtagId;
                    $extra['hashtag_name'] = $hashtagName;
                }
                break;
            case 'explore_popular':
                if ($toModule === 'explore_popular') {
                    $extra['nav_depth'] = $navDepth;
                    $extra['topic_cluster_id'] = $options['topic_cluster_id'];
                    $extra['topic_cluster_title'] = $options['topic_cluster_title'];
                    $extra['topic_cluster_session_id'] = $options['topic_cluster_session_id'];
                    $extra['topic_nav_order'] = $options['topic_nav_order'];
                }
                break;
            case 'search_tags':
                if ($toModule === 'feed_hashtag') {
                    $extra['nav_depth'] = $navDepth;
                    $extra['query_text'] = $options['query_text'];
                    $extra['search_session_id'] = $options['search_session_id'];
                    $extra['search_tab'] = $fromModule;
                    $extra['selected_type'] = 'hashtag';
                    $extra['hashtag_id'] = $options['hashtag_id'];
                    $extra['hashtag_name'] = $options['hashtag_name'];
                }
                break;
            case 'blended_search':
            case 'search_places':
            case 'feed_contextual_location':
                $extra['nav_depth'] = 1;
                if ($toModule === 'profile') {
                    $extra['username'] = $options['username'];
                    $extra['user_id'] = $options['user_id'];
                } elseif ($toModule === 'feed_location') {
                    $extra['rank_token'] = isset($options['rank_token']) ? $options['rank_token'] : null;
                    $extra['query_text'] = $options['query_text'];
                    $extra['search_session_id'] = $options['search_session_id'];
                    $extra['search_tab'] = $fromModule;
                    $extra['selected_type'] = $options['selected_type'];
                    $extra['position'] = $options['position'];
                    $extra['entity_page_name'] = $options['entity_page_name'];
                    $extra['entity_page_id'] = $options['entity_page_id'];
                }
                break;
            case 'unified_follow_lists':
                if ($toModule === 'unified_follow_lists') {
                    $extra['nav_depth'] = $navDepth;
                    $extra['source_tab'] = $options['source_tab'];
                    $extra['dest_tab'] = $options['dest_tab'];
                    $extra['action'] = 'swipe';
                }
                break;
            default:
                break;
        }

        $event = $this->_addEventBody('navigation', $fromModule, $extra);

        $this->ig->navigationSequence++;
        $this->_addEventData($event);
    }

    /**
     * Send navigation tab clicked.
     *
     * @param string $sourceSection      Section origin (main_X).
     * @param string $destinationSection Section destination (main_Y).
     * @param string $module             Current module
     * @param string $flag               Type of navigation tab.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendNavigationTabClicked(
        $sourceSection,
        $destinationSection,
        $module,
        $flag = 'tab')
    {
        $extra = [
            'current_section'        => $sourceSection,
            'destination_section'    => $destinationSection,
            'flag'                   => $flag,
        ];

        $event = $this->_addEventBody('ig_navigation_tab_clicked', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Update session chain.
     *
     * It is used for updating navigation chain.
     *
     * @param string $module     Module.
     * @param string $clickPoint Click point.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return string
     */
    public function sendUpdateSessionChain(
        $module,
        $clickPoint)
    {
        $navChain = $this->_generateNavChain($module, $clickPoint);
        $extra = [
            'sessions_chain'  => $navChain,
        ];

        $event = $this->_addEventBody('ig_sessions_chain_update', $module, $extra);
        $this->_addEventData($event);

        return $navChain;
    }

    /**
     * Open photo camera tab.
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $currentTime Current time. Timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendOpenPhotoCameraTab(
        $waterfallId,
        $startTime,
        $currentTime)
    {
        $extra = [
            'waterfall_id'  => $waterfallId,
            'start_time'    => $startTime,
            'current_time'  => $currentTime,
            'elapsed_time'  => $currentTime - $startTime,
        ];

        $event = $this->_addEventBody('photo_camera_tab_opened', 'waterfall_capture_flow', $extra);
        $this->_addEventData($event);
    }

    /**
     * Shutter click in camera.
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $currentTime Current time. Timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendShutterClickInCamera(
        $waterfallId,
        $startTime,
        $currentTime)
    {
        $extra = [
            'waterfall_id'  => $waterfallId,
            'start_time'    => $startTime,
            'current_time'  => $currentTime,
            'elapsed_time'  => $currentTime - $startTime,
        ];

        $event = $this->_addEventBody('shutter_click_in_camera', 'waterfall_capture_flow', $extra);
        $this->_addEventData($event);
    }

    /**
     * Start gallery edit.
     *
     * When you capture a media, Instagram lets you add stickers, mentions...
     * This is when gallery start session starts.
     *
     * @param string $sessionId Session ID. UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendStartGalleryEditSession(
        $sessionId)
    {
        $extra = [
            'ig_userid'     => $this->ig->account_id,
            'session_id'    => $sessionId,
            'event_type'    => 1,
            'entry_point'   => 58,
            'gallery_type'  => 'old_gallery',
        ];

        $event = $this->_addEventBody('ig_feed_gallery_start_edit_session', 'ig_creation_client_events', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send filter photo.
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $currentTime Current time. Timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendFilterPhoto(
        $waterfallId,
        $startTime,
        $currentTime)
    {
        $extra = [
            'waterfall_id'  => $waterfallId,
            'start_time'    => $startTime,
            'current_time'  => $currentTime,
            'elapsed_time'  => $currentTime - $startTime,
            'media_source'  => 1,
        ];

        $event = $this->_addEventBody('filter_photo', 'waterfall_capture_flow', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send filter finish.
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $currentTime Current time. Timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendFilterFinish(
        $waterfallId,
        $startTime,
        $currentTime)
    {
        $extra = [
            'waterfall_id'  => $waterfallId,
            'start_time'    => $startTime,
            'current_time'  => $currentTime,
            'elapsed_time'  => $currentTime - $startTime,
            'filter_id'     => 0,
        ];

        $event = $this->_addEventBody('filter_finished', 'waterfall_capture_flow', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send Instagram Media Creation.
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $currentTime Current time. Timestamp.
     * @param string $mediaType   Media type. 'photo'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendIGMediaCreation(
        $waterfallId,
        $startTime,
        $currentTime,
        $mediaType)
    {
        $extra = [
            'waterfall_id'  => $waterfallId,
            'start_time'    => $startTime,
            'current_time'  => $currentTime,
            'elapsed_time'  => $currentTime - $startTime,
        ];

        if ($mediaType === 'photo') {
            $extra['step'] = 'edit_photo';
            $extra['next_step'] = 'share_screen';
            $extra['entry_point'] = 'share_button';
        } else {
            throw new \InvalidArgumentException('Invalid media type.');
        }

        $event = $this->_addEventBody('ig_creation_flow_step', 'waterfall_capture_flow_v2', $extra);
        $this->_addEventData($event);
    }

    /**
     * End gallery edit.
     *
     * @param string $sessionId Session ID. UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendEndGalleryEditSession(
        $sessionId)
    {
        $extra = [
            'ig_userid'     => $this->ig->account_id,
            'session_id'    => $sessionId,
            'event_type'    => 1,
            'entry_point'   => 58,
            'gallery_type'  => 'old_gallery',
        ];

        $event = $this->_addEventBody('ig_feed_gallery_end_edit_session', 'ig_creation_client_events', $extra);
        $this->_addEventData($event);
    }

    /**
     * Start share session.
     *
     * @param string $sessionId Session ID. UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendStartShareSession(
        $sessionId)
    {
        $extra = [
            'ig_userid'     => $this->ig->account_id,
            'session_id'    => $sessionId,
            'event_type'    => 1,
            'entry_point'   => 58,
            'gallery_type'  => 'old_gallery',
        ];

        $event = $this->_addEventBody('ig_feed_gallery_start_share_session', 'ig_creation_client_events', $extra);
        $this->_addEventData($event);
    }

    /**
     * Share media.
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $currentTime Current time. Timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendShareMedia(
        $waterfallId,
        $startTime,
        $currentTime)
    {
        $extra = [
            'waterfall_id'  => $waterfallId,
            'start_time'    => $startTime,
            'current_time'  => $currentTime,
            'elapsed_time'  => $currentTime - $startTime,
        ];

        $event = $this->_addEventBody('share_media', 'waterfall_capture_flow', $extra);
        $this->_addEventData($event);
    }

    /**
     * Direct. Send intent/attempt of a message.
     *
     * For sending any direct message, first is must be sent the invent,
     * the 'direct_composer_send_text' and finally the attempt.
     *
     * @param string          $action        'send_intent', 'send_attempt' or 'sent'.
     * @param string          $clientContext Client context used for sending intent/attempt DM.
     * @param string          $type          Message type. 'text', 'visual_photo', 'reel_share', 'share_media' or 'profile'.
     * @param string|string[] $recipients    String array of users PK.
     * @param string          $channel       Channel used for sending the intent/attempt DM. If using MQTT 'realtime', if using HTTP direct 'rest'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendDirectMessageIntentOrAttempt(
        $action,
        $clientContext,
        $type,
        $recipients,
        $channel = 'rest')
    {
        if ($action !== 'send_intent' && $action !== 'send_attempt' && $action !== 'sent') {
            throw new \InvalidArgumentException(sprintf('%s is not a valid action.', $action));
        }

        if ($type !== 'text' && $type !== 'visual_photo' && $type !== 'reel_share' && $type !== 'share_media' && $type !== 'profile') {
            throw new \InvalidArgumentException(sprintf('%s is not a valid type.', $type));
        }

        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        $extra = [
            'action'         => $action,
            'client_context' => $clientContext,
            'type'           => $type,
            'dedupe_token'   => Signatures::generateUUID(),
            'sampled'        => true,
            'recipient_ids'  => $recipients,
        ];

        if ($action === 'send_intent') {
            $extra['channel'] = 'unset';
        }

        if ($action === 'send_attempt' || $action === 'visual_photo') {
            $extra['channel'] = $channel;
            $extra['is_shh_mode'] = false;
        }

        $event = $this->_addEventBody('direct_message_waterfall', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Direct. Send thread item seen (impression).
     *
     * @param string                                        $clientContext Client context used for sending intent/attempt DM.
     * @param string                                        $threadId      Thread ID.
     * @param \InstagramAPI\Response\Model\DirectThreadItem $threadItem    Direct Thread Item.
     * @param string                                        $action        'send_attempt' or 'sent'.
     * @param string                                        $channel       Channel used for sending the intent/attempt DM. Others: 'rest'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendDirectThreadItemSeen(
        $clientContext,
        $threadId,
        $threadItem,
        $action,
        $channel = 'rest')
    {
        $extra = [
            'type'           => 'thread_item_seen',
            'client_context' => $clientContext,
            'thread_id'      => $threadId,
            'message_id'     => $threadItem->getItemId(),
            'date_created'   => $threadItem->getTimestamp(),
            'action'         => $action,
            'channel'        => $channel,
        ];

        $event = $this->_addEventBody('direct_message_mark_waterfall', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Direct. Send thread inbox impression.
     *
     * @param string $threadId  Thread ID.
     * @param bool   $hasUnseen Has unseen messages.
     * @param int    $position  Position.
     * @param int    $folder    Folder.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendDirectThreadImpression(
        $threadId,
        $hasUnseen,
        $position,
        $folder = -1)
    {
        $extra = [
            'thread_id'      => $threadId,
            'has_unseen'     => $hasUnseen,
            'position'       => $position,
            'folder'         => $folder,
        ];

        $event = $this->_addEventBody('direct_inbox_thread_impression', 'direct_inbox', $extra);
        $this->_addEventData($event);
    }

    /**
     * Direct. Send thread unseen message impression.
     *
     * @param string                                        $threadId   Thread ID.
     * @param \InstagramAPI\Response\Model\DirectThreadItem $threadItem Direct Thread Item.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendThreadUnseenMessageImpression(
        $threadId,
        $threadItem)
    {
        $extra = [
            'message_id'     => $threadItem->getItemId(),
            'message_type'   => $threadItem->getItemType(),
            'thread_id'      => $threadId,
        ];

        $event = $this->_addEventBody('direct_thread_unseen_message_impression', 'direct_thread', $extra);
        $this->_addEventData($event);
    }

    /**
     * Direct thread pagination.
     *
     * @param string      $action 'attempt' or 'success'.
     * @param string|null $cursor Cursor used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendDirectFetchPagination(
        $action,
        $cursor)
    {
        $extra = [
            'action'     => $action,
            'fetch_uuid' => Signatures::generateUUID(),
            'fetch_type' => 'paging_new',
        ];

        if ($cursor !== null) {
            $extra['oldest_cursor'] = $cursor;
        }

        $event = $this->_addEventBody('ig_direct_thread_fetch_success_rate', 'ig_direct', $extra);
        $this->_addEventData($event);
    }

    /**
     * Direct inbox tab impression.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendDirectInboxTabImpression()
    {
        $extra = [
            'tab'   => 0,
        ];

        $event = $this->_addEventBody('direct_inbox_tab_impression', 'direct_inbox', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send direct enter thread.
     *
     * @param mixed $threadId
     * @param mixed $userId
     * @param mixed $position
     * @param mixed $folder
     * @param array $options
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendDirectEnterThread(
        $threadId,
        $userId,
        $position,
        $folder = 0,
        array $options = [])
    {
        $extra = [
            'thread_id'                 => $threadId,
            'inviter'                   => $userId,
            'entry_point'               => 'inbox',
            'is_request_pending'        => isset($options['is_request_pending']) ? $options['is_request_pending'] : false,
            'should_show_permission'    => isset($options['should_show_permission']) ? $options['should_show_permission'] : false,
            'is_unread'                 => isset($options['is_unread']) ? $options['is_unread'] : false,
            'folder'                    => $folder,
            'position'                  => $position,
        ];

        $event = $this->_addEventBody('direct_enter_thread', 'direct_thread', $extra);
        $this->_addEventData($event);
    }

    /**
     * Direct external share option.
     *
     * @param string $mediaId       Instagram's media ID.
     * @param string $shareLocation Share location.
     * @param string $shareOption   Share option.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendExternalShareOption(
        $mediaId,
        $shareLocation = 'direct_share_sheet',
        $shareOption = 'add_to_your_story')
    {
        $extra = [
            'media_id'          => $mediaId,
            'share_location'    => $shareLocation,
            'share_option'      => $shareOption,
        ];

        $event = $this->_addEventBody('external_share_option_impression', 'direct_reshare_sheet', $extra);
        $this->_addEventData($event);
    }

    /**
     * Direct direct share media.
     *
     * @param string      $userId   User ID.
     * @param string|null $threadId Thread ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendDirectShareMedia(
        $userId,
        $threadId = null)
    {
        $extra = [
            'thread_id' => $threadId,
            'a_pk'      => $userId,
        ];

        $event = $this->_addEventBody('direct_share_media', 'direct_reshare_sheet', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send badging event.
     *
     * NOTE: Needs more research.
     *
     * @param mixed $eventType
     * @param mixed $useCase
     * @param mixed $badgeValue
     * @param mixed $badgePosition
     * @param mixed $badgeDisplayStyle
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendBadgingEvent(
        $eventType = 'click',
        $useCase = 'activity_feed',
        $badgeValue = 0,
        $badgePosition = 'badge_position',
        $badgeDisplayStyle = 'dot_badge')
    {
        $extra = [
            'event_type'            => $eventType,
            'use_case_id'           => $useCase,
            'badge_value'           => $badgeValue,
            'badge_position'        => $badgePosition,
            'badge_display_style'   => $badgeDisplayStyle,
        ];

        $event = $this->_addEventBody('badging_event', 'badging', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send Organic Share button.
     *
     * @param \InstagramAPI\Response\Model\Item $item                The item object.
     * @param string                            $followingUserStatus Following status. 'following' or 'not_following'.
     * @param string                            $module              The current module you are. 'feed_contextual_profile',
     *                                                               'feed_contextual_self_profile',
     *                                                               'feed_contextual_chain',
     * @param array                             $clusterData         Cluster data used in 'feed_contextual_chain' module.
     *                                                               'feed_position' zero based position of the media in the feed.
     *                                                               'chaining_session_id' UUIDv4.
     *                                                               'topic_cluster_id' 'explore_all:0' (More info on Discover class).
     *                                                               'topic_cluster_title' 'For You' (More info on Discover class).
     *                                                               'topic_cluster_type' 'explore_all' (More info on Discover class).
     *                                                               'topic_cluster_session_id' UUIDv4.
     * @param array                             $options
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicShareButton(
        $item,
        $followingUserStatus,
        $module,
        array $clusterData = [],
        array $options = [])
    {
        if ($module === 'feed_contextual_profile' || $module === 'feed_contextual_self_profile' || $module === 'feed_short_url') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'm_ix'                      => 1,
                'avgViewPercent'            => 1,
                'maxViewPercent'            => 1,
                'media_thumbnail_section'   => 'grid',
                'entity_page_name'          => $item->getUser()->getUsername(),
                'entity_page_id'            => $item->getUser()->getPk(),
            ];
            if ($module !== 'feed_contextual_self_profile') {
                $extra['follow_status'] = $followingUserStatus;
            }
        } elseif ($module === 'feed_contextual_chain') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => $followingUserStatus,
                'connection_id'             => '180',
                'imp_logger_ver'            => 16,
                'avgViewPercent'            => 1,
                'maxViewPercent'            => 1,
                'chaining_position'         => $clusterData['feed_position'],
                'chaining_session_id'       => $clusterData['chaining_session_id'],
                'm_ix'                      => 0,
                'topic_cluster_id'          => $clusterData['topic_cluster_id'], // example: 'explore_all:0'
                'topic_cluster_title'       => $clusterData['topic_cluster_title'], // example: 'For You'
                'topic_cluster_type'        => $clusterData['topic_cluster_type'], // example: 'explore_all'
                'topic_cluster_debug_info'	 => null,
                'topic_cluster_session_id'	 => $clusterData['topic_cluster_session_id'],
            ];
        } elseif ($module === 'feed_contextual_hashtag') {
            $extra = [
                'id'                        => $item->getId(),
                'm_pk'                      => $item->getId(),
                'hashtag_id'                => $options['hashtag_id'],
                'hashtag_name'              => $options['hashtag_name'],
                'hashtag_follow_status'     => isset($options['following']) ? 'following' : 'not_following',
                'hashtag_feed_type'         => isset($options['feed_type']) ? $options['feed_type'] : 'top',
                'tab_index'                 => isset($options['tab_index']) ? $options['tab_index'] : 0,
                'source_of_action'          => $module,
                'session_id'                => $this->ig->client->getPigeonSession(),
                'media_type'                => $item->getMediaType(),
                'type'                      => 0,
                'section'                   => 0,
                'position'                  => isset($options['position']) ? $options['position'] : '["0","0"]',
            ];
        } elseif ($module === 'feed_timeline') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => 'following',
                'inventory_source'          => 'media_or_ad',
                'm_ix'                      => 0,
                'imp_logger_ver'            => 16,
                'is_eof'                    => false,
            ];
        } else {
            throw new \InvalidArgumentException(sprintf('%s module is not supported.'));
        }

        $event = $this->_addEventBody('instagram_organic_share_button', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Direct. Send text direct message.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendTextDirectMessage()
    {
        $extra = [];
        $event = $this->_addEventBody('direct_composer_send_text', 'direct_thread_toggle', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send string impressions.
     *
     * Impressions of internal strings shown in the app.
     *
     * @param array $impressions Impressions. Format: ['2131821003': 4, '2131821257': 2, '2131821331': 10].
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendStringImpressions(
        $impressions)
    {
        $extra = [
            'impressions'   => $impressions,
            'string_locale' => $this->ig->getLocale(),
        ];
        $event = $this->_addEventBody('android_string_impressions', 'IgResourcesAnalyticsModule', $extra);
        $this->_addEventData($event, 1);
    }

    /**
     * Send navigation tab impression.
     *
     * @param int $mode Mode. 0 - main ig navigation tab, 1 - direct.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendNavigationTabImpression(
        $mode)
    {
        $extra = [
            'app_device_id' => $this->ig->uuid,
        ];

        if ($mode === 0) {
            $extra['tabs'] = [
                'main_home',
                'main_search',
                'main_camera',
                'main_inbox',
                'main_profile',
            ];
        } elseif ($mode === 1) {
            $extra['headers'] = [
                'main_direct',
            ];
        }

        $event = $this->_addEventBody('ig_navigation_tab_impression', 'feed_timeline', $extra);
        $this->_addEventData($event, 1);
    }

    /**
     * Send screenshot detector.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendScreenshotDetector()
    {
        $extra = [
            'screenshot_directory_exists'           => false,
            'phone_model'                           => $this->ig->device->getModel(),
            'has_read_external_storage_permission'  => false,
        ];
        $event = $this->_addEventBody('ig_android_story_screenshot_directory', 'screenshot_detector', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send direct user search picker.
     *
     * This event is sent while searching a user. Everytime you type a character, this event is sent.
     * For example: 'I', 'In', 'Ins', 'Inst', 'Insta'. 5 events sent showing the query.
     *
     * If you click on any of the results, you should call after sending all these events, sendDirectUserSearchSelection().
     *
     * @param string $query The query.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendDirectUserSearchPicker(
        $query)
    {
        $extra = [
            'search_string'   => $query,
        ];
        $event = $this->_addEventBody('direct_compose_search', 'direct_recipient_picker', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send direct user search selection.
     *
     * This is sent when selection a user from the result.
     *
     * @param string      $userId   User ID of account who made the comment in Instagram's internal format.
     * @param int         $position The position on the result list.
     * @param string|null $uuid     UUIDv4.
     * @param string      $module   Module.
     * @param mixed|null  $query
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendDirectUserSearchSelection(
        $userId,
        $position,
        $uuid = null,
        $query = null,
        $module = 'direct_recipient_picker')
    {
        if ($module === 'direct_recipient_picker') {
            if ($uuid === null) {
                throw new \InvalidArgumentException('UUID must not be null.');
            }
            $extra = [
                'position'              => $position,
                'relative_position'     => $position,
                'search_query_length'   => ($query === null) ? 0 : strlen($query),
                'recipient'             => $userId,
                'search_string'         => ($query === null) ? '' : $query,
                'session_id'            => $uuid,
            ];
        } elseif ($module === 'direct_reshare_sheet') {
            if ($query === null) {
                throw new \InvalidArgumentException('Query must not be null.');
            }
            $extra = [
                'position'              => $position,
                'recipient'             => $userId,
                'section_type'          => 'search',
                'search_query_length'   => strlen($query),
            ];
        } else {
            throw new \InvalidArgumentException(sprintf('%s module not supported.', $module));
        }

        $event = $this->_addEventBody('direct_compose_select_recipient', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send group creation.
     *
     * @param string $groupSession UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendGroupCreation(
        $groupSession)
    {
        $extra = [
            'group_session_id'   => $groupSession,
        ];

        $event = $this->_addEventBody('direct_group_creation_create', 'direct_recipient_picker', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send group creation enter.
     *
     * @param string $groupSession UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendGroupCreationEnter(
        $groupSession)
    {
        $extra = [
            'group_session_id'   => $groupSession,
        ];

        $event = $this->_addEventBody('direct_group_creation_enter', 'direct_recipient_picker', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send enter direct thread event.
     *
     * Used when entering a thread.
     * TODO. More cases.
     *
     * @param string|null $threadId   The thread ID.
     * @param string      $sessionId  Session ID.
     * @param string      $entryPoint Entry point.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendEnterDirectThread(
        $threadId,
        $sessionId = null,
        $entryPoint = 'inbox_new_message')
    {
        $extra = [
            'entry_point'        => $entryPoint,
            'inviter'            => $this->ig->account_id,
        ];

        if ($sessionId !== null) {
            $extra['session_id'] = $sessionId;
        }

        if ($threadId !== null) {
            $extra['thread_id'] = $threadId;
        }

        $event = $this->_addEventBody('direct_enter_thread', 'direct_thread', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send search session initiated.
     *
     * @param string $searchSession Search session. UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @see follow example.
     */
    public function sendSearchInitiated(
        $searchSession)
    {
        $extra = [
            'search_session_id'                 => $searchSession,
            'shopping_session_id'               => null,
        ];

        $event = $this->_addEventBody('instagram_search_session_initiated', 'blended_search', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send search results.
     *
     * This event should be sent once you have searched something,
     * and it will send Instagram the results you got.
     *
     * @param string   $queryText       Query text.
     * @param string[] $results         String array of User IDs or hashtag IDs.
     * @param string[] $resultsTypeList String array with the same position as $results with 'USER' or 'HASHTAG'.
     * @param string   $rankToken       The rank token.
     * @param string   $searchSession   Search session. UUIDv4.
     * @param string   $module          'blended_search' or 'search_tags'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @see follow example.
     */
    public function sendSearchResults(
        $queryText,
        $results,
        $resultsTypeList,
        $rankToken,
        $searchSession,
        $module)
    {
        $extra = [
            'search_session_id'                 => $searchSession,
            'results_list'                      => $results,
            'results_type_list'                 => $resultsTypeList,
            'pigeon_reserved_keyword_module'    => $module,
            'rank_token'                        => $rankToken,
            'query_text'                        => $queryText,
            'results_source_list'               => empty($results) ? [] : array_fill(0, count($results) - 1, 'server'),
        ];
        $event = $this->_addEventBody('instagram_search_results', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Sends the selected user from the search results.
     *
     * This event should be sent once you have searched something,
     * and it will send Instagram the results you got.
     *
     * @param string   $queryText       Query text.
     * @param string   $selectedId      Selected User ID or hashtag ID.
     * @param string[] $results         String array of user IDs.
     * @param string[] $resultsTypeList String array with the same position as $results with 'USER' or 'HASHTAG'.
     * @param string   $rankToken       The rank token.
     * @param string   $searchSession   Search session. UUIDv4.
     * @param int      $position        Position in the result page of the selected user.
     * @param string   $selectedType    'USER' or 'HASHTAG'.
     * @param string   $module          Module.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendSearchResultsPage(
        $queryText,
        $selectedId,
        $results,
        $resultsTypeList,
        $rankToken,
        $searchSession,
        $position,
        $selectedType,
        $module)
    {
        $positionList = [];
        for ($c = 0; $c < count($results); $c++) {
            $positionList[] = $c;
        }

        if ($module === 'search_tags') {
            $searchType = 'HASHTAG';
        } elseif ($module === 'search_places') {
            $searchType = 'PLACE';
        } elseif ($module === 'blended_search') {
            $searchType = 'BLENDED';
        }

        $extra = [
            'rank_token'             => $rankToken,
            'query_text'             => $queryText,
            'search_session_id'      => $searchSession,
            'search_type'            => $searchType,
            'selected_type'          => $selectedType,
            'selected_id'            => $selectedId,
            'click_type'             => 'server_results',
            'selected_position'      => $position,
            'results_list'           => $results,
            'selected_follow_status' => 'not_following',
            'results_position_list'  => $positionList,
            'results_type_list'      => $resultsTypeList,
        ];
        $event = $this->_addEventBody('search_results_page', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send clear search history.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendClearSearchHistory()
    {
        $extra = [
            'pigeon_reserved_keyword_module' => 'blended_search_edit_recent',
        ];

        $event = $this->_addEventBody('clear_search_history', 'blended_search_edit_recent', $extra);
        $this->_addEventData($event);
    }

    /**
     * Sends related hashtag items.
     *
     * This info is returned from Hashtag::getSections() in persistent_sections.
     *
     * @param string $entityName  Related hashtag item name.
     * @param string $entityId    Related hashtag item ID.
     * @param string $hashtagId   Hashtag ID.
     * @param string $hashtagName Hashtag name.
     * @param array  $options     Options.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendRelatedHashtagItem(
        $entityName,
        $entityId,
        $hashtagId,
        $hashtagName,
        array $options = [])
    {
        $extra = [
            'entity_type'            => 'hashtag',
            'entity_name'            => '#'.$entityName,
            'entity_id'              => $entityId,
            'hashtag_id'             => $hashtagId,
            'hashtag_name'           => $hashtagName,
            'hashtag_follow_status'  => isset($options['hashtag_follow_status']) ? $options['hashtag_follow_status'] : 'not_following',
            'hashtag_feed_type'      => 'unspecified',
            'tab_index'              => -1,
        ];
        $event = $this->_addEventBody('related_hashtag_item_impression', 'feed_hashtag', $extra);
        $this->_addEventData($event);
    }

    /**
     * Sends follow button tapped.
     *
     * This event should be sent when tapped the follow button.
     *
     * @param string $userId      The user ID.
     * @param string $module      Module.
     * @param array  $navstack    Navstack.
     * @param string $entryModule From which module are you coming from.
     *                            It seems to be only set in blended_search.
     * @param bool   $unfollow    Unfollow flag.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendFollowButtonTapped(
        $userId,
        $module,
        $navstack,
        $entryModule = null,
        $unfollow = false)
    {
        $extra = [
            'request_type'                    => ($unfollow === false) ? 'create' : 'destroy',
            'nav_events'                      => json_encode($navstack),
            'user_id'                         => $userId,
            'follow_status'                   => ($unfollow === false) ? 'following' : 'not_following',
            'entity_id'                       => $userId,
            'entity_type'                     => 'user',
            'entity_follow_status'            => ($unfollow === false) ? 'following' : 'not_following',
            'nav_stack_depth'                 => count($navstack),
            'nav_stack'                       => $navstack,
        ];

        if ($entryModule === 'blended_search') {
            $extra['click_point'] = 'user_profile_header';
            $extra['entry_trigger'] = 'search_navigate_to_user';
            $extra['entry_module'] = 'blended_search';
            $extra['view_type'] = 'vertical';
        }

        $event = $this->_addEventBody('follow_button_tapped', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Sends follow button tapped.
     *
     * This event should be sent when tapped the follow button.
     *
     * @param string $userId    The user ID.
     * @param string $module    Module.
     * @param string $rankToken Rank token.
     * @param bool   $unfollow  Unfollow flag.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendSearchFollowButtonClicked(
        $userId,
        $module,
        $rankToken,
        $unfollow = false)
    {
        $extra = [
            'rank_token'                      => sprintf('%s|%s', $this->ig->account_id, $rankToken),
            'user_id'                         => $userId,
            'inline'                          => false,
            'follow_status'                   => ($unfollow === false) ? 'follow' : 'not_following',
        ];

        $event = $this->_addEventBody('search_follow_button_clicked', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Sends feed button tapped.
     *
     * @param string $module  Module.
     * @param string $tab     'top' or 'recent'.
     * @param array  $options Options.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendFeedButtonTapped(
        $module,
        $tab = 'top',
        array $options = [])
    {
        if ($module === 'feed_hashtag') {
            $name = 'hashtag_feed_button_tapped';
            $extra = [
                'session_id'                => $this->ig->client->getPigeonSession(),
                'hashtag_id'                => $options['hashtag_id'],
                'hashtag_name'              => $options['hashtag_name'],
                'hashtag_follow_status'     => isset($options['following']) ? 'following' : 'not_following',
                'hashtag_feed_type'         => isset($options['feed_type']) ? $options['feed_type'] : 'top',
                'tab_index'                 => ($tab === 'top') ? 0 : 1,
            ];
        } elseif ($module === 'feed_location') {
            $name = 'location_feed_button_tapped';
            $extra = [
                'tab_selected'              => $options['tab_selected'],
                'tab_index'                 => ($options['tab_selected'] === 'recent') ? 1 : 0,
                'entity_page_id'            => $options['entity_page_id'],
                'entity_page_name'          => $options['entity_page_name'],
            ];
        }

        $event = $this->_addEventBody($name, $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send muted media.
     *
     * @param string $type          'post' or 'story'.
     * @param bool   $mute          Wether to mure or not the media.
     * @param bool   $muted         If posts/stories are muted already or not.
     * @param string $userId        Target User ID in Instagram's internal format.
     * @param bool   $targetPrivate If target is private.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendMuteMedia(
        $type,
        $mute,
        $muted,
        $userId,
        $targetPrivate)
    {
        if ($type === 'post' && $mute === true) {
            $name = 'ig_mute_posts';
        } elseif ($type === 'posts' && $mute === false) {
            $name = 'ig_unmute_posts';
        } elseif ($type === 'story' && $mute === true) {
            $name = 'ig_mute_stories';
        } else {
            $name = 'ig_unmute_stories';
        }

        $extra = [
            'target_user_id'        => $userId,
            'target_is_private'     => $targetPrivate,
            'selected_from'         => 'following_sheet',
            'follow_status'         => 'following',
            'reel_type'             => 'story',
        ];

        if ($type === 'post') {
            $extra['target_stories_muted'] = $muted;
        } else {
            $extra['target_posts_muted'] = $muted;
        }

        $event = $this->_addEventBody($name, 'media_mute_sheet', $extra);
        $this->_addEventData($event);
    }

    /**
     * Report media action.
     *
     * @param string $action  Action. 'open_media_dialog'.
     * @param string $mediaId Media ID in Instagram's internal format.
     * @param string $module  Module.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function reportMediaAction(
        $action,
        $mediaId,
        $module = 'feed_contextual_self_profile')
    {
        $extra = [
            'actor_id'  => $this->ig->account_id,
            'action'    => $action,
            'target_id' => $mediaId,
        ];

        $event = $this->_addEventBody('report_media', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send picked media option.
     *
     * The options it appears when you click on the 'three dot' button and shows
     * different options: Share, Copy link, edit, delete...
     *
     * @param string $action   Option. 'DELETE'.
     * @param string $mediaId  Media ID in Instagram's internal format.
     * @param int    $pos      Zero-based position of the media.
     * @param string $module   Module.
     * @param mixed  $option
     * @param mixed  $position
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendMediaPickedOption(
        $option,
        $mediaId,
        $position,
        $module = 'feed_contextual_self_profile')
    {
        $extra = [
            'media_owner_id'    => $this->ig->account_id,
            'option'            => $option,
            'pos'               => $position,
            'media_id'          => $mediaId,
        ];

        $event = $this->_addEventBody('ig_media_option_picked', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Start ingest media.
     *
     * @param string $uploadId    Upload ID.
     * @param int    $mediaType   Media Type.
     * @param string $waterfallId UUIDv4
     * @param bool   $isCarousel  Wether is going to be uploaded as album or not.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function startIngestMedia(
        $uploadId,
        $mediaType,
        $waterfallId,
        $isCarousel)
    {
        $extra = [
            'upload_id'         => $uploadId,
            'session_id'        => $uploadId,
            'media_type'        => $mediaType,
            'from'              => 'NOT_UPLOADED',
            'connection'        => 'WIFI',
            'share_type'        => 'UNKNOWN',
            'waterfall_id'      => $waterfallId,
            'ingest_id'         => $uploadId,
            'ingest_surface'    => 'feed',
            'target_surface'    => 'feed',
            'is_carousel_item'  => $isCarousel,
        ];

        $event = $this->_addEventBody('ig_media_ingest_start', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Start upload attempt.
     *
     * @param string $uploadId    Upload ID.
     * @param int    $mediaType   Media Type.
     * @param string $waterfallId UUIDv4
     * @param bool   $isCarousel  Wether is going to be uploaded as album or not.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function startUploadAttempt(
        $uploadId,
        $mediaType,
        $waterfallId,
        $isCarousel)
    {
        if ($mediaType === 1) {
            $name = 'upload_photo_attempt';
        } else {
            $name = 'upload_video_attempt';
        }

        $extra = [
            'upload_id'         => $uploadId,
            'session_id'        => $uploadId,
            'media_type'        => ($mediaType === 1) ? 'PHOTO' : 'VIDEO',
            'from'              => 'NOT_UPLOADED',
            'connection'        => 'WIFI',
            'share_type'        => 'UNKNOWN',
            'waterfall_id'      => $waterfallId,
            'is_carousel_child' => (string) $isCarousel,
            'reason'            => 'fbupload',
        ];

        $event = $this->_addEventBody($name, null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Start upload attempt.
     *
     * @param string $uploadId    Upload ID.
     * @param int    $mediaType   Media Type.
     * @param string $waterfallId UUIDv4
     * @param bool   $isCarousel  Wether is going to be uploaded as album or not.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function uploadMediaSuccess(
        $uploadId,
        $mediaType,
        $waterfallId,
        $isCarousel)
    {
        if ($mediaType === 1) {
            $name = 'upload_photo_success';
        } else {
            $name = 'upload_video_success';
        }

        $extra = [
            'upload_id'         => $uploadId,
            'session_id'        => $uploadId,
            'media_type'        => ($mediaType === 1) ? 'PHOTO' : 'VIDEO',
            'from'              => 'UPLOADED',
            'connection'        => 'WIFI',
            'share_type'        => 'UNKNOWN',
            'waterfall_id'      => $waterfallId,
            'is_carousel_child' => (string) $isCarousel,
            'reason'            => 'fbupload',
        ];

        $event = $this->_addEventBody($name, null, $extra);
        $this->_addEventData($event);

        $event = $this->_addEventBody('ig_media_upload_success', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Start upload attempt.
     *
     * @param string $status      'attempt' or 'success'.
     * @param string $uploadId    Upload ID.
     * @param int    $mediaType   Media Type.
     * @param string $waterfallId UUIDv4
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendConfigureMedia(
        $status,
        $uploadId,
        $mediaType,
        $waterfallId)
    {
        if ($status === 'attempt') {
            $name = 'configure_media_attempt';
            $timeFromShare = 0;
        } else {
            $name = 'configure_media_success';
            $timeFromShare = mt_rand(3000, 5000);
        }

        if ($mediaType === 1) {
            $mediaType = 'PHOTO';
        } elseif ($mediaType === 2) {
            $mediaType = 'VIDEO';
        } elseif ($mediaType === 8) {
            $mediaType = 'CAROUSEL';
        }

        $extra = [
            'upload_id'                             => $uploadId,
            'session_id'                            => $uploadId,
            'media_type'                            => $mediaType,
            'from'                                  => 'UPLOADED',
            'connection'                            => 'WIFI',
            'share_type'                            => 'FOLLOWERS_SHARE',
            'source_type'                           => '4',
            'original_width'                        => 0,
            'original_height'                       => 0,
            'since_share_seconds'                   => (mt_rand(1000, 3000) + $timeFromShare) / 1000,
            'time_since_last_user_interaction_sec'  => mt_rand(1, 3),
            'waterfall_id'                          => $waterfallId,
            'attempt_source'                        => 'user post',
            'target'                                => 'CONFIGURED',
            'reason'                                => null,
        ];

        $event = $this->_addEventBody($name, null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Start camera session.
     *
     * @param string $sessionId Session UUID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendIGStartCameraSession(
        $sessionId)
    {
        $extra = [
            'session_id'               => $sessionId,
            'entry_point'              => 12,
            'ig_userid'                => $this->ig->account_id,
            'event_type'               => 1,
            'capture_type'             => 1,
            'capture_format_index'     => 0,
            'ar_core_version'          => -1,
        ];

        $event = $this->_addEventBody('ig_camera_start_camera_session', 'ig_camera_client_events', $extra);
        $this->_addEventData($event);
    }

    /**
     * Camera waterfall.
     *
     * @param string $product          Product name.
     * @param string $event            Event.
     * @param string $loggerId         Logger ID.
     * @param string $productSessionId Product session ID.
     * @param string $module           Module.
     * @param mixed  $time             Event time.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendCameraWaterfall(
        $product,
        $event,
        $loggerId,
        $productSessionId,
        $module,
        $time)
    {
        $extra = [
            'event'                                => $event,
            'product_name'                         => $product,
            'logger_session_id'                    => $loggerId,
            'product_session_id'                   => $productSessionId,
            'camera_core_controller'               => 'NotUsed',
            'extras'                               => json_encode([
                'event_time'            => strval($time),
                'maybe_bg_app_state'    => strval(0),
            ]),
            'current_outputs'     => [
                'SurfaceOutput',
            ],
            'texture_memory_bytes'          => 0,
        ];

        if ($event === 'set_input') {
            $event['current_input'] = 'IgCameraVideoInputV1';
            $event['current_input_size'] = '0x0';
        }

        $event = $this->_addEventBody('camera_waterfall', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Nametag session start.
     *
     * @param string $name        Name.
     * @param string $waterfallId Waterfall ID.
     * @param string $startTime   Start time.
     * @param string $currentTime Current tine.
     * @param string $origin      Origin.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendNametagSessionStart(
        $name,
        $waterfallId,
        $startTime,
        $currentTime,
        $origin)
    {
        $extra = [
            'waterfall_id'                                => $waterfallId,
            'start_time'                                  => $startTime,
            'current_time'                                => $currentTime,
            'elapsed_time'                                => $currentTime - $startTime,
        ];

        if ($name === 'ig_nametag_session_start') {
            $extra['origin'] = $origin;
            $extra['has_camera_permission'] = true;
            $extra['has_storage_permission'] = true;
        }

        $event = $this->_addEventBody($name, 'waterfall_instagram_nametag', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send IG camera share media.
     *
     * @param string $sessionId Session ID.
     * @param int    $mediaType Media type. 1 for photo, 2 for video.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendIgCameraShareMedia(
        $sessionId,
        $mediaType)
    {
        $extra = [
            'session_id'                                   => $sessionId,
            'entry_point'                                  => 12,
            'ig_userid'                                    => $this->ig->account_id,
            'event_type'                                   => 2,
            'capture_type'                                 => 1,
            'capture_format_index'                         => 0,
            'media_source'                                 => 1,
            'media_type'                                   => $mediaType,
            'camera_position'                              => 1,
            'share_destination'                            => 1,
            'thread_id'                                    => null,
            'posting_surface'                              => 1,
            'extra_data'                                   => [],
        ];

        $event = $this->_addEventBody('ig_camera_share_media', 'ig_camera_client_events', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send IG camera end post capture session.
     *
     * @param string $sessionId Session ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendIgCameraEndPostCaptureSession(
        $sessionId)
    {
        $extra = [
            'session_id'                                   => $sessionId,
            'entry_point'                                  => 12,
            'ig_userid'                                    => $this->ig->account_id,
            'event_type'                                   => 2,
            'capture_type'                                 => 1,
            'capture_format_index'                         => 0,
        ];

        $event = $this->_addEventBody('ig_camera_end_post_capture_session', 'ig_camera_client_events', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send IG camera end session.
     *
     * @param string $sessionId Session ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendIgCameraEndSession(
        $sessionId)
    {
        $extra = [
            'session_id'                                   => $sessionId,
            'entry_point'                                  => 12,
            'ig_userid'                                    => $this->ig->account_id,
            'event_type'                                   => 2,
            'capture_type'                                 => 1,
            'capture_format_index'                         => 0,
            'exit_point'                                   => 2,
        ];

        $event = $this->_addEventBody('ig_camera_end_camera_session', 'ig_camera_client_events', $extra);
        $this->_addEventData($event);
    }

    /**
     * Prepares Push Notification Settings. Managed automaticallyby the API. Set during cold start login (before).
     *
     * @param bool        $enabled
     * @param string|null $module
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function pushNotificationSettings()
    {
        $extra = [
            'all_notifications_status' => 1,
        ];

        $event = $this->_addEventBody('push_notification_setting', 'NotificationChannelsHelper', $extra);
        $this->_addEventData($event);
    }

    /**
     * Enables Push Notification Settings (event). Managed automaticallyby the API. Set during cold start login (before).
     *
     * @param string|string[] $channels
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function enableNotificationSettings(
        $channels)
    {
        if (!is_array($channels)) {
            $channels = [$channels];
        }

        foreach ($channels as $channel) {
            $extra = [
                'channel_id' => $channel,
            ];

            $extra = $this->_addCommonProperties($extra);
            $event = $this->_addEventBody('notification_channel_enabled', 'NotificationChannelsHelper', $extra);

            $this->_addEventData($event);
        }
    }

    /**
     * Send Instagram Netego Delivery.
     *
     * @param \InstagramAPI\Response\Model\Item $item      The item object.
     * @param string                            $sessionId UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendNetegoDelivery(
        $item,
        $sessionId)
    {
        $extra = [
            'session_id'        => $sessionId,
            'id'                => $item->getId(),
            'netego_id'         => $item->getId(),
            'tracking_token'    => $item->getOrganicTrackingToken(),
            'type'              => 'suggested_users',
        ];

        $event = $this->_addEventBody('instagram_netego_delivery', 'feed_timeline', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send Async ad controller success.
     *
     * @param string $trackingToken Tracking token.
     * @param array  $options       Options to configure the event.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendAsyncAdControllerSuccess(
        $trackingToken,
        array $options = [])
    {
        $extra = [
            'tracking_token'        => $trackingToken,
            'desired_action_pos'    => $options['desired_action_pos'],
            'async_ad_source'       => 'timeline_request',
        ];

        $event = $this->_addEventBody('instagram_ad_async_ad_controller_action_success', 'feed_timeline', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send zero carrier signal.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendZeroCarrierSignal()
    {
        $extra = [
            'event_type'    => 'config_update',
            'config'        => json_encode(['pingConfigs' => []]),
            'url'           => null,
            'status'        => null,
            'success'       => null,
            'state_changed' => null,
        ];

        $extra['pk'] = 0;
        $event = $this->_addEventBody('zero_carrier_signal', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send active interval.
     *
     * @param int $startTime Timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendActiveInterval(
        $startTime)
    {
        $extra = [
            'event_type'    => 'interval_start',
            'start_time'    => $startTime,
        ];

        $event = $this->_addEventBody('ig_active_interval', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send zero url rewrite.
     *
     * @param string $url          Url.
     * @param string $rewrittenUrl Url.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendZeroUrlRewrite(
        $url,
        $rewrittenUrl)
    {
        $extra = [
            'url'               => $url,
            'rewritten_url'     => $rewrittenUrl,
        ];

        $event = $this->_addEventBody('ig_zero_url_rewrite', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send cellular data opt.
     *
     * @param bool $dataSaver If the app has enabled data saver mode.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendCellularDataOpt(
        $dataSaver = false)
    {
        $extra = [
            'data_saver_mode'               => $dataSaver,
            'high_quality_network_setting'  => 1,
            'os_data_saver_settings'        => 1,
        ];

        $event = $this->_addEventBody('ig_cellular_data_opt', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send dark mode opt.
     *
     * @param bool $darkMode If the app has enabled dark mode.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendDarkModeOpt(
        $darkMode = false)
    {
        $extra = [
            'os_dark_mode_settings'     => $darkMode,
            'dark_mode_in_app_toggle'   => intval($darkMode),
            'in_app_dark_mode_setting'  => -1,
        ];

        $event = $this->_addEventBody('ig_dark_mode_opt', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Wellbeing time in app migration impression.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function wellbeingTimeInAppMigrationImpression()
    {
        $extra = [
            'action'                => 'schedule_reminder',
            'is_v1_enabled'         => false,
            'migration_timestamp'   => 0,
        ];

        $event = $this->_addEventBody('wellbeing_timeinapp_ui_migration_impression', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Perf percent render photos.
     *
     * @param string $module  The module where the app state was updated.
     * @param string $mediaId Media ID in Instagram's internal format.
     * @param array  $options Options to configure the event.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendPerfPercentPhotosRendered(
        $module,
        $mediaId,
        array $options = [])
    {
        $extra = [
            'media_id'                          => $mediaId,
            'is_grid_view'                      => isset($options['is_grid_view']) ? $options['is_grid_view'] : false,
            'rendered'                          => isset($options['rendered']) ? $options['rendered'] : false,
            'is_carousel'                       => isset($options['is_carousel']) ? $options['is_carousel'] : false,
            'did_fallback_render'               => isset($options['did_fallback_render']) ? $options['did_fallback_render'] : false,
            'is_ad'                             => false,
            'load_source'                       => 'network',
            'image_size_kb'                     => $options['image_size_kb'],
            'load_time_ms'                      => isset($options['load_time']) ? $options['load_time'] : 0,
            'estimated_bandwidth'               => $options['estimated_bandwidth'],
            'estimated_bandwidth_totalBytes_b'  => $options['estimated_bandwidth_totalBytes_b'],
            'estimated_bandwidth_totalTime_ms'  => $options['estimated_bandwidth_totalTime_ms'],
        ];

        $event = $this->_addEventBody('perf_percent_photos_rendered', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Network trace.
     *
     * TODO: More information needs to be acquired.
     *
     *    'ct' => 'WIFI',
     *    'bw' => -1.0,
     *    'sd' => 312,
     *    'sb' => 1301,
     *    'wd' => 0,
     *    'rd' => 1,
     *    'rb' => 29,
     *    'ts' => 1578549166293,
     *    'sip' => '31.xx.yy.53',
     *    'sc' => 200,
     *    'tt' => 'NmRjNWY4ZWY0YmFkNDIyNzhkZGM5N2QyMWI0MTFhMWJ8ODMuMTAyLjIwMy42MQ==',
     *    'url' => 'https://i.instagram.com/api/v1/feed/reels_media/',
     *    'hm' => 'POST',
     *    'nsn' => 'Instagram',
     *
     * @param array $trace Network trace.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function networkTrace(
        $trace)
    {
        $extra = $this->_addCommonProperties($trace);
        $event = $this->_addEventBody('network_trace', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * qe exposure.
     *
     * @param string $id         exposure   ID.
     * @param string $experiment Experiment.
     * @param string $group      Group.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function qeExposure(
        $id,
        $experiment,
        $group)
    {
        $extra = [
            'id'                => $id,
            'experiment'        => $experiment,
            'group'             => $group,
        ];

        $event = $this->_addEventBody('ig_qe_exposure', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Non feed activation impression.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendNonFeedActivationImpression()
    {
        $extra = [
            'card_type'         => 'follow',
            'pos'               => 3,
            'completed'         => true,
        ];

        $event = $this->_addEventBody('ig_non_feed_activation_impression', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send SSO Status.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendSsoStatus()
    {
        $extra = [
            'enable_igid'   => $this->ig->account_id,
            'enabled'       => 'NO',
            'app_device_id' => $this->ig->uuid,
        ];

        $event = $this->_addEventBody('sso_status', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Launcher badge.
     *
     * @param string $deviceId   UUIDv4.
     * @param int    $badgeCount Badge count.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function launcherBadge(
        $deviceId,
        $badgeCount)
    {
        $extra = [
            'device_id'         => $deviceId,
            'launcher_name'     => 'com.meizu.flyme.launcher',
            'badge_count'       => $badgeCount,
        ];

        $event = $this->_addEventBody('ig_launcher_badge', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Updates the app state.
     *
     * @param string $state  The new app state. 'background', 'foreground'.
     * @param string $module The module where the app state was updated.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function updateAppState(
        $state,
        $module = 'feed_timeline')
    {
        if ($state !== 'background' && $state !== 'foreground') {
            throw new \InvalidArgumentException(sprintf('%s is an invalid state.', $state));
        }

        $extra = [
            'state'     => $state,
            'nav_chain' => $this->ig->getNavChain(),
        ];

        $event = $this->_addEventBody('app_state', $module, $extra);
        $this->_addEventData($event);
    }

    /**
     * Send day of birth pick.
     *
     * @param string $date Date in the following format: YYYY-MM-dd.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendDobPick(
        $date)
    {
        $extra = [
            'to_date' => $date,
        ];

        $event = $this->_addEventBody('dob_picker_scrolled', 'add_birthday', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send IG Nux flow.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendIgNuxFlow()
    {
        $extra = [
            'seen_steps' => '[]',
            'new_flow'   => '{\"1DT\":[\"CHECK_FOR_PHONE\",\"FB_CONNECT\",\"FB_FOLLOW\",\"UNKNOWN\",\"CONTACT_INVITE\",\"ACCOUNT_PRIVACY\",\"TAKE_PROFILE_PHOTO\",\"ADD_PHONE\",\"TURN_ON_ONETAP\",\"DISCOVER_PEOPLE\",\"INTEREST_ACCOUNT_SUGGESTIONS\"]}',
            'old_flow'   => '{\"1DT\":[\"CHECK_FOR_PHONE\",\"FB_CONNECT\",\"FB_FOLLOW\",\"UNKNOWN\",\"CONTACT_INVITE\",\"ACCOUNT_PRIVACY\",\"TAKE_PROFILE_PHOTO\",\"ADD_PHONE\",\"TURN_ON_ONETAP\",\"DISCOVER_PEOPLE\",\"INTEREST_ACCOUNT_SUGGESTIONS\"]}',
        ];

        $extra['pk'] = 0;
        $event = $this->_addEventBody('ig_nux_flow_updated', 'nux_controller_business_logic', $extra);
        $this->_addEventData($event);
    }

    /**
     * Send Instagram Device IDs.
     *
     * @param string $waterfallId Waterfall ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendInstagramDeviceIds(
        $waterfallId)
    {
        $extra = [
            'app_device_id'         => $this->ig->uuid,
            'analytics_device_id'   => null,
            'module'                => 'instagram_device_ids',
            'waterfall_id'          => $waterfallId,
        ];

        $extra['pk'] = 0;
        $event = $this->_addEventBody('instagram_device_ids', null, $extra);
        $this->_addEventData($event, 0);
    }

    /**
     * Send APK testing exposure.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendApkTestingExposure()
    {
        $extra = [
            'build_num' => Constants::VERSION_CODE,
            'installer' => null,
        ];

        $extra['pk'] = 0;
        $event = $this->_addEventBody('android_apk_testing_exposure', null, $extra);
        $this->_addEventData($event, 0);
    }

    /**
     * Send APK signature V2.
     *
     * @param string $module Module.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendApkSignatureV2(
        $module = 'IgFamilyApplicationInitializer')
    {
        $extra = [
            'package_name'          => Constants::PACKAGE_NAME,
            'previous_signature'    => null,
            'signature'             => 'V2_UNTAGGED',
        ];

        $extra['pk'] = 0;
        $event = $this->_addEventBody('apk_signature_v2', $module, $extra);
        $this->_addEventData($event, 0);
    }

    /**
     * Send emergency push initial version.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendEmergencyPushInitialVersion()
    {
        $extra = [
            'current_version' => 46,
        ];

        $extra['pk'] = 0;
        $event = $this->_addEventBody('ig_emergency_push_did_set_initial_version', null, $extra);
        $this->_addEventData($event, 0);
    }

    /**
     * Send emergency push initial version.
     *
     * @param string $waterfallId Waterfall ID.
     * @param int    $state       State.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendInstagramInstallWithReferrer(
        $waterfallId,
        $state)
    {
        $extra = [
            'waterfall_id'  => $waterfallId,
        ];

        if ($state === 0) {
            $extra['referrer'] = 'first_open_waiting_for_referrer';
        } else {
            $extra['referrer'] = null;
            $extra['error'] = 'FEATURE_NOT_SUPPORTED';
        }

        $extra['pk'] = 0;
        $event = $this->_addEventBody('instagram_android_install_with_referrer', 'install_referrer', $extra);
        $this->_addEventData($event);
    }
}
