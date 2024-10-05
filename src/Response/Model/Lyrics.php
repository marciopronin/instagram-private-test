<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Lyrics.
 *
 * @method Phrase[] getPhrases()
 * @method bool isPhrases()
 * @method $this setPhrases(Phrase[] $value)
 * @method $this unsetPhrases()
 */
class Lyrics extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'phrases'          => 'Phrase[]',
    ];
}
