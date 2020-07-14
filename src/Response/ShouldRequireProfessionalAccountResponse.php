<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * ShouldRequireProfessionalAccountResponse.
 *
 * @method mixed getMessage()
 * @method bool getRequireProfessionalAccount()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isMessage()
 * @method bool isRequireProfessionalAccount()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setMessage(mixed $value)
 * @method $this setRequireProfessionalAccount(bool $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMessage()
 * @method $this unsetRequireProfessionalAccount()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class ShouldRequireProfessionalAccountResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'require_professional_account' => 'bool',
    ];
}
