<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * HasInteropUpgradedResponse.
 *
 * @method bool getHasInteropUpgraded()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isHasInteropUpgraded()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setHasInteropUpgraded(bool $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetHasInteropUpgraded()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class HasInteropUpgradedResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'has_interop_upgraded'             => 'bool',
    ];
}
