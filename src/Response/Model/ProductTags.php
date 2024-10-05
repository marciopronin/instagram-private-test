<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * ProductTags.
 *
 * @method In[] getIn()
 * @method bool isIn()
 * @method $this setIn(In[] $value)
 * @method $this unsetIn()
 */
class ProductTags extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'in'        => 'In[]',
    ];
}
