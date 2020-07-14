<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * AuthorizationHeader.
 *
 * @method string getValue()
 * @method bool isValue()
 * @method $this setValue(string $value)
 * @method $this unsetValue()
 */
class AuthorizationHeader extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'value'                        => 'string',
    ];
}
