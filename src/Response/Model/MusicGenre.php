<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * MusicGenre.
 *
 * @method string getCoverArtworkUri()
 * @method string getId()
 * @method string getName()
 * @method bool isCoverArtworkUri()
 * @method bool isId()
 * @method bool isName()
 * @method $this setCoverArtworkUri(string $value)
 * @method $this setId(string $value)
 * @method $this setName(string $value)
 * @method $this unsetCoverArtworkUri()
 * @method $this unsetId()
 * @method $this unsetName()
 */
class MusicGenre extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'                        => 'string',
        'name'                      => 'string',
        'cover_artwork_uri'         => 'string',
    ];
}
