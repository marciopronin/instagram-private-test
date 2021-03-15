<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class GetMusicGenresResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'items'             => 'Model\MusicGenreItem[]',
    ];
}
