<?php

namespace InstagramAPI;

class Constants
{
    // Core API Constants.
    const API_URLS = [
        1   => 'https://i.instagram.com/api/v1/',
        2   => 'https://i.instagram.com/api/v2/',
    ];
    const GRAPH_API_URL = 'https://graph.instagram.com/logging_client_events';
    const IG_VERSION = '249.0.0.20.105';
    const IG_IOS_VERSION = '212.1.0.25.118';
    const VERSION_CODE = '373310554';
    const IG_IOS_VERSION_CODE = '329643252';
    const IOS_MODEL = 'iPhone13,2';
    const IOS_VERSION = '14_1';
    const IOS_DPI = '750x1334';
    const IG_SIG_KEY = '3ab43afe09a883498054ae3584340e4a27b900e4f296e411a8f98ecf6db2bbc4';
    const MOBILE_CONFIG_EXPERIMTENTS = ['ig_android_dark_mode_user_override', 'ig_dynamic_ar_ads', 'ig_ios_dark_mode_toggle_dummy', 'ig_android_memory_instance_event', 'ig_session_chain_configs', 'igd_large_account_error_status_config', 'igd_professional_inbox_message_requests_upranking_v2', 'ig_android_smb_professional_account_signup_for_fb_ci_device_id_launcher', 'ig_smb_android_create_pro_device_level_backtest_launcher', 'ig_android_camera_network_activity_logger', 'ig_android_reels_cross_posting_deviceid_launcher', 'ig_android_crosspost_to_fb_attribution_clickable_overwrite', 'ig_android_direct_messaging_tab_device', 'ig_android_keyboard_detector_device', 'always_use_server_recents', 'fbns', 'ig_android_oppo_badging_fix_android_11', 'android_fx_access_device_library_ig4a', 'caa_ar_native_integration_point_ig_logged_out', 'caa_ig4a_google_oauth_killswitch', 'caa_ig4a_smartlock_delete_killswitch', 'caa_ig4a_smartlock_fetch_killswitch', 'caa_ig4a_smartlock_handle_incorrect_pwd_killswitch', 'caa_ig4a_smartlock_save_killswitch', 'caa_ig_aymh_keychain_killswitch', 'caa_login_native_integration', 'caa_password_flow_bloks_login_api', 'fx_android_replicated_storage_ig', 'fx_ig4a_cal_reg_account_recovery', 'fx_ig4a_nux_import_profile_pic_launcher', 'fx_ig4a_sac_linking_device_id_client_launcher', 'fx_ig_show_is_after_nux_linking_android', 'fx_ig_show_nux_is_android_upsell', 'ig4a_fdid_aa_test', 'ig4a_fdid_asdid_sync_killswitch', 'ig4a_fdid_oe_validation_launcher', 'ig4a_multiple_ar_fdid_backtest_launcher', 'ig4a_multiple_ar_lid_backtest_launcher', 'ig4a_reg_cal_contact_point_claiming_killswitch', 'ig_account_recovery_prefill', 'ig_android_access_control', 'ig_android_account_switch_black_banner_go_back_cta', 'ig_android_always_show_password_reg', 'ig_android_autobackup_killswitch', 'ig_android_caa_bloks_testing_contact_point_screen', 'ig_android_cal_nux', 'ig_android_cal_reg_fix_kill_switch', 'ig_android_device_quick_promotion', 'ig_android_double_tap_additional_entry_points_config', 'ig_android_double_tap_to_switch_timeout_config', 'ig_android_entry_point_to_cis_surface', 'ig_android_eu_data_transfer_consent', 'ig_android_explicit_tos_screen', 'ig_android_fix_username_invalid_character_error_message', 'ig_android_fx_linking_survey_with_callback', 'ig_android_import_content_to_new_account_launcher', 'ig_android_launcher_shortcut_for_account_switch_config', 'ig_android_legacy_consent_platform_device_based_v1', 'ig_android_long_press_education_tooltip_profile_entrypoint_launcher', 'ig_android_loose_the_limit_to_add_account_in_account_switcher_launcher', 'ig_android_mac_mimicry_owner', 'ig_android_mimicry_on_profile_owner', 'ig_android_mimicry_on_profile_visitor', 'ig_android_mutiple_account_launcher_badge_config_v2', 'ig_android_never_show_add_age_link', 'ig_android_one_tap_upsell_dialog_migration', 'ig_android_password_creation_for_passwordless_user_config', 'ig_android_recovery_password_reset_password_visibility', 'ig_android_registration_phone_field_direction_experiment', 'ig_android_remove_invalid_nonce', 'ig_android_share_post_to_other_account_launcher', 'ig_android_smart_prefill_killswitch', 'ig_android_sony_badging', 'ig_android_stop_using_server_corrected_email', 'ig_ci_ndx_upsell_learn_more', 'ig_contact_upload_policy_launcher', 'ig_save_ccu_state_after_account_creation_v2', 'ndx_ig4a_ma_as_experiment', 'ndx_ig4a_ma_as_killswitch', 'paid_ads_ig4a_attribution_sessionless', 'paid_ads_ig4a_privacy_compliance', 'fizz_ig_android', 'ig4a_proxy_service_sessionless', 'ig_android_app_release_channel', 'ig_android_app_startup_sticky_country', 'ig_android_bitmap_cache_weak_ref_cleaner', 'ig_android_data_mutation_launcher', 'ig_android_device_network_loader_scheduler_cancel', 'ig_android_drawable_usage_logging', 'ig_android_flytrap_screenshot', 'ig_android_force_switch_dialog_device', 'ig_android_image_clear_disk_cache', 'ig_android_layout_oom', 'ig_android_leak_bitmap', 'ig_android_memory_leak_reporting', 'ig_android_memory_manager_lib', 'ig_android_mx_foldable_infra', 'ig_android_os_version_blocking_config', 'ig_android_qpl_server_push_phase_metadata', 'ig_android_qpl_use_runtime_stats', 'ig_android_release_velocity_dod', 'ig_android_roomdb_session_end_close', 'ig_android_saf_deeplink', 'ig_session_change_fix', 'ig_user_mismatch_soft_error', 'sonar_prober_launcher', 'user_model_configuration', 'live_special_codec_size_list', 'qe_ig_android_device_detection_info_upload', 'qe_ig_android_device_info_foreground_reporting', 'qe_ig_android_device_verification_fb_signup', 'qe_ig_android_device_verification_separate_endpoint', 'qe_ig_android_fb_account_linking_sampling_freq_universe', 'qe_ig_android_gmail_oauth_in_reg', 'qe_ig_android_login_identifier_fuzzy_match', 'qe_ig_android_passwordless_account_password_creation_universe', 'qe_ig_android_quickcapture_keep_screen_on', 'qe_ig_android_security_intent_switchoff', 'qe_ig_android_sim_info_upload', 'qe_ig_android_suma_landing_page', 'qe_ig_growth_android_profile_pic_prefill_with_fb_pic_2', 'ig4a_analytics_module_class_override_logging', 'ig4a_bug_report_endpoint_with_module_class', 'ig4a_mobileconfig_device', 'ig_android_aed', 'ig_android_badging_on_transsion', 'ig_android_push_token_security_change', 'ig_device_session_canary_test', 'igns_af_dot_badging', 'read_cached_login_users_anr_device', 'ig_privacy_core_policy_zones_android', 'multiple_account_badging_platform_migration_new', 'ig_android_seller_actions_deeplink_config', 'ig_android_creation_external_storage_permission', 'ig_android_recycleravatar', 'ig_threads_android_cf_plus_notif_killswitch', 'ig_threads_android_post_sunset', 'ig_android_bottom_sheet_lifecycle_fix', 'ig_android_component_ax_device_id', 'ig_android_component_ax_device_id_v2', 'ig_android_fragmen_navigation_concurrency_fix', 'ig_android_igds_illustration_override_enabled_killswitch', 'ig_ax_action_bar_focus', 'ig_branding_2021_launcher', 'ig_downloadable_images', 'ig_igds_android_panavision_buttons', 'ig_ikons_sl', 'ig_android_codec_high_profile', 'ig_android_media_codec_info_collection', 'ig_android_pendingmedia_gc_ttl', 'ig_android_video_width_policy_deviceid', 'ig_android_uhl_entrypoint_content'];
    const SIG_KEY_VERSION = '4';
    const SIG_KEY_IOS_VERSION = '5';

