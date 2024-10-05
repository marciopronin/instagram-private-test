<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * UnseenFacebookNotificationsResponse.
 *
 * @method mixed getFbuid()
 * @method int getLastSeenTimestamp()
 * @method mixed getMessage()
 * @method int getNotifCount()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isFbuid()
 * @method bool isLastSeenTimestamp()
 * @method bool isMessage()
 * @method bool isNotifCount()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setFbuid(mixed $value)
 * @method $this setLastSeenTimestamp(int $value)
 * @method $this setMessage(mixed $value)
 * @method $this setNotifCount(int $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetFbuid()
 * @method $this unsetLastSeenTimestamp()
 * @method $this unsetMessage()
 * @method $this unsetNotifCount()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class UnseenFacebookNotificationsResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'notif_count'           => 'int',
        'last_seen_timestamp'   => 'int',
        'fbuid'                 => '',
    ];
}
