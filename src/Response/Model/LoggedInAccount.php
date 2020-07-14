<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * LoggedInAccount.
 *
 * @method LoginResponse getLoginResponse()
 * @method SessionInfo getSessionInfo()
 * @method bool isLoginResponse()
 * @method bool isSessionInfo()
 * @method $this setLoginResponse(LoginResponse $value)
 * @method $this setSessionInfo(SessionInfo $value)
 * @method $this unsetLoginResponse()
 * @method $this unsetSessionInfo()
 */
class LoggedInAccount extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'session_info'                        => 'SessionInfo',
        'login_response'                      => 'LoginResponse',
    ];
}
