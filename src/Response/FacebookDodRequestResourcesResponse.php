<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * FacebookDodRequestResourcesResponse.
 *
 * @method mixed getMessage()
 * @method Model\Resource getResource()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isMessage()
 * @method bool isResource()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setMessage(mixed $value)
 * @method $this setResource(Model\Resource $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMessage()
 * @method $this unsetResource()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class FacebookDodRequestResourcesResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'resource'           => 'Model\Resource',
    ];
}
