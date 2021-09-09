<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class StickerTray extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'section_name'      => 'string',
        'section_title'     => '',
        'show_separator'    => 'bool',
        'allow_overflow'    => 'bool',
        'sticker_bundles'   => 'StaticStickers[]'
    ];
}
