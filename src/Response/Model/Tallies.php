<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Tallies.
 *
 * @method int getCount()
 * @method int getFontSize()
 * @method string getText()
 * @method bool isCount()
 * @method bool isFontSize()
 * @method bool isText()
 * @method $this setCount(int $value)
 * @method $this setFontSize(int $value)
 * @method $this setText(string $value)
 * @method $this unsetCount()
 * @method $this unsetFontSize()
 * @method $this unsetText()
 */
class Tallies extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'text'                 => 'string',
        'font_size'            => 'int',
        'count'                => 'int',
    ];
}
