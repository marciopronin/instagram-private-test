<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * StoryMentions.
 *
 * @method string getMentionsCountString()
 * @method Reel[] getReels()
 * @method bool isMentionsCountString()
 * @method bool isReels()
 * @method $this setMentionsCountString(string $value)
 * @method $this setReels(Reel[] $value)
 * @method $this unsetMentionsCountString()
 * @method $this unsetReels()
 */
class StoryMentions extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'mentions_count_string' => 'string',
        'reels'                 => 'Reel[]',
    ];
}
