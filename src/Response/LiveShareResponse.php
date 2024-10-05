<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * LiveShareResponse.
 *
 * @method string getLiveToShareUrl()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isLiveToShareUrl()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setLiveToShareUrl(string $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetLiveToShareUrl()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class LiveShareResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'live_to_share_url'  => 'string',
    ];
}
