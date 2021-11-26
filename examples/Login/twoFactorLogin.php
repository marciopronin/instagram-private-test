<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../../vendor/autoload.php';

/////// CONFIG ///////
$username = 'hellogloria_';
$password = 'OldH4gs!';
$debug = true;
$truncatedDebug = false;
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $loginResponse = $ig->login($username, $password);

    if ($loginResponse !== null && $loginResponse->isTwoFactorRequired()) {
        $twoFactorInfo = $loginResponse->getTwoFactorInfo();
        $twoFactorIdentifier = $twoFactorInfo->getTwoFactorIdentifier();
        
		if($twoFactorInfo->getTotpTwoFactorOn()){
			$method = 3; // Two Factor Application
			$showMessage = 'Enter the code in your TOTP application';
		}
		else if($twoFactorInfo->getWhatsappTwoFactorOn()){
			$method = 6; // Code via Whatsapp
			$showMessage = 'Enter the code you received via WhatsApp';
		}
		else if($twoFactorInfo->getSmsTwoFactorOn()){
			$method = 1; // Code via SMS
			$showMessage = 'Enter the code you received via SMS';
		}
		else{
			throw new Exception('Unknown Two Factor method');
		}
        
        // The "STDIN" lets you paste the code via terminal for testing.
        // You should replace this line with the logic you want.
        // The verification code will be sent by Instagram via SMS.
        echo "{$showMessage}: ";
        $verificationCode = trim(fgets(STDIN));
        $ig->finishTwoFactorLogin($username, $password, $twoFactorIdentifier, $verificationCode, $method);
    }
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
