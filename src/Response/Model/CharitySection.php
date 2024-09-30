<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * CharitySection.
 *
 * @method mixed getCharities()
 * @method string getTitle()
 * @method bool isCharities()
 * @method bool isTitle()
 * @method $this setCharities(mixed $value)
 * @method $this setTitle(string $value)
 * @method $this unsetCharities()
 * @method $this unsetTitle()
 */
class CharitySection extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'title'                        => 'string',
        'charities'                    => '',
    ];
}
