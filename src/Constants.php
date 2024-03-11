<?php

namespace InstagramAPI;

class Constants
{
    // Core API Constants.
    const API_URLS = [
        1   => 'https://i.instagram.com/api/v1/',
        2   => 'https://i.instagram.com/api/v2/',
    ];
    const ZR_EXCLUSION = [
        '/graphql_www',
        '/api/v1/creator/creator_info',
    ];
    const GRAPH_API_URL = 'https://graph.instagram.com/logging_client_events';
    const IG_VERSION = '321.0.0.39.106';
    const IG_IOS_VERSION = '212.1.0.25.118';
    const VERSION_CODE = [
        '572963047',
        '572963048',
        '572963035',
        '572963046',
        '572962953',
        '572962937',
        '572962896',
        '572963030',
        '572962925',
    ];

    const IG_IOS_VERSION_CODE = '329643252';
    const IOS_MODEL = 'iPhone13,2';
    const IOS_VERSION = '14_1';
    const IOS_DPI = '750x1334';
    const IG_SIG_KEY = 'a399f367a2e4aa3e40cdb4aab6535045b23db15f3dea789880aa0970463de062';
    const MOBILE_CONFIG_EXPERIMTENTS = ['ads_lift_android_poll_multiselect', 'camera_permission_for_ig_story_ad_request', 'ctwa_ig_organic_bug_logging_sessionless_gate', 'ig_android_dark_mode_user_override', 'ig_android_feed_overlay_launcher', 'ig_dynamic_ar_ads', 'ig_ios_dark_mode_toggle_dummy', 'on_device_install_referrer', 'ig_android_memory_instance_event', 'bloks_android_bottom_sheet_killswitches', 'ig4a_bloks_encode_server_params_function_device_id', 'ig_caa_login_home_caching_mc', 'ig_caa_login_home_screen_caching_launcher', 'ig_cds_bloks_harmonization_sessionless', 'ig_android_create_promote_request_add_target_spec_string_on_boost_again', 'ig_smb_discovery_clickable_profile_category_launcher', 'igd_large_account_error_status_config', 'igd_professional_inbox_message_requests_upranking_v2', 'ig_android_dialview_two_hop_animation_bugfix', 'ig_android_drafts_mutability', 'ig_android_foldable_layout_bug_fix', 'ig_android_reels_cross_posting_deviceid_launcher', 'ig_android_reels_transition_effect_picker_shift_fix', 'ig_camera_flex_mode_v2', 'ig_reels_width_change_large_screens', 'ig_android_quick_capture_camera_destination', 'ig_android_direct_widget', 'ig_android_keyboard_detector_device', 'igd_android_call_xma_long_press', 'always_use_server_recents', 'fbns', 'ig_android_honor_badging_v2', 'android_fx_access_device_library_ig4a', 'caa_ar_native_integration_point_ig_logged_out', 'caa_full_test_ig4a_killswitch', 'caa_ig4a_bloks_spi_trigger_killswitch', 'caa_ig4a_google_oauth_killswitch', 'caa_ig4a_logout_save_password_redesign_killswitch', 'caa_ig4a_multiple_account_toast_killswitch', 'caa_ig4a_smartlock_delete_killswitch', 'caa_ig4a_smartlock_fetch_killswitch', 'caa_ig4a_smartlock_handle_incorrect_pwd_killswitch', 'caa_ig4a_smartlock_save_killswitch', 'caa_ig_aymh_keychain_killswitch', 'caa_ig_password_reset_email_source_handler_killswitch', 'caa_login_native_integration', 'caa_password_flow_bloks_login_api', 'caa_perf_ig_improvements', 'caa_rollout_ig4a_launcher', 'fx_android_replicated_storage_ig', 'fx_feta_prototype_sessionless_ig', 'fx_growth_ig4a_device_based_unified_launcher', 'fx_growth_ig4a_ig_nux_refresh_launcher', 'fx_growth_ig_nux_xmds_migration_android', 'fx_ig4a_cal_reg_account_recovery', 'fx_ig4a_msgr_reg_mc', 'fx_ig4a_sac_linking_device_id_client_launcher', 'fx_ig_android_switcher_regression_asdid', 'fx_ig_android_switcher_wave_1_asdid', 'fx_ig_android_switcher_wave_1_fdid', 'fx_ig_android_switcher_wave_2_3_fdid', 'fx_ig_android_switcher_wave_2_asdid', 'fx_ig_show_is_after_nux_linking_android', 'fx_ig_show_nux_is_android_upsell', 'fx_ig_switcher_v1', 'fx_ig_switcher_wave_3_dogfooding_device', 'fx_linking_disclosure_cta_reg', 'fx_native_auth_targeting_addacct', 'ig4a_android_13_notification_priming_screen', 'ig4a_android_13_notification_recurring_system_dialog', 'ig4a_android_13_priming_screen_device_id', 'ig4a_fdid_aa_test', 'ig4a_fdid_asdid_sync_killswitch', 'ig4a_fdid_oe_validation_launcher', 'ig4a_multiple_ar_fdid_backtest_launcher', 'ig4a_multiple_ar_lid_backtest_launcher', 'ig4a_notification_grouping', 'ig4a_notification_replacing_fix_device_id', 'ig4a_reg_cal_contact_point_claiming_killswitch', 'ig4a_turn_on_notifications_nux_screen', 'ig_account_recovery_prefill', 'ig_android_access_control', 'ig_android_autobackup_killswitch', 'ig_android_cal_nux', 'ig_android_cal_reg_fix_kill_switch', 'ig_android_double_tap_to_switch_timeout_config', 'ig_android_fix_username_invalid_character_error_message', 'ig_android_http_store_eviction_blocker', 'ig_android_launcher_shortcut_for_account_switch_config', 'ig_android_loose_the_limit_to_add_account_in_account_switcher_launcher', 'ig_android_mac_mimicry_owner', 'ig_android_mimicry_on_profile_owner', 'ig_android_mimicry_on_profile_visitor', 'ig_android_mutiple_account_launcher_badge_config_v2', 'ig_android_one_tap_upsell_dialog_migration', 'ig_android_password_creation_for_passwordless_user_config', 'ig_android_process_contact_point_signals_failure_rate_limit_expose_limit_on_request_failure_enabled', 'ig_android_reels_v2_launch_device_country_level', 'ig_android_reels_v2_launch_device_country_level_fdid', 'ig_contact_upload_policy_launcher', 'ig_disable_fb_token_caching', 'ig_e2e_test_bypass_caa_override', 'ig_launcher_replacedob_with_datapolicy_link', 'igns_ig4a_push_notification_logging_device_level', 'ndx_ig4a_ma_as_experiment', 'ndx_ig4a_ma_as_killswitch', 'paid_ads_ig4a_attribution_sessionless', 'xav_switcher_ig4a_badge_impression_cap_experiment_device_based', 'ig4a_proxy_service_sessionless', 'ig_android_app_release_channel', 'ig_android_data_mutation_launcher', 'ig_android_device_network_loader_scheduler_cancel', 'ig_android_drawable_usage_logging', 'ig_android_flytrap_screenshot', 'ig_android_foldable_canonicals_analytics_tracking', 'ig_android_force_switch_dialog_device', 'ig_android_image_clear_disk_cache', 'ig_android_investigation_bad_url_config', 'ig_android_layout_oom', 'ig_android_leak_bitmap', 'ig_android_logging_is_foldable', 'ig_android_memory_leak_reporting', 'ig_android_memory_manager_lib', 'ig_android_os_version_blocking_config', 'ig_android_qpl_server_push_phase_metadata', 'ig_android_saf_deeplink', 'igandroid_fdid_launcher_e2e_test', 'sonar_prober_launcher', 'user_model_configuration', 'live_special_codec_size_list', 'ig_subscription_media_visibility_migration', 'fx_linking_disclosure_cta_sac', 'qe_ig_android_device_info_foreground_reporting', 'qe_ig_android_device_verification_fb_signup', 'qe_ig_android_device_verification_separate_endpoint', 'qe_ig_android_fb_account_linking_sampling_freq_universe', 'qe_ig_android_gmail_oauth_in_reg', 'qe_ig_android_login_identifier_fuzzy_match', 'qe_ig_android_passwordless_account_password_creation_universe', 'qe_ig_android_quickcapture_keep_screen_on', 'qe_ig_android_security_intent_switchoff', 'qe_ig_android_sim_info_upload', 'qe_ig_android_suma_landing_page', 'qe_ig_growth_android_profile_pic_prefill_with_fb_pic_2', 'test_config_three', 'on_device_attribution', 'ig4a_analytics_module_class_override_logging', 'ig4a_mobileconfig_device', 'ig_android_foldable_peek_preview_sizing', 'ig_android_push_token_security_change', 'ig_attestation_android', 'ig_device_session_canary_test', 'ig_dfa_fallback_android', 'ig_profilo_device_params', 'ig_redirect_app_update_settings_to_appmanager', 'ig_remove_install_successfully_notification_setting', 'multiple_account_badging_platform_migration_fdid', 'read_cached_login_users_anr_device', 'ig_mobile_policy_zone_sessionless', 'ig_android_profile_page_button_width', 'ig_android_search_survey_success', 'ig_android_fold_story_recovery', 'ig_android_foldable_collpased_drawing_tool', 'ig_android_foldable_responsive_window_insets', 'ig_android_igds_illustration_override_enabled_killswitch', 'ig_android_status_nav_bar_api_update_launcher', 'ig_branding_2021_launcher', 'ig_downloadable_images', 'ig_foldable_aspect_ratio_changes', 'ig_ikons_sl', 'igds_android_blur_color_update_launcher', 'ig_android_media_codec_info_collection', 'ig_android_pending_media_store_clean_up_fix', 'ig_android_pendingmedia_gc_ttl', 'ig_autoplay_accessibility', 'ig_android_uhl_entrypoint', 'ig_android_uhl_force_input'];
    const SIG_KEY_VERSION = '4';
    const SIG_KEY_IOS_VERSION = '5';

