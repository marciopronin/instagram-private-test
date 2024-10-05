<?php

// Instagram Credentials
define('IG_USERNAME', '');
define('IG_PASS', '');
define('STORAGE_PATH', '');
define('VENDOR_PATH', '');
define('DEBUG_MODE', false);

/*
 * Settings below this line are optional!
 */

// General Settings
define('UPDATE_AUTO', false); // Change to true if you want the script to automatically update itself without having to run the update.php script
define('STREAM_RECOVERY', true); // Change to false if you want to disable automatic stream recovery (May improve performance when disabled)

// OBS Settings
define('OBS_MODIFY_SETTINGS', false); // Change this to false if you want the script to only modify the stream url and key and not resolution
define('OBS_BITRATE', '4000');

define('OBS_CUSTOM_PATH', 'INSERT_PATH'); // **OPTIONAL** Specify a custom path for the script to search for an obs executable
define('OBS_EXEC_NAME', 'obs64.exe'); // Recommend you don't touch this unless you modify the custom path & know what you're doing

define('OBS_X', '1080'); // You shouldn't touch this
define('OBS_Y', '1794'); // You shouldn't touch this

// Web console settings
define('WEB_HOST', '10.10.10.22'); // The IP to bind the web console to
define('WEB_PORT', '9001'); // The port to bind the web console to

define('ANALYTICS_OPT_OUT', false); // Change to true if you want to opt of of anonymous analytics.

// Config Metadata
define('configVersionCode', '9'); // You shouldn't touch this
