<?php

/**
 * NoCaptcha.
 */
class NoCaptcha extends Anticaptcha implements AntiCaptchaTaskProtocol
{
    /**
     * Website url.
     *
     * @var string
     */
    private $_websiteUrl;

    /**
     * Website key.
     *
     * @var string
     */
    private $_websiteKey;

    /**
     * Website SToken.
     *
     * @var string
     */
    private $_websiteSToken;

    /**
     * Proxy type.
     *
     * @var string
     */
    private $_proxyType = 'http';

    /**
     * Proxy address.
     *
     * @var string
     */
    private $_proxyAddress;

    /**
     * Proxy port.
     *
     * @var string
     */
    private $_proxyPort;

    /**
     * Proxy login.
     *
     * @var string
     */
    private $_proxyLogin;

    /**
     * Proxy password.
     *
     * @var string
     */
    private $_proxyPassword;

    /**
     * Proxy User Agent.
     *
     * @var string
     */
    private $_userAgent = '';

    /**
     * Proxy cookies.
     *
     * @var string
     */
    private $_cookies = '';

    /**
     * Get POST data.
     *
     * @return array
     */
    public function getPostData()
    {
        return [
            'type'          => 'NoCaptchaTask',
            'websiteURL'    => $this->_websiteUrl,
            'websiteKey'    => $this->_websiteKey,
            'websiteSToken' => $this->_websiteSToken,
            'proxyType'     => $this->_proxyType,
            'proxyAddress'  => $this->_proxyAddress,
            'proxyPort'     => $this->_proxyPort,
            'proxyLogin'    => $this->_proxyLogin,
            'proxyPassword' => $this->_proxyPassword,
            'userAgent'     => $this->_userAgent,
            'cookies'       => $this->_cookies,
        ];
    }

    /**
     * Set task info.
     *
     * @var string
     */
    public function setTaskInfo(
        $taskInfo)
    {
        $this->taskInfo = $taskInfo;
    }

    /**
     * Get captcha solution.
     *
     * @return string
     */
    public function getTaskSolution()
    {
        return $this->taskInfo->solution->gRecaptchaResponse;
    }

    /**
     * Set website URL.
     *
     * @var string
     */
    public function setWebsiteURL(
        $value)
    {
        $this->_websiteUrl = $value;
    }

    /**
     * Set website key.
     *
     * @var string
     */
    public function setWebsiteKey(
        $value)
    {
        $this->_websiteKey = $value;
    }

    /**
     * Set website SToken.
     *
     * @var string
     */
    public function setWebsiteSToken(
        $value)
    {
        $this->_websiteSToken = $value;
    }

    /**
     * Set proxy type.
     *
     * @var string
     */
    public function setProxyType(
        $value)
    {
        $this->_proxyType = $value;
    }

    /**
     * Set proxy address.
     *
     * @var string
     */
    public function setProxyAddress(
        $value)
    {
        $this->_proxyAddress = $value;
    }

    /**
     * Set proxy port.
     *
     * @var string
     */
    public function setProxyPort(
        $value)
    {
        $this->_proxyPort = $value;
    }

    /**
     * Set proxy login.
     *
     * @var string
     */
    public function setProxyLogin(
        $value)
    {
        $this->_proxyLogin = $value;
    }

    /**
     * Set proxy password.
     *
     * @var string
     */
    public function setProxyPassword(
        $value)
    {
        $this->_proxyPassword = $value;
    }

    /**
     * Set User Agent.
     *
     * @var string
     */
    public function setUserAgent(
        $value)
    {
        $this->_userAgent = $value;
    }

    /**
     * Set cookies.
     *
     * @var string
     */
    public function setCookies(
        $value)
    {
        $this->_cookies = $value;
    }
}
