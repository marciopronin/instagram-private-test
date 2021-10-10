<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * StickerTrayResponse.
 *
 * @method mixed getComposerConfig()
 * @method string getLayoutName()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\StickerTray[] getStickerTray()
 * @method Model\_Message[] get_Messages()
 * @method bool isComposerConfig()
 * @method bool isLayoutName()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isStickerTray()
 * @method bool is_Messages()
 * @method $this setComposerConfig(mixed $value)
 * @method $this setLayoutName(string $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setStickerTray(Model\StickerTray[] $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetComposerConfig()
 * @method $this unsetLayoutName()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetStickerTray()
 * @method $this unset_Messages()
 */
class StickerTrayResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'layout_name'               => 'string',
        'composer_config'           => '',
        'sticker_tray'              => 'Model\StickerTray[]',
    ];
}
