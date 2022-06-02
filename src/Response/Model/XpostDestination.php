<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * XpostDestination.
 *
 * @method string getDestinationId()
 * @method string getDestinationName()
 * @method string getDestinationType()
 * @method bool isDestinationId()
 * @method bool isDestinationName()
 * @method bool isDestinationType()
 * @method $this setDestinationId(string $value)
 * @method $this setDestinationName(string $value)
 * @method $this setDestinationType(string $value)
 * @method $this unsetDestinationId()
 * @method $this unsetDestinationName()
 * @method $this unsetDestinationType()
 */
class XpostDestination extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'destination_id'           => 'string',
        'destination_name'         => 'string',
        'destination_type'         => 'string',
    ];
}
