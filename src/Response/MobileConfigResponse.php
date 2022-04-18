<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * MobileConfigResponse.
 *
 * @method mixed getConfigs()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isConfigs()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setConfigs(mixed $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetConfigs()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class MobileConfigResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'configs'   => '',
    ];
}