    const IG_LOGIN_DEFAULT_PUBLIC_KEY = "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvcu1KMDR1vzuBr9iYKW8\nKWmhT8CVUBRkchiO8861H7zIOYRwkQrkeHA+0mkBo3Ly1PiLXDkbKQZyeqZbspke\n4e7WgFNwT23jHfRMV/cNPxjPEy4kxNEbzLET6GlWepGdXFhzHfnS1PinGQzj0ZOU\nZM3pQjgGRL9fAf8brt1ewhQ5XtpvKFdPyQq5BkeFEDKoInDsC/yKDWRAx2twgPFr\nCYUzAB8/yXuL30ErTHT79bt3yTnv1fRtE19tROIlBuqruwSBk9gGq/LuvSECgsl5\nz4VcpHXhgZt6MhrAj6y9vAAxO2RVrt0Mq4OY4HgyYz9Wlr1vAxXXGAAYIvrhAYLP\n7QIDAQAB\n-----END PUBLIC KEY-----\n";
    const IG_LOGIN_DEFAULT_PUBLIC_KEY_ID = '41';
    const IG_LOGIN_PUBLIC_KEY = 'LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUF1enRZOEZvUlRGRU9mK1RkTGlUdAplN3FIQXY1cmdBMmk5RkQ0YjgzZk1GK3hheW14b0xSdU5KTitRanJ3dnBuSm1LQ0QxNGd3K2w3TGQ0RHkvRHVFCkRiZlpKcmRRWkJIT3drS3RqdDdkNWlhZFdOSjdLczlBM0NNbzB5UktyZFBGU1dsS21lQVJsTlFrVXF0YkNmTzcKT2phY3ZYV2dJcGlqTkdJRVk4UkdzRWJWZmdxSmsrZzhuQWZiT0xjNmEwbTMxckJWZUJ6Z0hkYWExeFNKOGJHcQplbG4zbWh4WDU2cmpTOG5LZGk4MzRZSlNaV3VxUHZmWWUrbEV6Nk5laU1FMEo3dE80eWxmeWlPQ05ycnF3SnJnCjBXWTFEeDd4MHlZajdrN1NkUWVLVUVaZ3FjNUFuVitjNUQ2SjJTSTlGMnNoZWxGNWVvZjJOYkl2TmFNakpSRDgKb1FJREFRQUIKLS0tLS1FTkQgUFVCTElDIEtFWS0tLS0tCg==';

