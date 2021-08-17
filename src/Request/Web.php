<?php

namespace InstagramAPI\Request;

use InstagramAPI\Response;
use InstagramAPI\Utils;

/**
 * Functions related to Instagram Web.
 */
class Web extends RequestCollection
{

    /**
     * Pre login.
     * 
     * Used to get csrftoken.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return string
     */
    public function sendPreLogin()
    {
        if (extension_loaded('sodium') === false) {
            throw new \InstagramAPI\Exception\InternalException('You must have the sodium PHP extension to use web login.');
        }

        return $this->ig->request('https://www.instagram.com/accounts/login/')
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->addHeader('User-Agent', $this->ig->getWebUserAgent())
            ->setAddDefaultHeaders(false)
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->getRawResponse();
    }

    /**
     * Web login.
     *
     * @param string $username
     * @param string $password
     * @param string $csrftoken
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return string
     */
    public function login(
        $username,
        $password,
        $csrftoken)
    {
        if (extension_loaded('sodium') === false) {
            throw new \InstagramAPI\Exception\InternalException('You must have the sodium PHP extension to use web login.');
        }

        $query = [
            'next'        => '/accounts/access_tool/',
            'oneTapUsers' => [$this->ig->account_id],
        ];

        return $this->ig->request('https://www.instagram.com/accounts/login/ajax/')
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->setAddDefaultHeaders(false)
            ->addHeader('User-Agent', $this->ig->getWebUserAgent())
            ->addHeader('X-CSRFToken', $csrftoken)
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('Referer', 'https://www.instagram.com/accounts/login/')
            ->addPost('username', $username)
            ->addPost('enc_password', Utils::encryptPasswordForBrowser($password))
            ->addPost('query_params', json_encode($query))
            ->getRawResponse();
    }

    /**
     * Send signup SMS.
     *
     * @param string $phone The phone number.
     * @param string $mid   Mid value (obtained from cookie).
     * @param string $csrftoken
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return string
     */
    public function sendSignupSms(
        $phone,
        $mid,
        $csrftoken)
    {
        return $this->ig->request('https://www.instagram.com/accounts/send_signup_sms_code_ajax/')
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->setAddDefaultHeaders(false)
            ->addHeader('X-CSRFToken', $csrftoken)
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addPost('client_id', $mid)
            ->addPost('phone_number', $phone)
            ->addPost('phone_id', '')
            ->addPost('big_blue_token', '')
            ->getRawResponse();
    }

    /**
     * Send email verification code.
     *
     * @param string $email The email.
     * @param string $mid   Mid value (obtained from cookie).
     * @param string $csrftoken
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return string
     */
    public function sendEmailVerificationCode(
        $email,
        $mid,
        $csrftoken)
    {
        return $this->ig->request('accounts/send_verify_email/')
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->setAddDefaultHeaders(false)
            ->addHeader('X-CSRFToken', $csrftoken)
            ->addPost('device_id', $mid)
            ->addPost('email', $email)
            ->getRawResponse();
    }

    /**
     * Check email verification code.
     *
     * @param string $email The email.
     * @param string $code  The verification code.
     * @param string $mid   Mid value (obtained from cookie).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return string
     */
    public function checkEmailVerificationCode(
        $email,
        $code,
        $mid)
    {
        return $this->ig->request('accounts/check_confirmation_code/')
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->setAddDefaultHeaders(false)
            ->addHeader('X-CSRFToken', $this->ig->client->getToken())
            ->addPost('code', $code)
            ->addPost('device_id', $mid)
            ->addPost('email', $email)
            ->getRawResponse();
    }