    const IG_LOGIN_DEFAULT_PUBLIC_KEY = "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvcu1KMDR1vzuBr9iYKW8\nKWmhT8CVUBRkchiO8861H7zIOYRwkQrkeHA+0mkBo3Ly1PiLXDkbKQZyeqZbspke\n4e7WgFNwT23jHfRMV/cNPxjPEy4kxNEbzLET6GlWepGdXFhzHfnS1PinGQzj0ZOU\nZM3pQjgGRL9fAf8brt1ewhQ5XtpvKFdPyQq5BkeFEDKoInDsC/yKDWRAx2twgPFr\nCYUzAB8/yXuL30ErTHT79bt3yTnv1fRtE19tROIlBuqruwSBk9gGq/LuvSECgsl5\nz4VcpHXhgZt6MhrAj6y9vAAxO2RVrt0Mq4OY4HgyYz9Wlr1vAxXXGAAYIvrhAYLP\n7QIDAQAB\n-----END PUBLIC KEY-----\n";
    const IG_LOGIN_DEFAULT_PUBLIC_KEY_ID = '41';
    const IG_LOGIN_PUBLIC_KEY = 'LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS1NSUlCSWpBTkJna3Foa2lHOXcwQkFRRUZBQU9DQVE4QU1JSUJDZ0tDQVFFQXZjdTFLTURSMXZ6dUJyOWlZS1c4XG5LV21oVDhDVlVCUmtjaGlPODg2MUg3eklPWVJ3a1Fya2VIQSswbWtCbzNMeTFQaUxYRGtiS1FaeWVxWmJzcGtlXG40ZTdXZ0ZOd1QyM2pIZlJNVi9jTlB4alBFeTRreE5FYnpMRVQ2R2xXZXBHZFhGaHpIZm5TMVBpbkdRemowWk9VXG5aTTNwUWpnR1JMOWZBZjhicnQxZXdoUTVYdHB2S0ZkUHlRcTVCa2VGRURLb0luRHNDL3lLRFdSQXgydHdnUEZyXG5DWVV6QUI4L3lYdUwzMEVyVEhUNzlidDN5VG52MWZSdEUxOXRST0lsQnVxcnV3U0JrOWdHcS9MdXZTRUNnc2w1XG56NFZjcEhYaGdadDZNaHJBajZ5OXZBQXhPMlJWcnQwTXE0T1k0SGd5WXo5V2xyMXZBeFhYR0FBWUl2cmhBWUxQXG43UUlEQVFBQgotLS0tLUVORCBQVUJMSUMgS0VZLS0tLS0K';

