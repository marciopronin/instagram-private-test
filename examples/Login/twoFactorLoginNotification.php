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

$ig = new InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $loginResponse = $ig->login($username, $password);

    if ($loginResponse !== null && $loginResponse->isTwoFactorRequired()) {
        $twoFactorInfo = $loginResponse->getTwoFactorInfo();
        $twoFactorIdentifier = $twoFactorInfo->getTwoFactorIdentifier();
        $pollingNonce = $twoFactorInfo->getTrustedNotificationPollingNonce();

        do {
            $status = $ig->checkTrustedNotificationStatus($username, $twoFactorIdentifier, $pollingNonce)->getReviewStatus();
            sleep(5);
        } while ($status !== 1);

        $ig->finishTwoFactorLogin($username, $password, $twoFactorIdentifier, '', 4, 1800, null, true, $pollingNonce);
    }
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
