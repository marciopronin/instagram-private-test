<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/config.php';
require VENDOR_PATH.'/autoload.php';
require_once __DIR__.'/utils.php';

/////// CONFIG ///////
$debug = DEBUG_MODE;
$truncatedDebug = false;
//////////////////////

if ($argc > 2) {
    $username = $argv[1];
    $password = $argv[2];
} else {
    echo "================\n";
    echo "Instagram Login\n";
    echo "================\n\n";
    echo 'Insert username: ';
    $username = trim(fgets(STDIN));
    echo 'Insert password: ';
    $password = trim(fgets(STDIN));
    if ($username === null || $password === null) {
        echo 'Usage: php auth.php <username> <password>';
        echo 'Credentials for the accounts are missing.';
        exit();
    }
}

if (STORAGE_PATH !== '') {
    $storagePath =
        [
            'storage'       => 'file',
            'basefolder'    => STORAGE_PATH,
        ];
} else {
    Utils::log("\e[31m[x] Storage path: Please configure STORAGE_PATH in config.php.\e[0m");
    exit();
}

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug, $storagePath);

try {
    $loginResponse = $ig->login($username, $password);

    if ($loginResponse !== null && $loginResponse->isTwoFactorRequired()) {
        $twoFactorIdentifier = $loginResponse->getTwoFactorInfo()->getTwoFactorIdentifier();

        // The "STDIN" lets you paste the code via terminal for testing.
        // You should replace this line with the logic you want.
        // The verification code will be sent by Instagram via SMS.
        echo 'Insert verification code: ';
        $verificationCode = trim(fgets(STDIN));
        echo "\nIf SMS verification type 1, if backup code type 2, if 2FA app type 3, if notification approval type 4";
        $verificationMethod = intval(trim(fgets(STDIN)));
        if ($verificationMethod === 4) {
            do {
                $status = $ig->checkTrustedNotificationStatus($username, $twoFactorIdentifier)->getReviewStatus();
                sleep(5);
            } while($status !== 1);
        }
        $ig->finishTwoFactorLogin($username, $password, $twoFactorIdentifier, $verificationCode, $verificationMethod);
    }
} catch (Exception $e) {
    if ($e instanceof InstagramAPI\Exception\Checkpoint\ChallengeRequiredException) {
        $iterations = 0;
        $webForm = false;
        $challenge = $e->getResponse()->getChallenge();
        if (!is_array($challenge)) {
            $checkApiPath = substr($challenge->getApiPath(), 1);
        } else {
            $checkApiPath = substr($challenge['api_path'], 1);
        }
        while (true) {
            try {
                if (++$iterations >= InstagramAPI\Request\Checkpoint::MAX_CHALLENGE_ITERATIONS) {
                    throw new InstagramAPI\Exception\Checkpoint\ChallengeIterationsLimitException();
                }
                switch (true) {
                    case $e instanceof InstagramAPI\Exception\Checkpoint\ChallengeRequiredException:
                        if ($webForm) {
                            $ig->checkpoint->getWebFormCheckpoint($e->getResponse()->getChallenge()->getUrl());
                        } else {
                            if ($iterations > 5) {
                                $webForm = true;
                            }
                            if ((is_array($e->getResponse()->getChallenge()) === false) && ($e->getResponse()->getChallenge()->getChallengeContext() !== null)) {
                                $ig->checkpoint->sendChallenge($e->getResponse()->getChallenge()->getUrl(), true);
                            } else {
                                // Send a challenge request
                                $ig->checkpoint->sendChallenge($checkApiPath);
                            }
                        }
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\EscalationInformationalException:
                        $ig->checkpoint->sendAcceptEscalationInformational($checkApiPath);
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\SelectVerifyMethodException:
                        // If condition can be replaced by other logic. This will take always the phone number
                        // if it set, otherwise the email.
                        if ($e->getResponse()->getStepData()->getPhoneNumber() !== null) {
                            $method = 0;
                        } else {
                            $method = 1;
                        }
                        // requestVerificationCode() will request a verification code to your EMAIL or
                        // PHONE NUMBER. If you choose method 0, the code will be sent to your PHONE NUMBER.
                        // IF you choose method 1, the code will be sent to your EMAIL.
                        $ig->checkpoint->requestVerificationCode($checkApiPath, $method);
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\VerifyCodeException:
                        // The "STDIN" lets you paste the code via terminal for testing.
                        // You should replace this line with the logic you want.
                        // The verification code will be sent by Instagram via SMS.
                        $code = trim(fgets(STDIN));
                        // `sendVerificationCode()` will send the received verification code from the previous step.
                        // If the checkpoint was bypassed, you will be able to do any other request normally.
                        $challenge = $ig->checkpoint->sendVerificationCode($checkApiPath, $code);

                        if ($challenge->getLoggedInUser() !== null) {
                            // If code was successfully verified, update login state and send login flow.
                            $ig->finishCheckpoint($challenge);
                            // Break switch and while loop.
                            break 2;
                        } elseif ($challenge->getAction() === 'close') {
                            break 2;
                        }
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\SubmitPhoneException:
                        $phone = trim(fgets(STDIN));
                        // Set the phone number for verification.
                        $ig->checkpoint->sendVerificationPhone($checkApiPath, $phone);
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\SubmitEmailException:
                        $email = trim(fgets(STDIN));
                        // Set the email for verification.
                        $ig->checkpoint->sendVerificationEmail($checkApiPath, $email);
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\DeltaLoginReviewException:
                        $ig->checkpoint->requestVerificationCode($checkApiPath, 0);
                        break 2;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\RecaptchaChallengeException:
                        // $sitekey = $e->getResponse()->getSitekey();
                        $googleResponse = trim(fgets(STDIN));
                        $ig->checkpoint->sendCaptchaResponse($e->getResponse()->getChallengeUrl(), $googleResponse);
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\EscalationChallengeInformationException:
                        $ig->checkpoint->sendAcceptEscalationInformational($e->getResponse()->getChallengeUrl());
                        break 2;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\SubmitPhoneNumberFormException:
                        $phone = trim(fgets(STDIN));
                        $ig->checkpoint->sendWebFormPhoneNumber($e->getResponse()->getChallengeUrl(), $phone);
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\SelectVerificationMethodFormException:
                        $verifiationMethod = trim(fgets(STDIN));
                        $ig->checkpoint->selectVerificationMethodForm($e->getResponse()->getChallengeUrl(), $verifiationMethod);
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\VerifySMSCodeFormForSMSCaptchaException:
                    case $e instanceof InstagramAPI\Exception\Checkpoint\VerifyEmailCodeFormException:
                    case $e instanceof InstagramAPI\Exception\Checkpoint\VerifySMSCodeFormException:
                        $securityCode = trim(fgets(STDIN));
                        $ig->checkpoint->sendWebFormSecurityCode($e->getResponse()->getChallengeUrl(), $securityCode);
                        break 2;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\UFACBlockingFormException:
                        echo 'Account on moderation';
                        exit();
                        break 2;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\SelectContactPointRecoveryFormException:
                        $ig->checkpoint->selectVerificationMethodForm($e->getResponse()->getChallengeUrl(), $e->getResponse()->getVerificationChoice());
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\IeForceSetNewPasswordFormException:
                        $newPassword = trim(fgets(STDIN));
                        $ig->account->changePassword($password, $newPassword);
                        break 2;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\AcknowledgeFormException:
                        $ig->checkpoint->sendChallenge(substr($e->getResponse()->getChallengeUrl(), 1), false, true);
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\LegacyForceSetNewPasswordFormException:
                        $newPassword = trim(fgets(STDIN));
                        $ig->checkpoint->sendSetNewPassword($e->getResponse()->getChallengeUrl(), $newPassword);
                        break 2;
                    default:
                        throw new InstagramAPI\Exception\Checkpoint\UnknownChallengeStepException();
                }
            } catch (InstagramAPI\Exception\Checkpoint\ChallengeIterationsLimitException $ex) {
                echo 'Account likely to be blocked.';
                exit();
            } catch (Exception $ex) {
                $e = $ex;
            }
        }
    }
}

try {
    echo "\n\n[*] Already authed!\n\n";
    echo 'Do you want to run goLive directly? [y/n]: ';
    $option = trim(fgets(STDIN));
    if ($option === 'y') {
        echo 'Starting...';
        sleep(1);
        if (Utils::isWindows()) {
            system('cls');
        } else {
            system('clear');
        }
        system(sprintf('php %s/goLive.php --set-user=%s --disableObsMsgs', __DIR__, $username));
    }
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
