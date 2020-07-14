<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * UnfollowChainingCountResponse.
 *
 * @method int getCount()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isCount()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setCount(int $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetCount()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class UnfollowChainingCountResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'count'           => 'int',
    ];
}
