<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * UsernameSuggestions.
 *
 * @method string getPrototype()
 * @method string getUsername()
 * @method bool isPrototype()
 * @method bool isUsername()
 * @method $this setPrototype(string $value)
 * @method $this setUsername(string $value)
 * @method $this unsetPrototype()
 * @method $this unsetUsername()
 */
class UsernameSuggestions extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'username'          => 'string',
        'prototype'         => 'string',
    ];
}
