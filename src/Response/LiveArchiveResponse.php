<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * LiveArchiveResponse.
 *
 * @method int getCount()
 * @method Model\Item[] getItems()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isCount()
 * @method bool isItems()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setCount(int $value)
 * @method $this setItems(Model\Item[] $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetCount()
 * @method $this unsetItems()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class LiveArchiveResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'items'                  => 'Model\Item[]',
        'count'                  => 'int',
    ];
}
