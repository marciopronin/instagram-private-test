<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * CheckConfirmationCodeResponse.
 *
 * @method mixed getMessage()
 * @method string getSignupCode()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isMessage()
 * @method bool isSignupCode()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setMessage(mixed $value)
 * @method $this setSignupCode(string $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMessage()
 * @method $this unsetSignupCode()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class CheckConfirmationCodeResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'signup_code'        => 'string',
    ];
}
