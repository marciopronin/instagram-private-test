<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * ChallengeFields.
 *
 * @method bool getCodeWhitelisted()
 * @method int getDisableNumDaysRemaining()
 * @method string getG_x2D_recaptcha_x2D_response()
 * @method string getSitekey()
 * @method bool isCodeWhitelisted()
 * @method bool isDisableNumDaysRemaining()
 * @method bool isG_x2D_recaptcha_x2D_response()
 * @method bool isSitekey()
 * @method $this setCodeWhitelisted(bool $value)
 * @method $this setDisableNumDaysRemaining(int $value)
 * @method $this setG_x2D_recaptcha_x2D_response(string $value)
 * @method $this setSitekey(string $value)
 * @method $this unsetCodeWhitelisted()
 * @method $this unsetDisableNumDaysRemaining()
 * @method $this unsetG_x2D_recaptcha_x2D_response()
 * @method $this unsetSitekey()
 */
class ChallengeFields extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'g-recaptcha-response'          => 'string',
        'disable_num_days_remaining'    => 'int',
        'sitekey'                       => 'string',
        'code_whitelisted'              => 'bool',
    ];
}
