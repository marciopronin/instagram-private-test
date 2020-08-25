<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * UserReelsResponse.
 *
 * @method Model\Item[] getItems()
 * @method mixed getMessage()
 * @method Model\PageInfo getPagingInfo()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isItems()
 * @method bool isMessage()
 * @method bool isPagingInfo()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setItems(Model\Item[] $value)
 * @method $this setMessage(mixed $value)
 * @method $this setPagingInfo(Model\PageInfo $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetItems()
 * @method $this unsetMessage()
 * @method $this unsetPagingInfo()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class UserReelsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'items'        => 'Model\Item[]',
        'paging_info'  => 'Model\PageInfo',
    ];
}
