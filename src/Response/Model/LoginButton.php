<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * LoginButton.
 *
 * @method string getAction()
 * @method string getStopDeletionToken()
 * @method string getTitle()
 * @method bool isAction()
 * @method bool isStopDeletionToken()
 * @method bool isTitle()
 * @method $this setAction(string $value)
 * @method $this setStopDeletionToken(string $value)
 * @method $this setTitle(string $value)
 * @method $this unsetAction()
 * @method $this unsetStopDeletionToken()
 * @method $this unsetTitle()
 */
class LoginButton extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'title'                                        => 'string',
        'stop_deletion_token'                          => 'string',
        'action'                                       => 'string',
    ];
}
