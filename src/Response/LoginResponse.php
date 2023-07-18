<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * LoginResponse.
 *
 * @method int getAccountType()
 * @method string getAction()
 * @method bool getAllowContactsSync()
 * @method string getAllowedCommenterType()
 * @method Model\LoginButton[] getButtons()
 * @method bool getCanBoostPost()
 * @method bool getCanSeeOrganicInsights()
 * @method Model\Challenge getChallenge()
 * @method string getCheckpointUrl()
 * @method int getCountryCode()
 * @method string getErrorTitle()
 * @method string getErrorType()
 * @method string getExceptionName()
 * @method string getFullName()
 * @method bool getHasAnonymousProfilePicture()
 * @method bool getHasIgtvSeries()
 * @method bool getHasPlacedOrders()
 * @method string getHelpUrl()
 * @method mixed getInvalidCredentials()
 * @method bool getIsBloks()
 * @method bool getIsBusiness()
 * @method mixed getIsCallToActionEnabled()
 * @method bool getIsPrivate()
 * @method bool getIsVerified()
 * @method bool getLock()
 * @method Model\User getLoggedInUser()
 * @method string getMacLoginNonce()
 * @method string getMaskedCp()
 * @method mixed getMessage()
 * @method Model\Nametag getNametag()
 * @method int getNationalNumber()
 * @method string getPhoneNumber()
 * @method Model\PhoneVerificationSettings getPhoneVerificationSettings()
 * @method string getPk()
 * @method string getProfilePicId()
 * @method string getProfilePicUrl()
 * @method string getReelAutoArchive()
 * @method bool getShowInsightsTerms()
 * @method string getStatus()
 * @method int getTotalIgtvVideos()
 * @method string getTwoFactorChallenge()
 * @method string getTwoFactorContext()
 * @method Model\TwoFactorInfo getTwoFactorInfo()
 * @method mixed getTwoFactorRequired()
 * @method string getUsername()
 * @method Model\_Message[] get_Messages()
 * @method bool isAccountType()
 * @method bool isAction()
 * @method bool isAllowContactsSync()
 * @method bool isAllowedCommenterType()
 * @method bool isButtons()
 * @method bool isCanBoostPost()
 * @method bool isCanSeeOrganicInsights()
 * @method bool isChallenge()
 * @method bool isCheckpointUrl()
 * @method bool isCountryCode()
 * @method bool isErrorTitle()
 * @method bool isErrorType()
 * @method bool isExceptionName()
 * @method bool isFullName()
 * @method bool isHasAnonymousProfilePicture()
 * @method bool isHasIgtvSeries()
 * @method bool isHasPlacedOrders()
 * @method bool isHelpUrl()
 * @method bool isInvalidCredentials()
 * @method bool isIsBloks()
 * @method bool isIsBusiness()
 * @method bool isIsCallToActionEnabled()
 * @method bool isIsPrivate()
 * @method bool isIsVerified()
 * @method bool isLock()
 * @method bool isLoggedInUser()
 * @method bool isMacLoginNonce()
 * @method bool isMaskedCp()
 * @method bool isMessage()
 * @method bool isNametag()
 * @method bool isNationalNumber()
 * @method bool isPhoneNumber()
 * @method bool isPhoneVerificationSettings()
 * @method bool isPk()
 * @method bool isProfilePicId()
 * @method bool isProfilePicUrl()
 * @method bool isReelAutoArchive()
 * @method bool isShowInsightsTerms()
 * @method bool isStatus()
 * @method bool isTotalIgtvVideos()
 * @method bool isTwoFactorChallenge()
 * @method bool isTwoFactorContext()
 * @method bool isTwoFactorInfo()
 * @method bool isTwoFactorRequired()
 * @method bool isUsername()
 * @method bool is_Messages()
 * @method $this setAccountType(int $value)
 * @method $this setAction(string $value)
 * @method $this setAllowContactsSync(bool $value)
 * @method $this setAllowedCommenterType(string $value)
 * @method $this setButtons(Model\LoginButton[] $value)
 * @method $this setCanBoostPost(bool $value)
 * @method $this setCanSeeOrganicInsights(bool $value)
 * @method $this setChallenge(Model\Challenge $value)
 * @method $this setCheckpointUrl(string $value)
 * @method $this setCountryCode(int $value)
 * @method $this setErrorTitle(string $value)
 * @method $this setErrorType(string $value)
 * @method $this setExceptionName(string $value)
 * @method $this setFullName(string $value)
 * @method $this setHasAnonymousProfilePicture(bool $value)
 * @method $this setHasIgtvSeries(bool $value)
 * @method $this setHasPlacedOrders(bool $value)
 * @method $this setHelpUrl(string $value)
 * @method $this setInvalidCredentials(mixed $value)
 * @method $this setIsBloks(bool $value)
 * @method $this setIsBusiness(bool $value)
 * @method $this setIsCallToActionEnabled(mixed $value)
 * @method $this setIsPrivate(bool $value)
 * @method $this setIsVerified(bool $value)
 * @method $this setLock(bool $value)
 * @method $this setLoggedInUser(Model\User $value)
 * @method $this setMacLoginNonce(string $value)
 * @method $this setMaskedCp(string $value)
 * @method $this setMessage(mixed $value)
 * @method $this setNametag(Model\Nametag $value)
 * @method $this setNationalNumber(int $value)
 * @method $this setPhoneNumber(string $value)
 * @method $this setPhoneVerificationSettings(Model\PhoneVerificationSettings $value)
 * @method $this setPk(string $value)
 * @method $this setProfilePicId(string $value)
 * @method $this setProfilePicUrl(string $value)
 * @method $this setReelAutoArchive(string $value)
 * @method $this setShowInsightsTerms(bool $value)
 * @method $this setStatus(string $value)
 * @method $this setTotalIgtvVideos(int $value)
 * @method $this setTwoFactorChallenge(string $value)
 * @method $this setTwoFactorContext(string $value)
 * @method $this setTwoFactorInfo(Model\TwoFactorInfo $value)
 * @method $this setTwoFactorRequired(mixed $value)
 * @method $this setUsername(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetAccountType()
 * @method $this unsetAction()
 * @method $this unsetAllowContactsSync()
 * @method $this unsetAllowedCommenterType()
 * @method $this unsetButtons()
 * @method $this unsetCanBoostPost()
 * @method $this unsetCanSeeOrganicInsights()
 * @method $this unsetChallenge()
 * @method $this unsetCheckpointUrl()
 * @method $this unsetCountryCode()
 * @method $this unsetErrorTitle()
 * @method $this unsetErrorType()
 * @method $this unsetExceptionName()
 * @method $this unsetFullName()
 * @method $this unsetHasAnonymousProfilePicture()
 * @method $this unsetHasIgtvSeries()
 * @method $this unsetHasPlacedOrders()
 * @method $this unsetHelpUrl()
 * @method $this unsetInvalidCredentials()
 * @method $this unsetIsBloks()
 * @method $this unsetIsBusiness()
 * @method $this unsetIsCallToActionEnabled()
 * @method $this unsetIsPrivate()
 * @method $this unsetIsVerified()
 * @method $this unsetLock()
 * @method $this unsetLoggedInUser()
 * @method $this unsetMacLoginNonce()
 * @method $this unsetMaskedCp()
 * @method $this unsetMessage()
 * @method $this unsetNametag()
 * @method $this unsetNationalNumber()
 * @method $this unsetPhoneNumber()
 * @method $this unsetPhoneVerificationSettings()
 * @method $this unsetPk()
 * @method $this unsetProfilePicId()
 * @method $this unsetProfilePicUrl()
 * @method $this unsetReelAutoArchive()
 * @method $this unsetShowInsightsTerms()
 * @method $this unsetStatus()
 * @method $this unsetTotalIgtvVideos()
 * @method $this unsetTwoFactorChallenge()
 * @method $this unsetTwoFactorContext()
 * @method $this unsetTwoFactorInfo()
 * @method $this unsetTwoFactorRequired()
 * @method $this unsetUsername()
 * @method $this unset_Messages()
 */
class LoginResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'username'                      => 'string',
        'has_anonymous_profile_picture' => 'bool',
        'can_boost_post'                => 'bool',
        'is_business'                   => 'bool',
        'account_type'                  => 'int',
        'is_call_to_action_enabled'     => '',
        'can_see_organic_insights'      => 'bool',
        'show_insights_terms'           => 'bool',
        'total_igtv_videos'             => 'int',
        'has_igtv_series'               => 'bool',
        'has_placed_orders'             => 'bool',
        'nametag'                       => 'Model\Nametag',
        'profile_pic_url'               => 'string',
        'profile_pic_id'                => 'string',
        'full_name'                     => 'string',
        'pk'                            => 'string',
        'is_private'                    => 'bool',
        'is_verified'                   => 'bool',
        'allowed_commenter_type'        => 'string',
        'reel_auto_archive'             => 'string',
        'allow_contacts_sync'           => 'bool',
        'phone_number'                  => 'string',
        'country_code'                  => 'int',
        'national_number'               => 'int',
        'error_title'                   => 'string', // On wrong pass or other account related error.
        'error_type'                    => 'string', // On wrong pass or other account related error.
        'exception_name'                => 'string',
        'buttons'                       => 'Model\LoginButton[]', // On wrong pass or other account related error.
        'invalid_credentials'           => '', // On wrong pass or other account related error.
        'logged_in_user'                => 'Model\User',
        'two_factor_required'           => '',
        'phone_verification_settings'   => 'Model\PhoneVerificationSettings',
        'two_factor_info'               => 'Model\TwoFactorInfo',
        'checkpoint_url'                => 'string',
        'lock'                          => 'bool',
        'help_url'                      => 'string',
        'challenge'                     => 'Model\Challenge',
        'action'                        => 'string',
        'mac_login_nonce'               => 'string',
        'two_factor_challenge'          => 'string', // custom
        'two_factor_context'            => 'string', // custom
        'masked_cp'                     => 'string', // custom
        'is_bloks'                      => 'bool',   // custom
    ];
}
