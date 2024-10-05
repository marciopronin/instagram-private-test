<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * SectionMedia.
 *
 * @method Clip getClips()
 * @method Item getMedia()
 * @method bool isClips()
 * @method bool isMedia()
 * @method $this setClips(Clip $value)
 * @method $this setMedia(Item $value)
 * @method $this unsetClips()
 * @method $this unsetMedia()
 */
class SectionMedia extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'media'  => 'Item',
        'clips'  => 'Clip',
    ];
}
