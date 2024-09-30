<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * TwoFactorInfo.
 *
 * @method string getObfuscatedPhoneNumber()
 * @method PhoneVerificationSettings getPhoneVerificationSettings()
 * @method bool getShowMessengerCodeOption()
 * @method bool getShowNewLoginScreen()
 * @method bool getShowTrustedDeviceOption()
 * @method bool getSmsTwoFactorOn()
 * @method bool getTotpTwoFactorOn()
 * @method string getTrustedNotificationPollingNonce()
 * @method string getTwoFactorIdentifier()
 * @method string getUsername()
 * @method bool getWhatsappTwoFactorOn()
 * @method bool isObfuscatedPhoneNumber()
 * @method bool isPhoneVerificationSettings()
 * @method bool isShowMessengerCodeOption()
 * @method bool isShowNewLoginScreen()
 * @method bool isShowTrustedDeviceOption()
 * @method bool isSmsTwoFactorOn()
 * @method bool isTotpTwoFactorOn()
 * @method bool isTrustedNotificationPollingNonce()
 * @method bool isTwoFactorIdentifier()
 * @method bool isUsername()
 * @method bool isWhatsappTwoFactorOn()
 * @method $this setObfuscatedPhoneNumber(string $value)
 * @method $this setPhoneVerificationSettings(PhoneVerificationSettings $value)
 * @method $this setShowMessengerCodeOption(bool $value)
 * @method $this setShowNewLoginScreen(bool $value)
 * @method $this setShowTrustedDeviceOption(bool $value)
 * @method $this setSmsTwoFactorOn(bool $value)
 * @method $this setTotpTwoFactorOn(bool $value)
 * @method $this setTrustedNotificationPollingNonce(string $value)
 * @method $this setTwoFactorIdentifier(string $value)
 * @method $this setUsername(string $value)
 * @method $this setWhatsappTwoFactorOn(bool $value)
 * @method $this unsetObfuscatedPhoneNumber()
 * @method $this unsetPhoneVerificationSettings()
 * @method $this unsetShowMessengerCodeOption()
 * @method $this unsetShowNewLoginScreen()
 * @method $this unsetShowTrustedDeviceOption()
 * @method $this unsetSmsTwoFactorOn()
 * @method $this unsetTotpTwoFactorOn()
 * @method $this unsetTrustedNotificationPollingNonce()
 * @method $this unsetTwoFactorIdentifier()
 * @method $this unsetUsername()
 * @method $this unsetWhatsappTwoFactorOn()
 */
class TwoFactorInfo extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'username'                              => 'string',
        'two_factor_identifier'                 => 'string',
        'phone_verification_settings'           => 'PhoneVerificationSettings',
        'obfuscated_phone_number'               => 'string',
        'sms_two_factor_on'                     => 'bool',
        'whatsapp_two_factor_on'                => 'bool',
        'totp_two_factor_on'                    => 'bool',
        'show_messenger_code_option'            => 'bool',
        'show_new_login_screen'                 => 'bool',
        'show_trusted_device_option'            => 'bool',
        'trusted_notification_polling_nonce'    => 'string',
    ];
}
