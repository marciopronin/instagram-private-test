<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../../vendor/autoload.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $loginResponse = $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}

$csrftoken = json_decode($ig->web->sendPreLogin(), true)['config']['csrf_token'];
$ig->web->login($username, $password, $csrftoken);
$ig->client->saveCookieJar();
// $ig->client->getCookieJarAsJSON()
