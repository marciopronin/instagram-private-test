<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * CreateSerieResponse.
 *
 * @method string getDescription()
 * @method string getId()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method string getString()
 * @method Model\_Message[] get_Messages()
 * @method bool isDescription()
 * @method bool isId()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isString()
 * @method bool is_Messages()
 * @method $this setDescription(string $value)
 * @method $this setId(string $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setString(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetDescription()
 * @method $this unsetId()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetString()
 * @method $this unset_Messages()
 */
class CreateSerieResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'id'            => 'string',
        'string'        => 'string',
        'description'   => 'string',
    ];
}
