<?php

namespace InstagramAPI\Response\Model\Bloks;

use InstagramAPI\AutoPropertyMapper;

/**
 * Decoration.
 *
 * @method BoxDecoration getBkComponentsBoxDecoration()
 * @method bool isBkComponentsBoxDecoration()
 * @method $this setBkComponentsBoxDecoration(BoxDecoration $value)
 * @method $this unsetBkComponentsBoxDecoration()
 */
class Decoration extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'bk.components.BoxDecoration' => 'BoxDecoration',
    ];
}
