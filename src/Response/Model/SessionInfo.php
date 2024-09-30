<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * SessionInfo.
 *
 * @method AuthorizationHeader getAuthorizationHeader()
 * @method Cookie[] getCookies()
 * @method bool isAuthorizationHeader()
 * @method bool isCookies()
 * @method $this setAuthorizationHeader(AuthorizationHeader $value)
 * @method $this setCookies(Cookie[] $value)
 * @method $this unsetAuthorizationHeader()
 * @method $this unsetCookies()
 */
class SessionInfo extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'cookies'                        => 'Cookie[]',
        'authorization_header'           => 'AuthorizationHeader',
    ];
}
