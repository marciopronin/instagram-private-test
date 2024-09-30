<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * ConfirmEmailResponse.
 *
 * @method string getBody()
 * @method bool getIsProfileActionNeeded()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method string getTitle()
 * @method Model\_Message[] get_Messages()
 * @method bool isBody()
 * @method bool isIsProfileActionNeeded()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isTitle()
 * @method bool is_Messages()
 * @method $this setBody(string $value)
 * @method $this setIsProfileActionNeeded(bool $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setTitle(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetBody()
 * @method $this unsetIsProfileActionNeeded()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetTitle()
 * @method $this unset_Messages()
 */
class ConfirmEmailResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'is_profile_action_needed'  => 'bool',
        'title'                     => 'string',
        'body'                      => 'string',
    ];
}
