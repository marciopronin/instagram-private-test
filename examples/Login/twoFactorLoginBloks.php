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

    if ($loginResponse !== null && $loginResponse->isTwoFactorRequired() && $loginResponse->getIsBloks()) {
        $twoFactorChallenge = $loginResponse->getTwoFactorChallenge();  // 2FA TYPE ('totp', 'backup', 'sms' and 'email')
        $twoFactorContext = $loginResponse->getTwoFactorContext();

        $verificationCode = trim(fgets(STDIN));
        $ig->finishTwoFactorVerification($username, $password, $twoFactorContext, $twoFactorChallenge, $verificationCode);
    }
    $user = $ig->people->getSelfInfo()->getUser();
} catch (\Exception $e) {
    var_dump($e);
}
