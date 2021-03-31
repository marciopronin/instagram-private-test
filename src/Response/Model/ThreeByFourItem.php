<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * ThreeByFourItem.
 *
 * @method Clip getClips()
 * @method bool isClips()
 * @method $this setClips(Clip $value)
 * @method $this unsetClips()
 */
class ThreeByFourItem extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'clips'  => 'Clip',
    ];
}
