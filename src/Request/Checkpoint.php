<?php

namespace InstagramAPI\Request;

use InstagramAPI\Response;

/**
 * Functions for managing Checkpoint.
 */
class Checkpoint extends RequestCollection
{
    const MAX_CHALLENGE_ITERATIONS = 15;

    /**
     * Send checkpoint challenge.
     *
     * @param string $checkpointUrl Checkpoint URL.
     * @param bool   $webform       Webform.
     * @param mixed  $post
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckpointResponse
     */
    public function sendChallenge(
        $checkpointUrl,
        $webform = false,
        $post = false)
    {
        $checkpointUrl = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
        }, $checkpointUrl);

        $request = $this->ig->request($checkpointUrl)
            ->setNeedsAuth(false)
            ->setSignedPost(false);

        if ($webform === false) {
            $request
                ->addParam('guid', $this->ig->uuid)
                ->addParam('device_id', $this->ig->device_id);

            if ($post !== false) {
                $request->addPost('', '');
            }

            return $request->getResponse(new Response\CheckpointResponse());
        } else {
            return $request
                ->getResponse(new Response\WebCheckpointResponse());
        }
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
           ->addPost('choice', $verificationMethod)
           ->addPost('guid', $this->ig->uuid)
           ->addPost('device_id', $this->ig->device_id)
           ->addPost('_csrftoken', $this->ig->client->getToken())
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
            ->addPost('security_code', $verificationCode)
            ->addPost('guid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
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
            ->addPost('phone_number', $phoneNumber)
            ->addPost('guid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('_csrftoken', $this->ig->client->getToken());
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
            ->addPost('email', $email)
            ->addPost('guid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('_csrftoken', $this->ig->client->getToken());
        if ($afterLogin === false) {
            $request->setNeedsAuth(false);
        } else {
            $request->addPost('_uid', $this->ig->account_id)
                    ->addPost('_uuid', $this->ig->uuid);
        }

        return $request->getResponse(new Response\CheckpointResponse());
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
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-IG-WWW-Claim', 0)
            ->addHeader('User-Agent', $this->ig->device->getUserAgent())
            ->addHeader('X-CSRFToken', $this->ig->client->getToken())
            ->addHeader('X-IG-App-ID', '936619743392459');
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
