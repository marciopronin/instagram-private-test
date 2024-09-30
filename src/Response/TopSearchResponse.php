<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * TopSearchResponse.
 *
 * @method bool getClearClientCache()
 * @method bool getHasMore()
 * @method Model\MediaGrid getMediaGrid()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isClearClientCache()
 * @method bool isHasMore()
 * @method bool isMediaGrid()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setClearClientCache(bool $value)
 * @method $this setHasMore(bool $value)
 * @method $this setMediaGrid(Model\MediaGrid $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetClearClientCache()
 * @method $this unsetHasMore()
 * @method $this unsetMediaGrid()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class TopSearchResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'has_more'             => 'bool',
        'clear_client_cache'   => 'bool',
        'media_grid'           => 'Model\MediaGrid',
    ];
}
