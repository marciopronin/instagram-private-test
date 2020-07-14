<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * ChallengeNavigation.
 *
 * @method string getForward()
 * @method bool isForward()
 * @method $this setForward(string $value)
 * @method $this unsetForward()
 */
class ChallengeNavigation extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'forward'             => 'string',
    ];
}
