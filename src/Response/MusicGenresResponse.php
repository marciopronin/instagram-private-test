<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * MusicGenresResponse.
 *
 * @method Model\MusicGenreItem[] getItems()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isItems()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setItems(Model\MusicGenreItem[] $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetItems()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class MusicGenresResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'items'             => 'Model\MusicGenreItem[]',
    ];
}
