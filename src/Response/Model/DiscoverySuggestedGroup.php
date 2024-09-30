<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * DiscoverySuggestedGroup.
 *
 * @method UserCard[] getItems()
 * @method string getType()
 * @method bool isItems()
 * @method bool isType()
 * @method $this setItems(UserCard[] $value)
 * @method $this setType(string $value)
 * @method $this unsetItems()
 * @method $this unsetType()
 */
class DiscoverySuggestedGroup extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'type'         => 'string',
        'items'        => 'UserCard[]',
    ];
}
