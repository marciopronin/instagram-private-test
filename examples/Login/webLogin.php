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

$re = '/handleWithCustomApplyEach\(ScheduledApplyEach,(.*)\);}\);}\);<\/script>/m';
preg_match_all($re, $ig->web->sendPreLogin(), $matches, PREG_SET_ORDER, 0);
$data = json_decode($matches[0][1], true);

foreach ($data['define'] as $entry) {
    if ($entry[0] === 'XIGSharedData') {
        $csrftoken = $entry[2]['native']['config']['csrf_token'];
        break;
    }
}

$ig->web->login($username, $password, $csrftoken);
$ig->client->saveCookieJar();
echo $ig->client->getCookieJarAsJSON();
