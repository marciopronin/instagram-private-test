<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * StepData.
 *
 * @method string getBigBlueToken()
 * @method string getChoice()
 * @method string getCity()
 * @method string getContactPoint()
 * @method string getCountry()
 * @method string getEmail()
 * @method string getEnrollmentDate()
 * @method string getEnrollmentTime()
 * @method string getFbAccessToken()
 * @method string getFormType()
 * @method string getGoogleOauthToken()
 * @method float getLatitude()
 * @method float getLongitude()
 * @method string getPhoneNumber()
 * @method string getPhoneNumberFormatted()
 * @method string getPhoneNumberPreview()
 * @method string getPlatform()
 * @method int getResendDelay()
 * @method string getSecurityCode()
 * @method int getSmsResendDelay()
 * @method string getUserAgent()
 * @method bool isBigBlueToken()
 * @method bool isChoice()
 * @method bool isCity()
 * @method bool isContactPoint()
 * @method bool isCountry()
 * @method bool isEmail()
 * @method bool isEnrollmentDate()
 * @method bool isEnrollmentTime()
 * @method bool isFbAccessToken()
 * @method bool isFormType()
 * @method bool isGoogleOauthToken()
 * @method bool isLatitude()
 * @method bool isLongitude()
 * @method bool isPhoneNumber()
 * @method bool isPhoneNumberFormatted()
 * @method bool isPhoneNumberPreview()
 * @method bool isPlatform()
 * @method bool isResendDelay()
 * @method bool isSecurityCode()
 * @method bool isSmsResendDelay()
 * @method bool isUserAgent()
 * @method $this setBigBlueToken(string $value)
 * @method $this setChoice(string $value)
 * @method $this setCity(string $value)
 * @method $this setContactPoint(string $value)
 * @method $this setCountry(string $value)
 * @method $this setEmail(string $value)
 * @method $this setEnrollmentDate(string $value)
 * @method $this setEnrollmentTime(string $value)
 * @method $this setFbAccessToken(string $value)
 * @method $this setFormType(string $value)
 * @method $this setGoogleOauthToken(string $value)
 * @method $this setLatitude(float $value)
 * @method $this setLongitude(float $value)
 * @method $this setPhoneNumber(string $value)
 * @method $this setPhoneNumberFormatted(string $value)
 * @method $this setPhoneNumberPreview(string $value)
 * @method $this setPlatform(string $value)
 * @method $this setResendDelay(int $value)
 * @method $this setSecurityCode(string $value)
 * @method $this setSmsResendDelay(int $value)
 * @method $this setUserAgent(string $value)
 * @method $this unsetBigBlueToken()
 * @method $this unsetChoice()
 * @method $this unsetCity()
 * @method $this unsetContactPoint()
 * @method $this unsetCountry()
 * @method $this unsetEmail()
 * @method $this unsetEnrollmentDate()
 * @method $this unsetEnrollmentTime()
 * @method $this unsetFbAccessToken()
 * @method $this unsetFormType()
 * @method $this unsetGoogleOauthToken()
 * @method $this unsetLatitude()
 * @method $this unsetLongitude()
 * @method $this unsetPhoneNumber()
 * @method $this unsetPhoneNumberFormatted()
 * @method $this unsetPhoneNumberPreview()
 * @method $this unsetPlatform()
 * @method $this unsetResendDelay()
 * @method $this unsetSecurityCode()
 * @method $this unsetSmsResendDelay()
 * @method $this unsetUserAgent()
 */
class StepData extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'choice'                    => 'string',
        'country'                   => 'string',
        'enrollment_time'           => 'string',
        'enrollment_date'           => 'string',
        'latitude'                  => 'float',
        'longitude'                 => 'float',
        'city'                      => 'string',
        'platform'                  => 'string',
        'user_agent'                => 'string',
        'phone_number'              => 'string',
        'phone_number_formatted'    => 'string',
        'email'                     => 'string',
        'fb_access_token'           => 'string',
        'big_blue_token'            => 'string',
        'google_oauth_token'        => 'string',
        'security_code'             => 'string',
        'sms_resend_delay'          => 'int',
        'resend_delay'              => 'int',
        'contact_point'             => 'string',
        'form_type'                 => 'string',
        'phone_number_preview'      => 'string',
    ];
}
