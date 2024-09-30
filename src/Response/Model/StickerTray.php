<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * StickerTray.
 *
 * @method bool getAllowOverflow()
 * @method string getSectionName()
 * @method mixed getSectionTitle()
 * @method bool getShowSeparator()
 * @method StaticStickers[] getStickerBundles()
 * @method bool isAllowOverflow()
 * @method bool isSectionName()
 * @method bool isSectionTitle()
 * @method bool isShowSeparator()
 * @method bool isStickerBundles()
 * @method $this setAllowOverflow(bool $value)
 * @method $this setSectionName(string $value)
 * @method $this setSectionTitle(mixed $value)
 * @method $this setShowSeparator(bool $value)
 * @method $this setStickerBundles(StaticStickers[] $value)
 * @method $this unsetAllowOverflow()
 * @method $this unsetSectionName()
 * @method $this unsetSectionTitle()
 * @method $this unsetShowSeparator()
 * @method $this unsetStickerBundles()
 */
class StickerTray extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'section_name'      => 'string',
        'section_title'     => '',
        'show_separator'    => 'bool',
        'allow_overflow'    => 'bool',
        'sticker_bundles'   => 'StaticStickers[]',
    ];
}
