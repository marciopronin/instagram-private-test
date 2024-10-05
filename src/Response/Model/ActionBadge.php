<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * ActionBadge.
 *
 * @method mixed getActionCount()
 * @method mixed getActionTimestamp()
 * @method mixed getActionType()
 * @method bool isActionCount()
 * @method bool isActionTimestamp()
 * @method bool isActionType()
 * @method $this setActionCount(mixed $value)
 * @method $this setActionTimestamp(mixed $value)
 * @method $this setActionType(mixed $value)
 * @method $this unsetActionCount()
 * @method $this unsetActionTimestamp()
 * @method $this unsetActionType()
 */
class ActionBadge extends AutoPropertyMapper
{
    public const DELIVERED = 'raven_delivered';
    public const SENT = 'raven_sent';
    public const OPENED = 'raven_opened';
    public const SCREENSHOT = 'raven_screenshot';
    public const REPLAYED = 'raven_replayed';
    public const CANNOT_DELIVER = 'raven_cannot_deliver';
    public const SENDING = 'raven_sending';
    public const BLOCKED = 'raven_blocked';
    public const UNKNOWN = 'raven_unknown';
    public const SUGGESTED = 'raven_suggested';

    public const JSON_PROPERTY_MAP = [
        'action_type'      => '',
        'action_count'     => '',
        'action_timestamp' => '',
    ];
}
