<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class StickerTrayResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'layout_name'               => 'string',
        'composer_config'           => '',
        'sticker_tray'              => 'Model\StickerTray[]',
    ];
}