    // Endpoint Constants.
    const BLOCK_VERSIONING_ID = '8780913f81fad602722ff64c72d6a4a1a62aef1b8c5ec0fc5eee04861e8d23ca';
    const IOS_BLOCKS_VERSIONING_ID = '0d38efe9f67cf51962782e8aae19001881099884d8d86c683d374fc1b89ffad1';
    const BATCH_SURFACES = '{"8972":["instagram_feed_banner"],"4715":["instagram_feed_header","instagram_post_created","instagram_story_created"],"5858":["instagram_feed_tool_tip","instagram_navigation_tooltip","instagram_featured_product_media_tooltip","instagram_feed_promote_cta_tooltip"],"5734":["instagram_feed_prompt","instagram_branded_content_story_shared","instagram_shopping_enable_auto_highlight_interstitial","instagram_story_created"],"11383":["instagram_feed_bottomsheet"]}';
    const BATCH_QUERY = '{"8972":"Query QuickPromotionSurfaceQuery: Viewer{viewer(){eligible_promotions.trigger_context_v2(<trigger_context_v2>).ig_parameters(<ig_parameters>).trigger_name(<trigger_name>).surface_nux_id(<surface>).external_gating_permitted_qps(<external_gating_permitted_qps>).supports_client_filters(true).include_holdouts(true){edges{client_ttl_seconds,log_eligibility_waterfall,is_holdout,priority,time_range{start,end},node{id,promotion_id,logging_data,is_server_force_pass,max_impressions,triggers,contextual_filters{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}}}}}},is_uncancelable,template{name,parameters{name,required,bool_value,string_value,color_value}},creatives{title{text},content{text},footer{text},social_context{text},social_context_images,primary_action{title{text},url,limit,dismiss_promotion},secondary_action{title{text},url,limit,dismiss_promotion},dismiss_action{title{text},url,limit,dismiss_promotion},bullet_list{title,subtitle,icon{uri,width,height}}image.scale(<scale>){uri,width,height}}}}}}}","4715":"Query QuickPromotionSurfaceQuery: Viewer{viewer(){eligible_promotions.trigger_context_v2(<trigger_context_v2>).ig_parameters(<ig_parameters>).trigger_name(<trigger_name>).surface_nux_id(<surface>).external_gating_permitted_qps(<external_gating_permitted_qps>).supports_client_filters(true).include_holdouts(true){edges{client_ttl_seconds,log_eligibility_waterfall,is_holdout,priority,time_range{start,end},node{id,promotion_id,logging_data,is_server_force_pass,max_impressions,triggers,contextual_filters{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}}}}}},is_uncancelable,template{name,parameters{name,required,bool_value,string_value,color_value}},creatives{title{text},content{text},footer{text},social_context{text},social_context_images,primary_action{title{text},url,limit,dismiss_promotion},secondary_action{title{text},url,limit,dismiss_promotion},dismiss_action{title{text},url,limit,dismiss_promotion},bullet_list{title,subtitle,icon{uri,width,height}}image.scale(<scale>){uri,width,height},dark_mode_image.scale(<scale>){uri,width,height}}}}}}}","5858":"Query QuickPromotionSurfaceQuery: Viewer{viewer(){eligible_promotions.trigger_context_v2(<trigger_context_v2>).ig_parameters(<ig_parameters>).trigger_name(<trigger_name>).surface_nux_id(<surface>).external_gating_permitted_qps(<external_gating_permitted_qps>).supports_client_filters(true).include_holdouts(true){edges{client_ttl_seconds,log_eligibility_waterfall,is_holdout,priority,time_range{start,end},node{id,promotion_id,logging_data,is_server_force_pass,max_impressions,triggers,contextual_filters{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}}}}}},is_uncancelable,template{name,parameters{name,required,bool_value,string_value,color_value}},creatives{title{text},content{text},footer{text},social_context{text},social_context_images,primary_action{title{text},url,limit,dismiss_promotion},secondary_action{title{text},url,limit,dismiss_promotion},dismiss_action{title{text},url,limit,dismiss_promotion},bullet_list{title,subtitle,icon{uri,width,height}}image.scale(<scale>){uri,width,height}}}}}}}","5734":"Query QuickPromotionSurfaceQuery: Viewer{viewer(){eligible_promotions.trigger_context_v2(<trigger_context_v2>).ig_parameters(<ig_parameters>).trigger_name(<trigger_name>).surface_nux_id(<surface>).external_gating_permitted_qps(<external_gating_permitted_qps>).supports_client_filters(true).include_holdouts(true){edges{client_ttl_seconds,log_eligibility_waterfall,is_holdout,priority,time_range{start,end},node{id,promotion_id,logging_data,is_server_force_pass,max_impressions,triggers,contextual_filters{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}}}}}},is_uncancelable,template{name,parameters{name,required,bool_value,string_value,color_value}},creatives{title{text},content{text},footer{text},social_context{text},social_context_images,primary_action{title{text},url,limit,dismiss_promotion},secondary_action{title{text},url,limit,dismiss_promotion},dismiss_action{title{text},url,limit,dismiss_promotion},bullet_list{title,subtitle,icon{uri,width,height}}image.scale(<scale>){uri,width,height},dark_mode_image.scale(<scale>){uri,width,height}}}}}}}","11383":"Query QuickPromotionSurfaceQuery: Viewer{viewer(){eligible_promotions.trigger_context_v2(<trigger_context_v2>).ig_parameters(<ig_parameters>).trigger_name(<trigger_name>).surface_nux_id(<surface>).external_gating_permitted_qps(<external_gating_permitted_qps>).supports_client_filters(true).include_holdouts(true){edges{client_ttl_seconds,log_eligibility_waterfall,is_holdout,priority,time_range{start,end},node{id,promotion_id,logging_data,is_server_force_pass,max_impressions,triggers,contextual_filters{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}}}}}},is_uncancelable,template{name,parameters{name,required,bool_value,string_value,color_value}},creatives{title{text},content{text},footer{text},social_context{text},social_context_images,primary_action{title{text},url,limit,dismiss_promotion},secondary_action{title{text},url,limit,dismiss_promotion},dismiss_action{title{text},url,limit,dismiss_promotion},bullet_list{title,subtitle,icon{uri,width,height}}image.scale(<scale>){uri,width,height}}}}}}}"}';
    const BATCH_VERSION = 1;

