<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../../vendor/autoload.php';

// ///// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
// ////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
\InstagramAPI\Instagram::$skipLoginFlowAtMyOwnRisk = true;

try {
    $loginResponse = $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}

$ig->web->sendPreLogin();
$ig->web->login($username, $password);
$ig->client->saveCookieJar();
