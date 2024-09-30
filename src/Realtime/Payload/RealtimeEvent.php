<?php

namespace InstagramAPI\Realtime\Payload;

use InstagramAPI\AutoPropertyMapper;

/**
 * RealtimeEvent.
 *
 * @method string getEvent()
 * @method bool isEvent()
 * @method $this setEvent(string $value)
 * @method $this unsetEvent()
 */
abstract class RealtimeEvent extends AutoPropertyMapper
{
    public const SUBSCRIBED = 'subscribed';
    public const UNSUBSCRIBED = 'unsubscribed';
    public const KEEPALIVE = 'keepalive';
    public const PATCH = 'patch';
    public const BROADCAST_ACK = 'broadcast-ack';
    public const ERROR = 'error';

    public const JSON_PROPERTY_MAP = [
        'event' => 'string',
    ];
}