    /**
     * Web registration.
     *
     * @param string $username     The account username.
     * @param string $password     The account password.
     * @param string $name         The name of the account.
     * @param string $phoneOrEmail The phone number or email.
     * @param string $day          Day of birth.
     * @param string $month        Month of birth.
     * @param string $year         Year of bith.
     * @param string $mid          Mid value (obtained from cookie).
     * @param bool   $attempt      Wether it is an attempt or not.
     * @param string $tos          Terms of Service.
     * @param string $smsCode      The SMS code.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return string
     */
    public function createAccount(
        $username,
        $password,
        $name,
        $phoneOrEmail,
        $day,
        $month,
        $year,
        $mid,
        $attempt,
        $smsCode = null,
        $tos = 'row')
    {
        if (extension_loaded('sodium') === false) {
            throw new \InstagramAPI\Exception\InternalException('You must have the sodium PHP extension to use web login.');
        }

        if ($attempt) {
            $endpoint = '/accounts/web_create_ajax/attempt/';
        } else {
            $endpoint = '/accounts/web_create_ajax/';
        }

        $request = $this->ig->request('https://www.instagram.com'.$endpoint)
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->setAddDefaultHeaders(false)
            ->addHeader('X-CSRFToken', $this->ig->client->getToken())
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addPost('enc_password', Utils::encryptPasswordForBrowser($password))
            ->addPost('username', $username)
            ->addPost('first_name', $name)
            ->addPost('month', $month)
            ->addPost('day', $day)
            ->addPost('year', $year)
            ->addPost('client_id', $mid)
            ->addPost('seamless_login_enabled', 1)
            ->addPost('tos_version', $tos);

        if (strpos($phoneOrEmail, '@') !== false) {
            $request->addPost('email', $phoneOrEmail);
        } else {
            $request->addPost('phone_number', $phoneOrEmail);
        }

        if ($attempt === false && (strpos($phoneOrEmail, '@') !== false)) {
            $request->addPost('code', $smsCode);
        } else {
            $request->addPost('sms_code', $smsCode);
        }

        return $request->getRawResponse();
    }

    /**
     * Gets account information.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AccountAccessToolResponse
     */
    public function getAccountData()
    {
        $response = $this->ig->request('https://instagram.com/accounts/access_tool/')
            ->setAddDefaultHeaders(false)
            ->addHeader('User-Agent', $this->ig->getWebUserAgent())
            ->getRawResponse();

        return new Response\AccountAccessToolResponse(json_decode($response, true));
    }

    /**
     * Like a media using Web Session.
     *
     * @param string $mediaId
     * @param string $csrftoken
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function like(
        $mediaId,
        $csrftoken)
    {
        return $this->ig->request("https://instagram.com/web/likes/{$mediaId}/like/")
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->addHeader('X-CSRFToken', $csrftoken)
            ->addHeader('Referer', 'https://www.instagram.com/')
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-Instagram-AJAX', 'a878ae26c721')
            ->addHeader('X-IG-App-ID', '936619743392459')
            ->addHeader('User-Agent', $this->ig->getWebUserAgent())
            ->addPost('', '')
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Follow a user using Web Session.
     *
     * @param string $userId
     * @param string $csrftoken
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebFollowResponse
     */
    public function follow(
        $userId,
        $csrftoken)
    {
        return $this->ig->request("https://instagram.com/web/friendships/{$userId}/follow/")
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->addHeader('X-CSRFToken', $csrftoken)
            ->addHeader('Referer', 'https://www.instagram.com/')
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-Instagram-AJAX', 'a878ae26c721')
            ->addHeader('X-IG-App-ID', '936619743392459')
            ->addHeader('User-Agent', $this->ig->getWebUserAgent())
            ->addPost('', '')
            ->getResponse(new Response\WebFollowResponse());
    }

    /**
     * Unfollow a user using Web Session.
     *
     * @param string $userId
     * @param string $csrftoken
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebFollowResponse
     */
    public function unfollow(
        $userId,
        $csrftoken)
    {
        return $this->ig->request("https://instagram.com/web/friendships/{$userId}/unfollow/")
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->addHeader('X-CSRFToken', $csrftoken)
            ->addHeader('Referer', 'https://www.instagram.com/')
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-Instagram-AJAX', 'a878ae26c721')
            ->addHeader('X-IG-App-ID', '936619743392459')
            ->addHeader('User-Agent', $this->ig->getWebUserAgent())
            ->addPost('', '')
            ->getResponse(new Response\WebFollowResponse());
    }

