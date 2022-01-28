<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../../vendor/autoload.php';

class ExtendedInstagram extends \InstagramAPI\Instagram
{
    /**
     * Set the active account for the class instance.
     *
     * We can call this multiple times to switch between multiple accounts.
     *
     * @param string $username Your Instagram username.
     * @param string $password Your Instagram password.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function setUser(
        $username,
        $password)
    {
        $this->_setUser('regular', $username, $password);
    }
}

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////
$step = '';
$checkApiPath = '';
//////////////////////

$ig = new ExtendedInstagram($debug, $truncatedDebug);

try {
    if ($step === 'login') {
        $loginResponse = $ig->login($username, $password);

        if ($loginResponse !== null && $loginResponse->isTwoFactorRequired()) {
            $twoFactorIdentifier = $loginResponse->getTwoFactorInfo()->getTwoFactorIdentifier();

            // The "STDIN" lets you paste the code via terminal for testing.
            // You should replace this line with the logic you want.
            // The verification code will be sent by Instagram via SMS.
            $verificationCode = trim(fgets(STDIN));
            $ig->finishTwoFactorLogin($username, $password, $twoFactorIdentifier, $verificationCode);
        }
    } else {
        $ig->setUser($username, $password);
        switch($step) {
            case 'select_method':
                throw new InstagramAPI\Exception\Checkpoint\SelectVerifyMethodException;
                break;
            case 'verify_method':
                throw new InstagramAPI\Exception\Checkpoint\VerifyCodeException;
                break;
        }
    }
} catch (Exception $e) {
    if ($e instanceof InstagramAPI\Exception\Checkpoint\ChallengeRequiredException) {
        if (empty($checkApiPath)) {
            $challenge = $e->getResponse()->getChallenge();
            if (!is_array($challenge)) {
                $checkApiPath = substr($challenge->getApiPath(), 1);
            } else {
                $checkApiPath = substr($challenge['api_path'], 1);
            }
            echo sprintf('Checkpoint path: %s', $checkApiPath);
        }
    }
    try {
        switch (true) {
            case $e instanceof InstagramAPI\Exception\Checkpoint\ChallengeRequiredException:
                if ((is_array($e->getResponse()->getChallenge()) === false) && ($e->getResponse()->getChallenge()->getChallengeContext() !== null)) {
                    $challengeContext = $e->getResponse()->getChallenge()->getChallengeContext();
                } else {
                    $challengeContext = null;
                }
                $res = $ig->checkpoint->sendChallenge($checkApiPath, $challengeContext);
                if ($res->getAction() !== null) {
                    break;
                }
                break;
            case $e instanceof InstagramAPI\Exception\Checkpoint\SelectVerifyMethodException:
                $method = trim(fgets(STDIN));
                $ig->checkpoint->requestVerificationCode($checkApiPath, $method);
                break;
            case $e instanceof InstagramAPI\Exception\Checkpoint\VerifyCodeException:
                $code = trim(fgets(STDIN));
                $challenge = $ig->checkpoint->sendVerificationCode($checkApiPath, $code);

                if ($challenge->getLoggedInUser() !== null) {
                    $ig->finishCheckpoint($challenge);
                    break ;
                } elseif ($challenge->getAction() === 'close') {
                    break ;
                }
                break;
        }
    } catch (Exception $ex) {
        $e = $ex;
    }
}

try {
    // Your code
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
