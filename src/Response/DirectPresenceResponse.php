<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * DirectPresenceResponse.
 *
 * @method mixed getMessage()
 * @method string getStatus()
 * @method string[] getUserIds()
 * @method mixed getUserPresence()
 * @method Model\_Message[] get_Messages()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isUserIds()
 * @method bool isUserPresence()
 * @method bool is_Messages()
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setUserIds(string[] $value)
 * @method $this setUserPresence(mixed $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetUserIds()
 * @method $this unsetUserPresence()
 * @method $this unset_Messages()
 */
class DirectPresenceResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'user_ids'      => 'string[]',
        'user_presence' => '',
    ];
}
