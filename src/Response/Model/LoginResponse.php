<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * LoginResponse.
 *
 * @method User getLoggedInUser()
 * @method bool isLoggedInUser()
 * @method $this setLoggedInUser(User $value)
 * @method $this unsetLoggedInUser()
 */
class LoginResponse extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'logged_in_user'                        => 'User',
    ];
}
