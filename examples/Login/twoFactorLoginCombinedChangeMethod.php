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

    if ($loginResponse !== null && $loginResponse->isTwoFactorRequired() && $loginResponse->getIsBloks()) { // BLOKS
        $twoFactorChallenge = $loginResponse->getTwoFactorChallenge(); // 2FA TYPE ('totp', 'backup_codes', 'sms' and 'email')
        $twoFactorContext = $loginResponse->getTwoFactorContext();

        // Received challenge (2FA method) is `notification` but you want to use other method instead:
        if ($twoFactorChallenge !== 'backup_codes') {
            $availableMethods = $ig->getAvailableTwoFactorMethods($twoFactorContext);
            if (in_array('backup_codes', $availableMethods)) {
                $twoFactorChallenge = 'backup_codes';
                $ig->selectTwoFactorMethod($twoFactorContext, $twoFactorChallenge);
            }
        }

        $verificationCode = trim(fgets(STDIN));
        $ig->finishTwoFactorVerification($username, $password, $twoFactorContext, $twoFactorChallenge, $verificationCode);
    } elseif ($loginResponse !== null && $loginResponse->isTwoFactorRequired()) { // LEGACY 2FA
        $twoFactorInfo = $loginResponse->getTwoFactorInfo();
        $twoFactorIdentifier = $twoFactorInfo->getTwoFactorIdentifier();
        if (($twoFactorInfo->getSmsTwoFactorOn() === true) && ($twoFactorInfo->getTotpTwoFactorOn() === false)) {
            $method = 1; // SMS
        } else {
            $method = 3; // TOTP
        }

        // The "STDIN" lets you paste the code via terminal for testing.
        // You should replace this line with the logic you want.
        // The verification code will be sent by Instagram via SMS.
        $verificationCode = trim(fgets(STDIN));
        $ig->finishTwoFactorVerification($username, $password, $twoFactorIdentifier, $verificationCode, $method);
    }
} catch (\Exception $e) {
    var_dump($e);
}
