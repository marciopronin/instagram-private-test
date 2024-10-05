<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * LiveMetadata.
 *
 * @method bool getIsBroadcastEnded()
 * @method bool getIsScheduledLive()
 * @method bool getLiveNotifsEnabled()
 * @method int getVisibility()
 * @method bool isIsBroadcastEnded()
 * @method bool isIsScheduledLive()
 * @method bool isLiveNotifsEnabled()
 * @method bool isVisibility()
 * @method $this setIsBroadcastEnded(bool $value)
 * @method $this setIsScheduledLive(bool $value)
 * @method $this setLiveNotifsEnabled(bool $value)
 * @method $this setVisibility(int $value)
 * @method $this unsetIsBroadcastEnded()
 * @method $this unsetIsScheduledLive()
 * @method $this unsetLiveNotifsEnabled()
 * @method $this unsetVisibility()
 */
class LiveMetadata extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'is_scheduled_live'     => 'bool',
        'live_notifs_enabled'   => 'bool',
        'is_broadcast_ended'    => 'bool',
        'visibility'            => 'int',
    ];
}
