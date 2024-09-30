<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * ChallengeFields.
 *
 * @method bool getCodeWhitelisted()
 * @method int getDisableNumDaysRemaining()
 * @method string getFormType()
 * @method string getG_x2D_recaptcha_x2D_response()
 * @method string getPhoneNumberPreview()
 * @method int getResendDelay()
 * @method string getSecurityCode()
 * @method string getSitekey()
 * @method int getSmsResendDelay()
 * @method bool isCodeWhitelisted()
 * @method bool isDisableNumDaysRemaining()
 * @method bool isFormType()
 * @method bool isG_x2D_recaptcha_x2D_response()
 * @method bool isPhoneNumberPreview()
 * @method bool isResendDelay()
 * @method bool isSecurityCode()
 * @method bool isSitekey()
 * @method bool isSmsResendDelay()
 * @method $this setCodeWhitelisted(bool $value)
 * @method $this setDisableNumDaysRemaining(int $value)
 * @method $this setFormType(string $value)
 * @method $this setG_x2D_recaptcha_x2D_response(string $value)
 * @method $this setPhoneNumberPreview(string $value)
 * @method $this setResendDelay(int $value)
 * @method $this setSecurityCode(string $value)
 * @method $this setSitekey(string $value)
 * @method $this setSmsResendDelay(int $value)
 * @method $this unsetCodeWhitelisted()
 * @method $this unsetDisableNumDaysRemaining()
 * @method $this unsetFormType()
 * @method $this unsetG_x2D_recaptcha_x2D_response()
 * @method $this unsetPhoneNumberPreview()
 * @method $this unsetResendDelay()
 * @method $this unsetSecurityCode()
 * @method $this unsetSitekey()
 * @method $this unsetSmsResendDelay()
 */
class ChallengeFields extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'g-recaptcha-response'          => 'string',
        'disable_num_days_remaining'    => 'int',
        'sitekey'                       => 'string',
        'code_whitelisted'              => 'bool',
        'security_code'                 => 'string',
        'sms_resend_delay'              => 'int',
        'phone_number_preview'          => 'string',
        'resend_delay'                  => 'int',
        'form_type'                     => 'string',
    ];
}
