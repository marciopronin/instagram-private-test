<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class MusicGenreItem extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'genre'                        => 'MusicGenre',
    ];
}