    // Endpoint Constants.
    const BLOCK_VERSIONING_ID = '33bf851d4ffa1459309fc7b28463c5d91ffc7aaad80d1c5f9a8a4ed728e319f7';
    const IOS_BLOCKS_VERSIONING_ID = '0d38efe9f67cf51962782e8aae19001881099884d8d86c683d374fc1b89ffad1';
    const BATCH_SURFACES = [
        ['4715', ['instagram_other_profile_page_header']],
        ['5734', ['instagram_other_profile_page_prompt']],
        ['5858', ['instagram_other_profile_tooltip', 'instagram_other_checkout_profile_tooltip']],
    ];
    const BATCH_QUERY = 'Query QuickPromotionSurfaceQuery: Viewer{viewer(){eligible_promotions.trigger_context_v2(<trigger_context_v2>).ig_parameters(<ig_parameters>).trigger_name(<trigger_name>).surface_nux_id(<surface>).external_gating_permitted_qps(<external_gating_permitted_qps>).supports_client_filters(true).include_holdouts(true){edges{client_ttl_seconds,log_eligibility_waterfall,is_holdout,priority,time_range{start,end},node{id,promotion_id,logging_data,max_impressions,triggers,contextual_filters{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}},clauses{clause_type,filters{filter_type,unknown_action,value{name,required,bool_value,int_value,string_value},extra_datas{name,required,bool_value,int_value,string_value}}}}}},is_uncancelable,template{name,parameters{name,required,bool_value,string_value,color_value}},creatives{title{text},content{text},footer{text},social_context{text},social_context_images,primary_action{title{text},url,limit,dismiss_promotion},secondary_action{title{text},url,limit,dismiss_promotion},dismiss_action{title{text},url,limit,dismiss_promotion},image.scale(<scale>){uri,width,height},dark_mode_image.scale(<scale>){uri,width,height}}}}}}}';
    const BATCH_VERSION = 1;

    // User-Agent Constants.
    const USER_AGENT_LOCALE = 'en_US'; // "language_COUNTRY".

    // HTTP Protocol Constants.
    const ACCEPT_LANGUAGE = 'en-US'; // "language-COUNTRY".
    const ACCEPT_ENCODING = 'gzip, deflate';
    const CONTENT_TYPE = 'application/x-www-form-urlencoded; charset=UTF-8';
    const X_IG_Connection_Type = 'WIFI';
    const X_IG_Capabilities = '3brTv10=';
    const IOS_X_IG_Capabilities = '36r/F38=';
    const X_FB_HTTP_Engine = 'Liger';

    // Supported Capabilities
    const SUPPORTED_CAPABILITIES = [
        [
            'name'    => 'SUPPORTED_SDK_VERSIONS',
            'value'   => '119.0,120.0,121.0,122.0,123.0,124.0,125.0,126.0,127.0,128.0,129.0,130.0,131.0,132.0,133.0,134.0,135.0,136.0,137.0,138.0,139.0,140.0,141.0,142.0,143.0,144.0,145.0',
        ],
        [
            'name'  => 'FACE_TRACKER_VERSION',
            'value' => '14',
        ],
        [
            'name'  => 'COMPRESSION',
            'value' => 'ETC2_COMPRESSION',
        ],
        [
            'name'  => 'world_tracker',
            'value' => 'world_tracker_enabled',
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
