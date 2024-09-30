<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../../vendor/autoload.php';

// ///// CONFIG ///////
$username = '';
$debug = true;
$truncatedDebug = false;
// ////////////////////

$ig = new InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->getForgotPasswordLink($username, 'phone');
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
