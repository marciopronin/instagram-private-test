<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Metadata.
 *
 * @method bool getIsBookmarked()
 * @method bool isIsBookmarked()
 * @method $this setIsBookmarked(bool $value)
 * @method $this unsetIsBookmarked()
 */
class Metadata extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'is_bookmarked'          => 'bool',
    ];
}
