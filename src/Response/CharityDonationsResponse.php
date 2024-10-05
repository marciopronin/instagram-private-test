<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * CharityDonationsResponse.
 *
 * @method mixed getCharityDonations()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isCharityDonations()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setCharityDonations(mixed $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetCharityDonations()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class CharityDonationsResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'charity_donations' => '',
    ];
}
