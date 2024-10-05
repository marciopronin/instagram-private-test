<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * UpcomingEventsResponse.
 *
 * @method string getId()
 * @method Model\LiveMetadata getLiveMetadata()
 * @method mixed getMessage()
 * @method bool getReminderEnabled()
 * @method string getStartTime()
 * @method string getStatus()
 * @method string getTitle()
 * @method string getUpcomingEventIdType()
 * @method Model\_Message[] get_Messages()
 * @method bool isId()
 * @method bool isLiveMetadata()
 * @method bool isMessage()
 * @method bool isReminderEnabled()
 * @method bool isStartTime()
 * @method bool isStatus()
 * @method bool isTitle()
 * @method bool isUpcomingEventIdType()
 * @method bool is_Messages()
 * @method $this setId(string $value)
 * @method $this setLiveMetadata(Model\LiveMetadata $value)
 * @method $this setMessage(mixed $value)
 * @method $this setReminderEnabled(bool $value)
 * @method $this setStartTime(string $value)
 * @method $this setStatus(string $value)
 * @method $this setTitle(string $value)
 * @method $this setUpcomingEventIdType(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetId()
 * @method $this unsetLiveMetadata()
 * @method $this unsetMessage()
 * @method $this unsetReminderEnabled()
 * @method $this unsetStartTime()
 * @method $this unsetStatus()
 * @method $this unsetTitle()
 * @method $this unsetUpcomingEventIdType()
 * @method $this unset_Messages()
 */
class UpcomingEventsResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'id'                        => 'string',
        'title'                     => 'string',
        'start_time'                => 'string',
        'reminder_enabled'          => 'bool',
        'live_metadata'             => 'Model\LiveMetadata',
        'upcoming_event_id_type'    => 'string',
    ];
}
