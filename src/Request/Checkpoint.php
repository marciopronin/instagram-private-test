<?php

namespace InstagramAPI\Request;

use InstagramAPI\Constants;
use InstagramAPI\Response;
use InstagramAPI\Utils;

/**
 * Functions for managing Checkpoint.
 */
class Checkpoint extends RequestCollection
{
    const MAX_CHALLENGE_ITERATIONS = 15;

    /**
     * Send checkpoint challenge.
     *
     * @param string      $checkpointUrl Checkpoint URL.
     * @param string|null $context       Challenge context.
     * @param bool        $post
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckpointResponse
     */
    public function sendChallenge(
        $checkpointUrl,
        $context = null,
        $post = false)
    {
        $checkpointUrl = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
        }, $checkpointUrl);

        $request = $this->ig->request($checkpointUrl)
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->addParam('guid', $this->ig->uuid)
            ->addParam('device_id', $this->ig->device_id);

        if ($context !== null) {
            $request->addParam('challenge_context', $context);
        }

        if ($post !== false) {
            $request->addPost('', '');
        }

        return $request->getResponse(new Response\CheckpointResponse());
    }

    /**
     * Request verficiation method.
     *
     * @param string $checkpointUrl      Checkpoint URL.
     * @param string $verificationMethod Verification method. '1' for EMAIL, '0' for SMS.
     * @param bool   $replay             Resend verification code.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckpointResponse
     */
    public function requestVerificationCode(
        $checkpointUrl,
        $verificationMethod,
        $replay = false)
    {
        if ($replay === true) {
            $checkpointUrl = str_replace('challenge/', 'challenge/replay/', $checkpointUrl);
        }

        return $this->ig->request($checkpointUrl)
           ->setNeedsAuth(false)
           ->setSignedPost(false)
           ->addPost('choice', $verificationMethod)
           ->addPost('guid', $this->ig->uuid)
           ->addPost('device_id', $this->ig->device_id)
           //->addPost('_csrftoken', $this->ig->client->getToken())
           ->getResponse(new Response\CheckpointResponse());
    }

    /**
     * Accept scraping warning.
     *
     * @param string $checkpointUrl Checkpoint URL.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckpointResponse
     */
    public function sendAcceptScrapingWarning(
        $checkpointUrl)
    {
        return $this->ig->request($checkpointUrl)
           ->setNeedsAuth(false)
           ->setSignedPost(false)
           ->addPost('guid', $this->ig->uuid)
           ->addPost('device_id', $this->ig->device_id)
           //->addPost('_csrftoken', $this->ig->client->getToken())
           ->getResponse(new Response\CheckpointResponse());
    }

    /**
     * Accept dummy step.
     *
     * @param string $checkpointUrl Checkpoint URL.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckpointResponse
     */
    public function sendAcceptDummyStep(
        $checkpointUrl)
    {
        return $this->ig->request($checkpointUrl)
           ->setNeedsAuth(false)
           ->setSignedPost(false)
           ->addPost('guid', $this->ig->uuid)
           ->addPost('device_id', $this->ig->device_id)
           //->addPost('_csrftoken', $this->ig->client->getToken())
           ->getResponse(new Response\CheckpointResponse());
    }

    /**
     * Accept review linked accounts.
     *
     * @param string $checkpointUrl Checkpoint URL.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckpointResponse
     */
    public function sendAcceptReviewLinkedAccounts(
        $checkpointUrl)
    {
        return $this->ig->request($checkpointUrl)
           ->setNeedsAuth(false)
           ->setSignedPost(false)
           ->addPost('guid', $this->ig->uuid)
           ->addPost('device_id', $this->ig->device_id)
           ->addPost('choice', 0)
           //->addPost('_csrftoken', $this->ig->client->getToken())
           ->getResponse(new Response\CheckpointResponse());
    }

    /**
     * Send force password change.
     *
     * @param string $checkpointUrl Checkpoint URL.
     * @param string $password      Password to set.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckpointResponse
     */
    public function sendSetNewPasswordCheck(
        $checkpointUrl,
        $password)
    {
        return $this->ig->request($checkpointUrl)
           ->setNeedsAuth(false)
           ->setSignedPost(false)
           ->addPost('enc_new_password1', Utils::encryptPassword($password, '', '', true))
           ->addPost('enc_new_password2', Utils::encryptPassword($password, '', '', true))
           ->addPost('guid', $this->ig->uuid)
           ->addPost('device_id', $this->ig->device_id)
           //->addPost('_csrftoken', $this->ig->client->getToken())
           ->getResponse(new Response\CheckpointResponse());
    }

    /**
     * Send verficiation method.
     *
     * @param string $checkpointUrl    Checkpoint URL.
     * @param string $verificationCode Verification code.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse
     */
    public function sendVerificationCode(
        $checkpointUrl,
        $verificationCode)
    {
        return $this->ig->request($checkpointUrl)
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->addPost('security_code', $verificationCode)
            ->addPost('guid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\LoginResponse());
    }

    /**
     * Set phone number for checkpoint verification.
     *
     * This could be required and enforced by Instagram.
     *
     * @param string $checkpointUrl Checkpoint URL.
     * @param string $phoneNumber   Phone number with country code. Example: 34123456789.
     * @param bool   $afterLogin    Flag to select wether this is going to be sent after login.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckpointResponse
     */
    public function sendVerificationPhone(
        $checkpointUrl,
        $phoneNumber,
        $afterLogin = false)
    {
        $request = $this->ig->request($checkpointUrl)
            ->setSignedPost(false)
            ->addPost('phone_number', $phoneNumber)
            ->addPost('guid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id);
        //->addPost('_csrftoken', $this->ig->client->getToken());
        if ($afterLogin === false) {
            $request->setNeedsAuth(false);
        } else {
            $request->addPost('_uid', $this->ig->account_id)
                    ->addPost('_uuid', $this->ig->uuid);
        }

        return $request->getResponse(new Response\CheckpointResponse());
    }

    /**
     * Set email for checkpoint verification.
     *
     * This could be required and enforced by Instagram.
     *
     * @param string $checkpointUrl Checkpoint URL.
     * @param string $email         Email.
     * @param bool   $afterLogin    Flag to select wether this is going to be sent after login.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckpointResponse
     */
    public function sendVerificationEmail(
        $checkpointUrl,
        $email,
        $afterLogin = false)
    {
        $request = $this->ig->request($checkpointUrl)
            ->setSignedPost(false)
            ->addPost('email', $email)
            ->addPost('guid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id);
        //->addPost('_csrftoken', $this->ig->client->getToken());
        if ($afterLogin === false) {
            $request->setNeedsAuth(false);
        } else {
            $request->addPost('_uid', $this->ig->account_id)
                    ->addPost('_uuid', $this->ig->uuid);
        }

        return $request->getResponse(new Response\CheckpointResponse());
    }

    /**
     * Set birth date.
     *
     * @param string $checkpointUrl Checkpoint URL.
     * @param string $day
     * @param string $month
     * @param string $year
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckpointResponse
     */
    public function sendSetBirthDate(
        $checkpointUrl,
        $day,
        $month,
        $year)
    {
        return $this->ig->request($checkpointUrl)
            ->setSignedPost(false)
            ->addPost('day', $day)
            ->addPost('month', $month)
            ->addPost('year', $year)
            ->addPost('guid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\CheckpointResponse());
    }

    /**
     * Accept an escalation informational.
     *
     * This happens when one of the account's media has violated
     * Instagram's ToS or is using a copyrighted media.
     *
     * @param string $checkpointUrl Checkpoint URL.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebCheckpointResponse
     */
    public function sendAcceptEscalationInformational(
        $checkpointUrl)
    {
        $request = $this->_getWebFormRequest($checkpointUrl);

        return $request
            ->addPost('choice', 0)
            ->getResponse(new Response\WebCheckpointResponse());
    }

    protected function _getWebFormRequest(
        $checkpointUrl)
    {
        return $this->ig->request('https://i.instagram.com'.$checkpointUrl)
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->setAddDefaultHeaders(false)
            ->addHeader('Cookie', $this->_getWebCookieString())
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-IG-WWW-Claim', ($this->ig->settings->get('www_claim') !== null) ? $this->ig->settings->get('www_claim') : 0)
            ->addHeader('User-Agent', sprintf('%s %s', Constants::WEB_CHALLENGE_USER_AGENT, $this->ig->device->getUserAgent()))
            //->addHeader('X-CSRFToken', $this->ig->client->getToken())
            ->addHeader('X-CSRFToken', ($this->ig->settings->get('csrftoken') !== null) ? $this->ig->settings->get('csrftoken') : $this->ig->client->getToken())
            ->addHeader('X-IG-App-ID', '1217981644879628')
            ->addHeader('X-Instagram-AJAX', 'c795b4273c42');
    }

    protected function _getWebCookieString()
    {
        $cookieString = sprintf('authorization=%s; ', $this->ig->settings->get('authorization_header'));
        $cookieString .= sprintf('csrftoken=%s; ', $this->ig->settings->get('csrftoken'));
        $cookieString .= sprintf('ds_user_id=%s; ', $this->ig->settings->get('account_id'));
        $cookieString .= sprintf('ig_did=%s; ', $this->ig->uuid);
        $cookieString .= sprintf('mid=%s; ', $this->ig->settings->get('mid'));
        $cookieString .= sprintf('rur=%s', $this->ig->settings->get('rur'));

        return $cookieString;
    }

    /**
     * Send captcha response.
     *
     * @param string $checkpointUrl Checkpoint URL.
     * @param string $response      Google Recaptcha response.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebCheckpointResponse
     */
    public function sendCaptchaResponse(
        $checkpointUrl,
        $response)
    {
        $request = $this->_getWebFormRequest($checkpointUrl);

        return $request
            ->setIsSilentFail(true)
            ->addPost('g-recaptcha-response', $response)
            ->getResponse(new Response\WebCheckpointResponse());
    }

    /**
     * Select verification method form.
     *
     * @param string $checkpointUrl      Checkpoint URL.
     * @param string $verificationMethod Verification method. '1' for EMAIL, '0' for SMS.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebCheckpointResponse
     */
    public function selectVerificationMethodForm(
        $checkpointUrl,
        $verificationMethod)
    {
        $request = $this->_getWebFormRequest($checkpointUrl);

        return $request
            ->addPost('choice', $verificationMethod)
            ->getResponse(new Response\WebCheckpointResponse());
    }

    /**
     * Select accept/correct form.
     *
     * @param string $checkpointUrl Checkpoint URL.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function selectAcceptCorrectForm(
        $checkpointUrl)
    {
        $request = $this->_getWebFormRequest($checkpointUrl);

        return $request
            ->setIsSilentFail(true)
            ->addPost('choice', 0)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Send phone via web form.
     *
     * @param string $checkpointUrl Checkpoint URL.
     * @param string $phoneNumber   Phone number.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebCheckpointResponse
     */
    public function sendWebFormPhoneNumber(
        $checkpointUrl,
        $phoneNumber)
    {
        $request = $this->_getWebFormRequest($checkpointUrl);

        return $request
            ->addPost('phone_number', $phoneNumber)
            ->getResponse(new Response\WebCheckpointResponse());
    }

    /**
     * Send sms code via web form.
     *
     * @param string $checkpointUrl Checkpoint URL.
     * @param string $securityCode  SMS Code.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebCheckpointResponse
     */
    public function sendWebFormSecurityCode(
        $checkpointUrl,
        $securityCode)
    {
        $request = $this->_getWebFormRequest($checkpointUrl);

        return $request
            ->addPost('security_code', $securityCode)
            ->getResponse(new Response\WebCheckpointResponse());
    }

    /**
     * Send review login web form. (It was me = 0, It wasn't me = 1).
     *
     * @param string $checkpointUrl Checkpoint URL.
     * @param int    $option        Option.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebCheckpointResponse
     */
    public function sendWebReviewLoginForm(
        $checkpointUrl,
        $option)
    {
        $request = $this->_getWebFormRequest($checkpointUrl);

        return $request
            ->addPost('choice', $option)
            ->getResponse(new Response\WebCheckpointResponse());
    }

    /**
     * Send force password change.
     *
     * @param string $checkpointUrl Checkpoint URL.
     * @param string $password      Password to set.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebCheckpointResponse
     */
    public function sendSetNewPassword(
        $checkpointUrl,
        $password)
    {
        $request = $this->_getWebFormRequest($checkpointUrl);

        return $request
            ->setIsSilentFail(true)
            ->addPost('enc_new_password1', Utils::encryptPassword($password, '', '', true))
            ->addPost('enc_new_password2', Utils::encryptPassword($password, '', '', true))
            ->getResponse(new Response\WebCheckpointResponse());
    }

    /**
     * Accept scraping warning.
     *
     * @param string $checkpointUrl Checkpoint URL.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebCheckpointResponse
     */
    public function sendWebAcceptScrapingWarning(
        $checkpointUrl)
    {
        $request = $this->_getWebFormRequest($checkpointUrl);

        return $request
            ->getResponse(new Response\WebCheckpointResponse());
    }

    /**
     * Get webform checkpoint.
     *
     * @param string $checkpointUrl Checkpoint URL.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebCheckpointResponse
     */
    public function getWebFormCheckpoint(
        $checkpointUrl)
    {
        return $this->ig->request($checkpointUrl)
            ->setNeedsAuth(false)
            ->addParam('theme', 'light')
            ->getResponse(new Response\WebCheckpointResponse());
    }
}
