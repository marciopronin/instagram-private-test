<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * FundraiserInfoResponse.
 *
 * @method string getFundraiserId()
 * @method string getFundraiserTitle()
 * @method bool getHasActiveFundraiser()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isFundraiserId()
 * @method bool isFundraiserTitle()
 * @method bool isHasActiveFundraiser()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setFundraiserId(string $value)
 * @method $this setFundraiserTitle(string $value)
 * @method $this setHasActiveFundraiser(bool $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetFundraiserId()
 * @method $this unsetFundraiserTitle()
 * @method $this unsetHasActiveFundraiser()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class FundraiserInfoResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'has_active_fundraiser'        => 'bool',
        'fundraiser_id'                => 'string',
        'fundraiser_title'             => 'string',
    ];
}