    // User-Agent Constants.
    const USER_AGENT_LOCALE = 'en_US'; // "language_COUNTRY".

    // HTTP Protocol Constants.
    const ACCEPT_LANGUAGE = 'en-US'; // "language-COUNTRY".
    const ACCEPT_ENCODING = 'gzip, deflate, br';
    const CONTENT_TYPE = 'application/x-www-form-urlencoded; charset=UTF-8';
    const X_IG_Connection_Type = 'WIFI';
    const X_IG_Capabilities = '3brTv10=';
    const IOS_X_IG_Capabilities = '36r/F38=';
    const X_FB_HTTP_Engine = 'Liger';

    // Supported Capabilities
    const SUPPORTED_CAPABILITIES = [
        [
            'name'    => 'SUPPORTED_SDK_VERSIONS',
            'value'   => '139.0,140.0,141.0,142.0,143.0,144.0,145.0,146.0,147.0,148.0,149.0,150.0,151.0,152.0,153.0,154.0,155.0,156.0,157.0,158.0,159.0,160.0,161.0,162.0,163.0,164.0,165.0,166.0,167.0,168.0,169.0,170.0,171.0,172.0,173.0,174.0,175.0,176.0,177.0,178.0,179.0,180.0',
        ],
        [
            'name'  => 'FACE_TRACKER_VERSION',
            'value' => '14',
        ],
    ];

