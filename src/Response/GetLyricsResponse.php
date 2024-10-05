<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * GetLyricsResponse.
 *
 * @method Model\Lyrics getLyrics()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isLyrics()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setLyrics(Model\Lyrics $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetLyrics()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class GetLyricsResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'lyrics'                     => 'Model\Lyrics',
    ];
}
