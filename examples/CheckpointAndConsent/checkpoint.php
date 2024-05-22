<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../../vendor/autoload.php';
require __DIR__.'/NoCaptchaProxyless.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $loginResponse = $ig->login($username, $password);

    if ($loginResponse !== null && $loginResponse->isTwoFactorRequired()) {
        $twoFactorIdentifier = $loginResponse->getTwoFactorInfo()->getTwoFactorIdentifier();

        // The "STDIN" lets you paste the code via terminal for testing.
        // You should replace this line with the logic you want.
        // The verification code will be sent by Instagram via SMS.
        $verificationCode = trim(fgets(STDIN));
        $ig->finishTwoFactorLogin($username, $password, $twoFactorIdentifier, $verificationCode);
    }
} catch (Exception $e) {
    if ($e instanceof InstagramAPI\Exception\Checkpoint\ChallengeRequiredException) {
        $iterations = 0;
        $webForm = false;
        $challenge = $e->getResponse()->getChallenge();
        if (!is_array($challenge)) {
            if ($challenge === null) {
                $checkApiPath = substr($e->getResponse()->asArray()['api_path'], 1);
            } else {
                $checkApiPath = substr($challenge->getApiPath(), 1);
            }
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
                            if ($e->getResponse() instanceof InstagramAPI\Response\LoginResponse) {
                                if ($e->getResponse()->getChallenge() !== null && (is_array($e->getResponse()->getChallenge()) === false) && ($e->getResponse()->getChallenge()->getChallengeContext() !== null)) {
                                    $challengeContext = $e->getResponse()->getChallenge()->getChallengeContext();
                                } elseif ($e->getResponse()->getChallengeContext() !== null) {
                                    $challengeContext = $e->getResponse()->getChallengeContext();
                                } else {
                                    $challengeContext = null;
                                }
                            } else {
                                $challengeContext = null;
                            }
                            $res = $ig->checkpoint->sendChallenge($checkApiPath, $challengeContext);
                            if ($res->getAction() !== null) {
                                break 2;
                            }
                        }
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\EscalationInformationalException:
                        $ig->checkpoint->sendAcceptEscalationInformational($checkApiPath);
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\SelectVerifyMethodException:
                        // If condition can be replaced by other logic. This will take always the phone number
                        // if it set, otherwise the email.
                        if (!is_array($e->getResponse()->getStepData())) {
                            if ($e->getResponse()->getStepData()->getPhoneNumber() !== null) {
                                $method = 0;
                            } else {
                                $method = 1;
                            }
                        } else {
                            $method = $e->getResponse()->getStepData()['choice'];
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

                        try {
                            if ($challenge->getLoggedInUser() !== null) {
                                // If code was successfully verified, update login state and send login flow.
                                $ig->finishCheckpoint($challenge);
                                // Break switch and while loop.
                                break 2;
                            }
                        } catch (Exception $ex) {
                            break 2;
                        }
                        if ($challenge->getAction() === 'close') {
                            break 2;
                        }
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\BirthdayRequiredException:
                        $birthday = explode('-', trim(fgets(STDIN))); // 10-12-1970 (dd-mm-yyyy)
                        $day = $birthday[0];
                        $month = $birthday[1];
                        $year = $birthday[2];
                        $challenge = $ig->checkpoint->sendSetBirthDate($checkApiPath, $day, $month, $year);
                        break 2;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\ChangePasswordException:
                        $password = trim(fgets(STDIN));
                        $challenge = $ig->checkpoint->sendSetNewPasswordCheck($checkApiPath, $password);

                        try {
                            if ($challenge->getLoggedInUser() !== null) {
                                // If code was successfully verified, update login state and send login flow.
                                $ig->finishCheckpoint($challenge);
                                // Break switch and while loop.
                                break 2;
                            }
                        } catch (Exception $ex) {
                            break 2;
                        }
                        if ($challenge->getAction() === 'close') {
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
                        $choice = (is_array($e->getResponse()->getStepData())) ? 0 : $e->getResponse()->getStepData()->getChoice();
                        $ig->checkpoint->requestVerificationCode($checkApiPath, $choice);
                        break 2;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\ScrapingWarningException:
                        $ig->checkpoint->sendAcceptScrapingWarning($checkApiPath);
                        break 2;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\DummyStepException:
                        $ig->checkpoint->sendAcceptDummyStep($checkApiPath);
                        break 2;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\ReviewLinkedAccountsException:
                        $ig->checkpoint->sendAcceptReviewLinkedAccounts($checkApiPath);
                        break 2;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\RecaptchaChallengeException:
                        /*
                            This implementation is using proxyless, there is other class to do the same with proxy.
                        */
                        $recaptcha = new NoCaptchaProxyless();
                        $recaptcha->setKey('ANTI-CAPTCHA-KEY'); // This is the API KEY
                        $recaptcha->setWebsiteURL('https://fbsbx.com/captcha/recaptcha/iframe'); // It could be https://i.instagram.com as well
                        $recaptcha->setWebsiteKey('6LdktRgnAAAAAFQ6icovYI2-masYLFjEFyzQzpix'); // This sitekey is always the same.
                        // Older sitekey: 6Lc9qjcUAAAAADTnJq5kJMjN9aD1lxpRLMnCS2TR
                        $recaptcha->createTask(); // returns ID of task but it is set internally.
                        if ($recaptcha->waitForResult()) { // timeout 300 seconds (5 minutes)
                            $googleResponse = $recaptcha->getTaskSolution();
                        } else {
                            // timeout
                            // Some logic for failure cases
                        }
                        // $sitekey = $e->getResponse()->getSitekey();
                        //$googleResponse = trim(fgets(STDIN));
                        $ig->settings->set('csrftoken', $e->getResponse()->getCsrftoken());
                        $ig->checkpoint->sendCaptchaResponse($e->getResponse()->getChallengeUrl(), $googleResponse);
                        break 2; // Captcha solved!
                    case $e instanceof InstagramAPI\Exception\Checkpoint\EscalationChallengeInformationException:
                        $ig->checkpoint->sendAcceptEscalationInformational($e->getResponse()->getChallengeUrl());
                        break 2;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\ScrapingWarningFormException:
                        $ig->checkpoint->sendWebAcceptScrapingWarning($e->getResponse()->getChallengeUrl());
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
                    case $e instanceof InstagramAPI\Exception\Checkpoint\UFACBloksException:
                    case $e instanceof InstagramAPI\Exception\Checkpoint\SelfieCaptchaException:
                        echo 'Account on moderation';
                        exit();
                        break 2;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\SelectContactPointRecoveryFormException:
                        $ig->checkpoint->selectVerificationMethodForm($e->getResponse()->getChallengeUrl(), $e->getResponse()->getVerificationChoice());
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\ReviewContactPointChangeFormException:
                        $ig->checkpoint->selectAcceptCorrectForm($e->getResponse()->getChallengeUrl());
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\IeForceSetNewPasswordFormException:
                        $newPassword = trim(fgets(STDIN));
                        $ig->account->changePassword($password, $newPassword);
                        break 2;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\AcknowledgeFormException:
                        $ig->checkpoint->sendChallenge(substr($e->getResponse()->getChallengeUrl(), 1), null, true);
                        break;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\LegacyForceSetNewPasswordFormException:
                        $newPassword = trim(fgets(STDIN));
                        $ig->checkpoint->sendSetNewPassword($e->getResponse()->getChallengeUrl(), $newPassword);
                        break 2;
                    case $e instanceof InstagramAPI\Exception\Checkpoint\ReviewLoginFormException:
                        $choice = 0; // It was me = 0, It wasn't me = 1.
                        $ig->checkpoint->sendWebReviewLoginForm($e->getResponse()->getChallengeUrl(), $choice);
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
    // Your code
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