    // Facebook Constants.
    const FACEBOOK_OTA_FIELDS = 'update%7Bdownload_uri%2Cdownload_uri_delta_base%2Cversion_code_delta_base%2Cdownload_uri_delta%2Cfallback_to_full_update%2Cfile_size_delta%2Cversion_code%2Cpublished_date%2Cfile_size%2Cota_bundle_type%2Cresources_checksum%2Cresources_sha256_checksum%2Callowed_networks%2Crelease_id%7D';
    const FACEBOOK_ORCA_PROTOCOL_VERSION = 20150314;
    const FACEBOOK_ORCA_APPLICATION_ID = '124024574287414';
    const FACEBOOK_ANALYTICS_APPLICATION_ID = '567067343352427';
    const GRAPH_API_ACCESS_TOKEN = 'f249176f09e26ce54212b472dbab8fa8';

    // MQTT Constants.
    const PLATFORM = 'android';
    const FBNS_APPLICATION_NAME = 'MQTT';
    const INSTAGRAM_APPLICATION_NAME = 'Instagram';
    const PACKAGE_NAME = 'com.instagram.android';

    // Internal Feedtype Constants. CRITICAL: EVERY value here MUST be unique!
    const PROFILE_PIC = -1;
    const FEED_TIMELINE = 1;
    const FEED_TIMELINE_ALBUM = 2;
    const FEED_STORY = 3;
    const FEED_DIRECT = 4;
    const FEED_DIRECT_STORY = 5;
    const FEED_TV = 6;
    const FEED_REELS = 9; // Clips
    const FEED_DIRECT_AUDIO = 11;

