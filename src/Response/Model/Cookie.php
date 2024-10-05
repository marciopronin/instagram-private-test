<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Cookie.
 *
 * @method string getDomain()
 * @method bool getHttponly()
 * @method int getMax()
 * @method string getName()
 * @method string getPath()
 * @method bool getSecure()
 * @method string getValue()
 * @method bool isDomain()
 * @method bool isHttponly()
 * @method bool isMax()
 * @method bool isName()
 * @method bool isPath()
 * @method bool isSecure()
 * @method bool isValue()
 * @method $this setDomain(string $value)
 * @method $this setHttponly(bool $value)
 * @method $this setMax(int $value)
 * @method $this setName(string $value)
 * @method $this setPath(string $value)
 * @method $this setSecure(bool $value)
 * @method $this setValue(string $value)
 * @method $this unsetDomain()
 * @method $this unsetHttponly()
 * @method $this unsetMax()
 * @method $this unsetName()
 * @method $this unsetPath()
 * @method $this unsetSecure()
 * @method $this unsetValue()
 */
class Cookie extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'name'                        => 'string',
        'value'                       => 'string',
        'path'                        => 'string',
        'max'                         => 'int',
        'secure'                      => 'bool',
        'httponly'                    => 'bool',
        'domain'                      => 'string',
    ];
}
