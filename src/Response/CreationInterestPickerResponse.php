<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * CreationInterestPickerResponse.
 *
 * @method Model\InterestTopic[] getInterests()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isInterests()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setInterests(Model\InterestTopic[] $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetInterests()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class CreationInterestPickerResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'interests'  => 'Model\InterestTopic[]',
    ];
}