    // Story view modes.
    const STORY_VIEW_MODE_ONCE = 'once';
    const STORY_VIEW_MODE_REPLAYABLE = 'replayable';
    const STORY_VIEW_MODE_PERMANENT = 'permanent';

    // Web.
    const WEB_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:69.0) Gecko/20100101 Firefox/69.0';
    const WEB_CHALLENGE_USER_AGENT = 'Mozilla/5.0 (Linux; Android 10; MI 8 Build/QKQ1.190828.002; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/90.0.4430.91 Mobile Safari/537.36';

    // Share Type Constants.
    const SHARE_TYPE = [
        'FOLLOWERS_SHARE'                   => 0,
        'DIRECT_SHARE'                      => 1,
        'REEL_SHARE'                        => 2,
        'DIRECT_STORY_SHARE'                => 3,
        'DIRECT_STORY_SHARE_DRAFT'          => 4,
        'REEL_SHARE_AND_DIRECT_STORY_SHARE' => 5,
        'NAMETAG_SELFIE'                    => 6,
        'UNKNOWN'                           => 7,
        'IGTV'                              => 8,
        'IGTV_REACTION'                     => 9,
        'COWATCH_LOCAL'                     => 10,
        'GROUP_REEL_SHARE'                  => 11,
        'ARCHIVE'                           => 12,
        'CLIPS'                             => 13,
        'POST_LIVE_IGTV'                    => 14,
        'POST_LIVE_IGTV_COVER_PHOTO'        => 15,
        'IGWB_SELFIE_CAPTCHA'               => 16,
        'IGWB_ID_CAPTCHA'                   => 17,
        'EFFECT_DEMO_VIDEO'                 => 18,
        'INVALID'                           => 19,
    ];

    const REASONS = [
        'cold_start_fetch',
        'warm_start_fetch',
        'pull_to_refresh',
        'new_follow',
        'find_new_friends',
        'pagination',
        'checkpoint_shown',
        'pill_refresh',
        'none',
    ];

    // PDQ TIME FRAMES FOR VIDEO UPLOADS.
    const PDQ_VIDEO_TIME_FRAMES = [
        0,
        1.0,
        2.0,
        3.0,
    ];

    // Whitelist and blacklist Constants.
    const BLACKLISTED_PASSWORDS = ['summer', '112233445566', '121212', 'iloveu', '654321', 'lovelove', 'hello123', 'asdfghjkl', 'chicken', '1234512345', 'aaaaaa', 'lakers24', 'fuckyou', 'ihateyou', '998877', 'harrystyles', '123456789', '123123123', 'soccer', 'iloveyou123', '1122334455', 'password123', 'vanessa', 'cupcake', '12344321', 'qwe123', 'facebook', 'fucklove', 'bubbles', 'password', '1q2w3e4r', '123123', '11223344', '123456123456', '111222', '123qwe', 'butterfly', 'cookies', 'instagram', 'spongebob', 'fuckoff', 'qwertyuiop', 'bismillah', 'lalala', 'lol123', 'flower', 'destiny', 'barbie', '555555', 'hellokitty', 'iloveme', '12345678910', '1234554321', 'icecream', 'daniel', 'Aa123456', '101010', 'jessica', '102030', '12341234', 'jasmine', 'cookie', '666666', 'princess', 'justin', '1234567890', '222222', '999999', 'iloveyou', '009988', 'abc123', 'baseball', 'zxcvbnm', 'family', '098765', 'instagram1', 'babygirl', 'taylor', '909090', 'onedirection', 'pokemon', 'kobe24', 'nicole', 'qwerty123', '87654321', 'basketball', 'monkey', '1234566', '1234567', 'lollipop', 'lovely', 'banana', 'loveyou', 'liverpool', '123654', 'batman', 'sunshine', 'love1234', 'chocolate', '12345678', 'jordan', 'asdfgh', 'iloveyou1', 'niallhoran', 'ashley', 'isabella', 'spiderman', 'sayang', 'samsung', 'hahaha', '12345', 'beautiful', 'password1', 'fashion', '123abc', '098098', 'awesome', 'qwerty', 'superman', 'incorrect', '111111', '123456', '123321', 'hannah', 'zaynmalik', '0987654321', '112233', 'qazwsx', 'michelle', 'elizabeth', '987654321', 'football', '7777777', 'cupcakes', 'softball', 'friends', 'love123', 'anthony', 'forever', 'loveme', '000000', 'love12', 'charlie', 'barcelona', 'forever21', 'justinbieber', 'purple', 'jordan23', 'rahasia', '123456654321', 'bieber'];
    const PRIDE_HASHTAGS = ['tokyorainbowpride', 'trp2019', 'PrideFestival', 'lgbtqjapan', 'edgesoftherainbow', 'loveislove', 'lgbt', 'lesbian', 'gay', 'bisexual', 'transgender', 'trans', 'queer', 'lgbtq', 'instagay', 'pride', 'gaypride', 'loveislove', 'pansexual', 'lovewins', 'asexual', 'nonbinary', 'queer', 'queerpride', 'pride2019', 'genderqueer', 'bi', 'genderfluid', 'lgbtqqia', 'comingout', 'intersex', 'transman', 'transwoman', 'transvisibility', 'queerart', 'dragqueen', 'dragking', 'dragartist', 'twomoms', 'twodads', 'lesbianmoms', 'gaydads', 'gendernonconforming'];

    // General Constants.
    const SRC_DIR = __DIR__; // Absolute path to the "src" folder.
}
