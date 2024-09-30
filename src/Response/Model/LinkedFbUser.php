<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * LinkedFbUser.
 *
 * @method string getFbAccountCreationTime()
 * @method string getId()
 * @method bool getIsValid()
 * @method string getLinkTime()
 * @method string getName()
 * @method bool isFbAccountCreationTime()
 * @method bool isId()
 * @method bool isIsValid()
 * @method bool isLinkTime()
 * @method bool isName()
 * @method $this setFbAccountCreationTime(string $value)
 * @method $this setId(string $value)
 * @method $this setIsValid(bool $value)
 * @method $this setLinkTime(string $value)
 * @method $this setName(string $value)
 * @method $this unsetFbAccountCreationTime()
 * @method $this unsetId()
 * @method $this unsetIsValid()
 * @method $this unsetLinkTime()
 * @method $this unsetName()
 */
class LinkedFbUser extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'id'                        => 'string',
        'name'                      => 'string',
        'is_valid'                  => 'bool',
        'fb_account_creation_time'  => 'string',
        'link_time'                 => 'string',
    ];
}
