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
     * Web login.
     *
     * @param string $username
     * @param string $password
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return string
     */
    public function login(
        $username,
        $password)
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
            ->addHeader('X-CSRFToken', $this->ig->client->getToken())
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
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return string
     */
    public function sendSignupSms(
        $phone,
        $mid)
    {
        return $this->ig->request('https://www.instagram.com/send_signup_sms_code_ajax/')
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->setAddDefaultHeaders(false)
            ->addHeader('X-CSRFToken', $this->ig->client->getToken())
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addPost('client_id', $mid)
            ->addPost('phone_number', $phone)
            ->addPost('phone_id', '')
            ->addPost('big_blue_token', '')
            ->getRawResponse();
    }

    /**
     * Web registration.
     *
     * @param string $username The account username.
     * @param string $password The account password.
     * @param string $name     The name of the account.
     * @param string $phone    The phone number.
     * @param string $day      Day of birth.
     * @param string $month    Month of birth.
     * @param string $year     Year of bith.
     * @param string $mid      Mid value (obtained from cookie).
     * @param bool   $attempt  Wether it is an attempt or not.
     * @param string $tos      Terms of Service.
     * @param string $smsCode  The SMS code.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return string
     */
    public function createAccount(
        $username,
        $password,
        $name,
        $phone,
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
            ->addPost('phone_number', $phone)
            ->addPost('username', $username)
            ->addPost('first_name', $name)
            ->addPost('month', $month)
            ->addPost('day', $day)
            ->addPost('year', $year)
            ->addPost('client_id', $mid)
            ->addPost('seamless_login_enabled', 1)
            ->addPost('tos_version', $tos);

        if ($attempt === false) {
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
            ->addHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:69.0) Gecko/20100101 Firefox/69.0')
            ->getRawResponse();

        preg_match_all('/window._sharedData = (.*);<\/script>/m', $response, $matches, PREG_SET_ORDER, 0);

        return new Response\AccountAccessToolResponse(json_decode($matches[0][1], true));
    }

    /**
     * Like a media using Web Session.
     *
     * @param string $mediaId
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function like(
        $mediaId)
    {
        return $this->ig->request("https://instagram.com/web/likes/{$mediaId}/like/")
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->addHeader('X-CSRFToken', $this->ig->client->getToken())
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
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebFollowResponse
     */
    public function follow(
        $userId)
    {
        return $this->ig->request("https://instagram.com/web/friendships/{$userId}/follow/")
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->addHeader('X-CSRFToken', $this->ig->client->getToken())
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
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebFollowResponse
     */
    public function unfollow(
        $userId)
    {
        return $this->ig->request("https://instagram.com/web/friendships/{$userId}/unfollow/")
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->addHeader('X-CSRFToken', $this->ig->client->getToken())
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
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function reportMedia(
        $mediaId,
        $reason)
    {
        return $this->ig->request("https://instagram.com/media/{$mediaId}/flag/")
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->addHeader('X-CSRFToken', $this->ig->client->getToken())
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
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\WebUserInfoResponse
     */
    public function getUserInfo(
        $username)
    {
        return $this->ig->request("https://www.instagram.com/{$username}/")
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->setIsSilentFail(true)
            ->addHeader('X-CSRFToken', $this->ig->client->getToken())
            ->addHeader('Referer', 'https://www.instagram.com/')
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-Instagram-AJAX', 'a878ae26c721')
            ->addHeader('X-IG-App-ID', '936619743392459')
            ->addHeader('User-Agent', $this->ig->getWebUserAgent())
            ->addParam('__a', '1')
            ->getResponse(new Response\WebUserInfoResponse());
    }
}
