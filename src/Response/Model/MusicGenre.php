<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class MusicGenre extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'                        => 'string',
        'name'                      => 'string',
        'cover_artwork_uri'         => 'string'
    ];
}
