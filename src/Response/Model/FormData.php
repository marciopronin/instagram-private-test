<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * FormData.
 *
 * @method string getBiography()
 * @method string getEmail()
 * @method string getFirstName()
 * @method string getPhoneNumber()
 * @method string getUsername()
 * @method bool isBiography()
 * @method bool isEmail()
 * @method bool isFirstName()
 * @method bool isPhoneNumber()
 * @method bool isUsername()
 * @method $this setBiography(string $value)
 * @method $this setEmail(string $value)
 * @method $this setFirstName(string $value)
 * @method $this setPhoneNumber(string $value)
 * @method $this setUsername(string $value)
 * @method $this unsetBiography()
 * @method $this unsetEmail()
 * @method $this unsetFirstName()
 * @method $this unsetPhoneNumber()
 * @method $this unsetUsername()
 */
class FormData extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'first_name'                  => 'string',
        'username'                    => 'string',
        'phone_number'                => 'string',
        'email'                       => 'string',
        'biography'                   => 'string',
    ];
}
