<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Clip.
 *
 * @method string getDesign()
 * @method string getId()
 * @method Item[] getItems()
 * @method string getMaxId()
 * @method bool getMoreAvailable()
 * @method string getType()
 * @method bool isDesign()
 * @method bool isId()
 * @method bool isItems()
 * @method bool isMaxId()
 * @method bool isMoreAvailable()
 * @method bool isType()
 * @method $this setDesign(string $value)
 * @method $this setId(string $value)
 * @method $this setItems(Item[] $value)
 * @method $this setMaxId(string $value)
 * @method $this setMoreAvailable(bool $value)
 * @method $this setType(string $value)
 * @method $this unsetDesign()
 * @method $this unsetId()
 * @method $this unsetItems()
 * @method $this unsetMaxId()
 * @method $this unsetMoreAvailable()
 * @method $this unsetType()
 */
class Clip extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'                        => 'string',
        'items'                     => 'Item[]',
        'max_id'                    => 'string',
        'more_available'            => 'bool',
        'type'                      => 'string',
        'design'                    => 'string',
    ];
}