    /**
     * Report media using web session.
     *
     * @param string $mediaId
     * @param string $reason  The reason of the report. '1' is Spam, '4' is pornography.
     * @param string $csrftoken
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function reportMedia(
        $mediaId,
        $reason,
        $csrftoken)
    {
        return $this->ig->request("https://instagram.com/media/{$mediaId}/flag/")
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->addHeader('X-CSRFToken', $csrftoken)
            ->addHeader('Referer', 'https://www.instagram.com/')
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-Instagram-AJAX', 'a878ae26c721')
            ->addHeader('X-IG-App-ID', '936619743392459')
            ->addHeader('User-Agent', $this->ig->getWebUserAgent())
            ->addPost('reason', $reason)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get username profile info.
     *
     * @param string $username
     * @param string $csrftoken
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebUserInfoResponse
     */
    public function getUserInfo(
        $username,
        $csrftoken)
    {
        return $this->ig->request("https://www.instagram.com/{$username}/")
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->setIsSilentFail(true)
            ->addHeader('X-CSRFToken', $csrftoken)
            ->addHeader('Referer', 'https://www.instagram.com/')
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-Instagram-AJAX', 'a878ae26c721')
            ->addHeader('X-IG-App-ID', '936619743392459')
            ->addHeader('User-Agent', $this->ig->getWebUserAgent())
            ->addParam('__a', '1')
            ->getResponse(new Response\WebUserInfoResponse());
    }

    /**
     * Gets information about password changes.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AccountAccessToolResponse
     */
    public function getPasswordChanges()
    {
        $response = $this->ig->request('https://instagram.com/accounts/access_tool/password_changes')
            ->setAddDefaultHeaders(false)
            ->addHeader('User-Agent', $this->ig->getWebUserAgent())
            ->getRawResponse();

        return new Response\AccountAccessToolResponse(json_decode($response, true));
    }

    /**
     * Make GraphQL request.
     *
     * @param string $queryHash Query hash.
     * @param array  $variables Variables.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebUserInfoResponse
     */
    public function sendGraphqlQuery(
        $queryHash,
        array $variables)
    {
        return $this->ig->request('https://www.instagram.com/graphql/query/')
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->setIsSilentFail(true)
            ->addHeader('X-CSRFToken', $this->ig->client->getToken())
            ->addHeader('Referer', 'https://www.instagram.com/')
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-Instagram-AJAX', 'a878ae26c721')
            ->addHeader('X-IG-App-ID', '936619743392459')
            ->addHeader('User-Agent', $this->ig->getWebUserAgent())
            ->addParam('query_hash', $queryHash)
            ->addParam('variables', json_encode($variables))
            ->getRawResponse();
    }

    /**
     * Update date of birth.
     *
     * @param string $day   Day.
     * @param string $month Month.
     * @param string $year  Year.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function updateDateOfBirth(
        $day,
        $month,
        $year)
    {
        return $this->ig->request('https://instagram.com/web/consent/update_dob/')
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->addHeader('X-CSRFToken', $this->ig->client->getToken())
            ->addHeader('Referer', 'https://www.instagram.com/')
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-Instagram-AJAX', 'a878ae26c721')
            ->addHeader('X-IG-App-ID', '936619743392459')
            ->addHeader('User-Agent', $this->ig->getWebUserAgent())
            ->addPost('day', $day)
            ->addPost('month', $month)
            ->addPost('year', $year)
            ->addPost('current_screen_key', 'dob')
            ->getResponse(new Response\GenericResponse());
    }
}
