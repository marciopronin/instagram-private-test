<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * MusicItemsResponse.
 *
 * @method string getAlacornSessionId()
 * @method mixed getDarkBannerMessage()
 * @method Model\Item[] getItems()
 * @method mixed getMessage()
 * @method mixed getMusicReels()
 * @method Model\PageInfo getPageInfo()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isAlacornSessionId()
 * @method bool isDarkBannerMessage()
 * @method bool isItems()
 * @method bool isMessage()
 * @method bool isMusicReels()
 * @method bool isPageInfo()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setAlacornSessionId(string $value)
 * @method $this setDarkBannerMessage(mixed $value)
 * @method $this setItems(Model\Item[] $value)
 * @method $this setMessage(mixed $value)
 * @method $this setMusicReels(mixed $value)
 * @method $this setPageInfo(Model\PageInfo $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetAlacornSessionId()
 * @method $this unsetDarkBannerMessage()
 * @method $this unsetItems()
 * @method $this unsetMessage()
 * @method $this unsetMusicReels()
 * @method $this unsetPageInfo()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class MusicItemsResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'items'                     => 'Model\Item[]',
        'page_info'                 => 'Model\PageInfo',
        'alacorn_session_id'        => 'string',
        'music_reels'               => '',
        'dark_banner_message'       => '',
    ];
}
