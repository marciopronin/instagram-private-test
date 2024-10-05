<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * TokenResultResponse.
 *
 * @method Model\Token getFreeToken()
 * @method mixed getMessage()
 * @method Model\Token getNormalToken()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isFreeToken()
 * @method bool isMessage()
 * @method bool isNormalToken()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setFreeToken(Model\Token $value)
 * @method $this setMessage(mixed $value)
 * @method $this setNormalToken(Model\Token $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetFreeToken()
 * @method $this unsetMessage()
 * @method $this unsetNormalToken()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class TokenResultResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'normal_token' => 'Model\Token',
        'free_token'   => 'Model\Token',
    ];
}
