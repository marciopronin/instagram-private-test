<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * MusicGenreItem.
 *
 * @method MusicGenre getGenre()
 * @method bool isGenre()
 * @method $this setGenre(MusicGenre $value)
 * @method $this unsetGenre()
 */
class MusicGenreItem extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'genre'                        => 'MusicGenre',
    ];
}
