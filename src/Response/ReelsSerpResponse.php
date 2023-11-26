<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * ReelsSerpResponse.
 *
 * @method bool getHasMore()
 * @method mixed getMessage()
 * @method int getPageIndex()
 * @method string getRankToken()
 * @method string getReelsMaxId()
 * @method Model\ReelsSerpModule[] getReelsSerpModules()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isHasMore()
 * @method bool isMessage()
 * @method bool isPageIndex()
 * @method bool isRankToken()
 * @method bool isReelsMaxId()
 * @method bool isReelsSerpModules()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setHasMore(bool $value)
 * @method $this setMessage(mixed $value)
 * @method $this setPageIndex(int $value)
 * @method $this setRankToken(string $value)
 * @method $this setReelsMaxId(string $value)
 * @method $this setReelsSerpModules(Model\ReelsSerpModule[] $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetHasMore()
 * @method $this unsetMessage()
 * @method $this unsetPageIndex()
 * @method $this unsetRankToken()
 * @method $this unsetReelsMaxId()
 * @method $this unsetReelsSerpModules()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class ReelsSerpResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'reels_serp_modules'  => 'Model\ReelsSerpModule[]',
        'has_more'            => 'bool',
        'reels_max_id'        => 'string',
        'page_index'          => 'int',
        'rank_token'          => 'string',
    ];
}
