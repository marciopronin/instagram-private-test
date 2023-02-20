<?php

require __DIR__.'/Anticaptcha.php';

/**
 * NoCaptchaProxyless.
 */
class NoCaptchaProxyless extends Anticaptcha implements AntiCaptchaTaskProtocol
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
     * Get POST data.
     *
     * @return array
     */
    public function getPostData()
    {
        return [
            'type'          => 'NoCaptchaTaskProxyless',
            'websiteURL'    => $this->_websiteUrl,
            'websiteKey'    => $this->_websiteKey,
            'websiteSToken' => $this->_websiteSToken,
        ];
    }

    /**
     * Gets captcha solution.
     *
     * @return string
     */
    public function getTaskSolution()
    {
        return $this->taskInfo->solution->gRecaptchaResponse;
    }

    /**
     * Sets the website URL.
     *
     * @param string $value Website URL to set.
     */
    public function setWebsiteURL(
        $value
    ): void {
        $this->_websiteUrl = $value;
    }

    /**
     * Sets the website key.
     *
     * @param string $value Website key to set.
     */
    public function setWebsiteKey(
        $value
    ): void {
        $this->_websiteKey = $value;
    }

    /**
     * Sets the website SToken.
     *
     * @param string $value Website SToken to set.
     */
    public function setWebsiteSToken(
        $value
    ): void {
        $this->_websiteSToken = $value;
    }
}
