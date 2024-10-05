<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * OneByTwoItem.
 *
 * @method Clip getClips()
 * @method Story getStories()
 * @method bool isClips()
 * @method bool isStories()
 * @method $this setClips(Clip $value)
 * @method $this setStories(Story $value)
 * @method $this unsetClips()
 * @method $this unsetStories()
 */
class OneByTwoItem extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'stories' => 'Story',
        'clips'   => 'Clip',
    ];
}
