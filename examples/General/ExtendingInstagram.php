<?php

require __DIR__.'/../../vendor/autoload.php';

class ExtendedInstagram extends InstagramAPI\Instagram
{
    /**
     * Set the active account for the class instance.
     *
     * We can call this multiple times to switch between multiple accounts.
     *
     * @param string $username Your Instagram username.
     * @param string $password Your Instagram password.
     *
     * @throws InstagramAPI\Exception\InstagramException
     */
    public function setUser(
        $username,
        $password,
    ) {
        $this->_setUser('regular', $username, $password);
    }
}
