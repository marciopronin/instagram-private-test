<?php

require __DIR__.'/Anticaptcha.php';

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
     *
     * @param mixed $taskInfo
     */
    public function setTaskInfo(
        $taskInfo,
    ): void {
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
     *
     * @param mixed $value
     */
    public function setWebsiteURL(
        $value,
    ): void {
        $this->_websiteUrl = $value;
    }

    /**
     * Set website key.
     *
     * @var string
     *
     * @param mixed $value
     */
    public function setWebsiteKey(
        $value,
    ): void {
        $this->_websiteKey = $value;
    }

    /**
     * Set website SToken.
     *
     * @var string
     *
     * @param mixed $value
     */
    public function setWebsiteSToken(
        $value,
    ): void {
        $this->_websiteSToken = $value;
    }

    /**
     * Set proxy type.
     *
     * @var string
     *
     * @param mixed $value
     */
    public function setProxyType(
        $value,
    ): void {
        $this->_proxyType = $value;
    }

    /**
     * Set proxy address.
     *
     * @var string
     *
     * @param mixed $value
     */
    public function setProxyAddress(
        $value,
    ): void {
        $this->_proxyAddress = $value;
    }

    /**
     * Set proxy port.
     *
     * @var string
     *
     * @param mixed $value
     */
    public function setProxyPort(
        $value,
    ): void {
        $this->_proxyPort = $value;
    }

    /**
     * Set proxy login.
     *
     * @var string
     *
     * @param mixed $value
     */
    public function setProxyLogin(
        $value,
    ): void {
        $this->_proxyLogin = $value;
    }

    /**
     * Set proxy password.
     *
     * @var string
     *
     * @param mixed $value
     */
    public function setProxyPassword(
        $value,
    ): void {
        $this->_proxyPassword = $value;
    }

    /**
     * Set User Agent.
     *
     * @var string
     *
     * @param mixed $value
     */
    public function setUserAgent(
        $value,
    ): void {
        $this->_userAgent = $value;
    }

    /**
     * Set cookies.
     *
     * @var string
     *
     * @param mixed $value
     */
    public function setCookies(
        $value,
    ): void {
        $this->_cookies = $value;
    }
}
