<?php

namespace InstagramAPI\Push\Fbns;

use Fbns\Auth as AuthInterface;
use Fbns\Auth\DeviceAuth;
use InstagramAPI\Instagram;

class Auth implements AuthInterface
{
    /**
     * @var Instagram
     */
    protected $_instagram;

    /**
     * @var DeviceAuth
     */
    protected $_deviceAuth;

    /**
     * Auth constructor.
     *
     * @param Instagram $instagram
     */
    public function __construct(
        Instagram $instagram
    ) {
        $this->_instagram = $instagram;
        $this->_deviceAuth = $this->_instagram->settings->getFbnsAuth();
    }

    /** {@inheritdoc} */
    public function getClientId(): string
    {
        return $this->_deviceAuth->getClientId();
    }

    /** {@inheritdoc} */
    public function getClientType(): string
    {
        return $this->_deviceAuth->getClientType();
    }

    /** {@inheritdoc} */
    public function getUserId(): string
    {
        return $this->_deviceAuth->getUserId();
    }

    /** {@inheritdoc} */
    public function getPassword(): string
    {
        return $this->_deviceAuth->getPassword();
    }

    /** {@inheritdoc} */
    public function getDeviceId(): string
    {
        return $this->_deviceAuth->getDeviceId();
    }

    /** {@inheritdoc} */
    public function getDeviceSecret(): string
    {
        return $this->_deviceAuth->getDeviceSecret();
    }

    public function resetAuth(): void
    {
        $this->_instagram->settings->set('fbns_auth', '');
        $this->_deviceAuth = $this->_instagram->settings->getFbnsAuth();
    }

    /** {@inheritdoc} */
    public function __toString()
    {
        return json_encode($this->_deviceAuth);
    }

    /**
     * Update auth data.
     *
     * @param string $auth
     *
     * @throws \InvalidArgumentException
     */
    public function update(
        $auth
    ) {
        /* @var DeviceAuth $auth */
        $this->_deviceAuth->read($auth);
        $this->_instagram->settings->setFbnsAuth($this->__toString());
    }
}
