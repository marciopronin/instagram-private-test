<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * CrossPostingDestinationStatusResponse.
 *
 * @method Model\LinkedFbUser getLinkedFbUser()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\XpostDestination getXpostDestination()
 * @method Model\_Message[] get_Messages()
 * @method bool isLinkedFbUser()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isXpostDestination()
 * @method bool is_Messages()
 * @method $this setLinkedFbUser(Model\LinkedFbUser $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setXpostDestination(Model\XpostDestination $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetLinkedFbUser()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetXpostDestination()
 * @method $this unset_Messages()
 */
class CrossPostingDestinationStatusResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'linked_fb_user'        => 'Model\LinkedFbUser',
        'xpost_destination'     => 'Model\XpostDestination',
    ];
}
