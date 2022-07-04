<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * NonExpiredRequestsInfoResponse.
 *
 * @method bool getHasNonExpiredRequest()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isHasNonExpiredRequest()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setHasNonExpiredRequest(bool $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetHasNonExpiredRequest()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class NonExpiredRequestsInfoResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'has_non_expired_request' => 'bool',
    ];
}
