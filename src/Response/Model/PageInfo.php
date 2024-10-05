<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * PageInfo.
 *
 * @method string getEndCursor()
 * @method bool getHasNextPage()
 * @method bool getHasPreviousPage()
 * @method string getMaxId()
 * @method bool isEndCursor()
 * @method bool isHasNextPage()
 * @method bool isHasPreviousPage()
 * @method bool isMaxId()
 * @method $this setEndCursor(string $value)
 * @method $this setHasNextPage(bool $value)
 * @method $this setHasPreviousPage(bool $value)
 * @method $this setMaxId(string $value)
 * @method $this unsetEndCursor()
 * @method $this unsetHasNextPage()
 * @method $this unsetHasPreviousPage()
 * @method $this unsetMaxId()
 */
class PageInfo extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'end_cursor'        => 'string',
        'has_next_page'     => 'bool',
        'has_previous_page' => 'bool',
        'max_id'            => 'string',
    ];
}
