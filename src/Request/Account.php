<?php

namespace InstagramAPI\Request;

use InstagramAPI\Constants;
use InstagramAPI\Exception\InternalException;
use InstagramAPI\Exception\SettingsException;
use InstagramAPI\Request\Metadata\Internal as InternalMetadata;
use InstagramAPI\Response;
use InstagramAPI\Signatures;
use InstagramAPI\Utils;

/**
 * Account-related functions, such as profile editing and security.
 *
 * @param string $username    Username.
 * @param string $password    The password that is going to be set for the account.
 * @param string $signupCode  The signup code.
 * @param string $email       The email user for registration.
 * @param string $date        The date of birth. Format: YYYY-MM-DD.
 * @param string $firstName   First name.
 * @param string $waterfallId UUIDv4.
 * @param string $tosVersion  ToS version.
 *
 * @throws \InstagramAPI\Exception\InstagramException
 *
 * @return \InstagramAPI\Response\AccountCreateResponse
 */
class Account extends RequestCollection
{
    public function create(
        $username,
        $password,
        $signupCode,
        $email,
        $date,
        $firstName,
        $waterfallId,
        $tosVersion = 'row')
    {
        if (strlen($password) < 6) {
            throw new \InstagramAPI\Exception\InstagramException('Passwords must be at least 6 characters.');
        } elseif (in_array($password, Constants::BLACKLISTED_PASSWORDS, true)) {
            throw new \InstagramAPI\Exception\InstagramException('This is a common password. Try something that\'s harder to guess.');
        }

        $date = explode('-', $date);

        $request = $this->ig->request('accounts/create/')
            ->setNeedsAuth(false)
            ->addPost('tos_version', $tosVersion)
            ->addPost('phone_id', $this->ig->phone_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('username', $username)
            ->addPost('first_name', $firstName)
            ->addPost('adid', $this->ig->advertising_id)
            ->addPost('guid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('email', $email)
            ->addPost('day', $date[2])
            ->addPost('month', $date[1])
            ->addPost('year', $date[0])
            ->addPost('enc_password', Utils::encryptPassword($password, $this->ig->settings->get('public_key_id'), $this->ig->settings->get('public_key')))
            ->addPost('force_sign_up_code', $signupCode)
            ->addPost('waterfall_id', $waterfallId)
            ->addPost('qs_stamp', '');

        if ($this->ig->getIsAndroid()) {
            $request->addPost('sn_nonce', base64_encode($username.'|'.time().'|'.random_bytes(24)))
                ->addPost('suggestedUsername', '')
                ->addPost('is_secondary_account_creation', false)
                ->addPost('jazoest', Utils::generateJazoest($this->ig->phone_id))
                ->addPost('sn_result', sprintf('GOOGLE_PLAY_UNAVAILABLE: %s', array_rand(['SERVICE_INVALID', 'UNKNOWN', 'SERVICE_DISABLED', 'NETWORK_ERROR', 'INTERNAL_ERROR', 'CANCELED', 'INTERRUPTED', 'API_UNAVAILABLE'])))
                ->addPost('do_not_auto_login_if_credentials_match', 'true');
        } else {
            $request->addPost('do_not_auto_login_if_credentials_match', '0')
                ->addPost('force_create_account', '0')
                ->addPost('ck_error', 'NSURLErrorDomain: -1202')
                ->addPost('ck_environment', 'production')
                ->addPost('ck_environment', 'iCloud.com.burbn.instagram');
        }

        return $request->getResponse(new Response\AccountCreateResponse());
    }

    /**
     * Create an account with validated phone number.
     *
     * @param string $smsCode     The received SMS code.
     * @param string $username    Username.
     * @param string $password    The password that is going to be set for the account.
     * @param string $phone       Phone with country code. For example: '+34123456789'.
     * @param string $date        The date of birth. Format: YYYY-MM-DD.
     * @param string $firstName   First name.
     * @param string $waterfallId UUIDv4.
     * @param string $tosVersion  ToS version.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AccountCreateResponse
     */
    public function createValidated(
        $smsCode,
        $username,
        $password,
        $phone,
        $date,
        $firstName,
        $waterfallId,
        $tosVersion = 'row')
    {
        if (strlen($password) < 6) {
            throw new \InstagramAPI\Exception\InstagramException('Passwords must be at least 6 characters.');
        } elseif (in_array($password, Constants::BLACKLISTED_PASSWORDS, true)) {
            throw new \InstagramAPI\Exception\InstagramException('This is a common password. Try something that\'s harder to guess.');
        }

        $date = explode('-', $date);

        $request = $this->ig->request('accounts/create_validated/')
            ->setNeedsAuth(false)
            ->addPost('tos_version', $tosVersion)
            ->addPost('allow_contacts_sync', 'true')
            ->addPost('phone_id', $this->ig->phone_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('username', $username)
            ->addPost('first_name', $firstName)
            ->addPost('adid', $this->ig->advertising_id)
            ->addPost('guid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('phone_number', $phone)
            ->addPost('day', $date[2])
            ->addPost('month', $date[1])
            ->addPost('year', $date[0])
            ->addPost('waterfall_id', $waterfallId)
            ->addPost('enc_password', Utils::encryptPassword($password, $this->ig->settings->get('public_key_id'), $this->ig->settings->get('public_key')))
            ->addPost('verification_code', $smsCode)
            ->addPost('qs_stamp', '')
            ->addPost('has_sms_consent', 'true');

        if ($this->ig->getIsAndroid()) {
            $request->addPost('sn_nonce', base64_encode($username.'|'.time().'|'.random_bytes(24)))
                ->addPost('suggestedUsername', '')
                ->addPost('is_secondary_account_creation', false)
                ->addPost('jazoest', Utils::generateJazoest($this->ig->phone_id))
                ->addPost('sn_result', sprintf('GOOGLE_PLAY_UNAVAILABLE: %s', array_rand(['SERVICE_INVALID', 'UNKNOWN', 'SERVICE_DISABLED', 'NETWORK_ERROR', 'INTERNAL_ERROR', 'CANCELED', 'INTERRUPTED', 'API_UNAVAILABLE'])))
                ->addPost('do_not_auto_login_if_credentials_match', 'true')
                ->addPost('force_sign_up_code', '');
        } else {
            $request->addPost('do_not_auto_login_if_credentials_match', '0')
                ->addPost('force_create_account', '0')
                ->addPost('ck_error', 'NSURLErrorDomain: -1202')
                ->addPost('ck_environment', 'production')
                ->addPost('ck_environment', 'iCloud.com.burbn.instagram');
        }

        return $request->getResponse(new Response\AccountCreateResponse());
    }

    /**
     * Check if phone number is valid.
     *
     * @param string $phone Phone with country code. For example: '+34123456789'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function checkPhoneNumber(
        $phone)
    {
        return $this->ig->request('accounts/check_phone_number/')
            ->setNeedsAuth(false)
            ->addPost('prefill_shown', 'False')
            ->addPost('login_nonce_map', '{}')
            ->addPost('phone_number', $phone)
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('guid', $this->ig->uuid)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Request registration SMS code.
     *
     * @param string $phone       Phone with country code. For example: '+34123456789'.
     * @param string $waterfallId UUIDv4.
     * @param string $username    Username.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendSignupSmsCodeResponse
     */
    public function requestRegistrationSms(
        $phone,
        $waterfallId,
        $username)
    {
        $this->ig->setUserWithoutPassword($username);

        return $this->ig->request('accounts/send_signup_sms_code/')
            ->setNeedsAuth(false)
            ->addPost('phone_number', $phone)
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('device_id', $this->ig->device_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('android_build_type', 'RELEASE')
            ->addPost('guid', $this->ig->uuid)
            ->addPost('waterfall_id', $waterfallId)
            ->getResponse(new Response\SendSignupSmsCodeResponse());
    }

    /**
     * Validate signup sms code.
     *
     * @param string $smsCode     The received SMS code.
     * @param string $phone       Phone with country code. For example: '+34123456789'.
     * @param string $waterfallId UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function validateSignupSmsCode(
        $smsCode,
        $phone,
        $waterfallId)
    {
        return $this->ig->request('accounts/validate_signup_sms_code/')
            ->setNeedsAuth(false)
            ->addPost('verification_code', $smsCode)
            ->addPost('phone_number', $phone)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('guid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('waterfall_id', $waterfallId)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Send email verification code.
     *
     * @param string $email       Email.
     * @param string $waterfallId UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendVerifyEmailResponse
     */
    public function sendEmailVerificationCode(
        $email,
        $waterfallId)
    {
        return $this->ig->request('accounts/send_verify_email/')
            ->setNeedsAuth(false)
            ->addPost('email', $email)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('guid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('waterfall_id', $waterfallId)
            ->addPost('auto_confirm_only', 'false')
            ->getResponse(new Response\SendVerifyEmailResponse());
    }

    /**
     * Check confirmation code.
     *
     * @param string $code        The received code.
     * @param string $email       Email.
     * @param string $waterfallId UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckConfirmationCodeResponse
     */
    public function checkConfirmationCode(
        $code,
        $email,
        $waterfallId)
    {
        return $this->ig->request('accounts/check_confirmation_code/')
            ->setNeedsAuth(false)
            ->addPost('code', $code)
            ->addPost('email', $email)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('guid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('waterfall_id', $waterfallId)
            ->getResponse(new Response\CheckConfirmationCodeResponse());
    }

    /**
     * Get login activity and suspicious login attempts.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginActivityResponse
     */
    public function getLoginActivity()
    {
        return $this->ig->request('session/login_activity/')
            ->addParam('device_id', $this->ig->device_id)
            ->getResponse(new Response\LoginActivityResponse());
    }

    /**
     * Logout session.
     *
     * @param $sessionId    Session ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function logoutSession(
        $sessionId)
    {
        return $this->ig->request('session/login_activity/logout_session/')
            ->setSignedPost(false)
            ->addPost('session_id', $sessionId)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Approve (Confirm it was you) a suspicious login.
     *
     * @param string $loginId        Login ID.
     * @param string $loginTimestamp Login timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     *
     * @see getLoginActivity() for obtaining login IDs, login timestamps and suspicious logins
     */
    public function approveSuspiciousLogin(
        $loginId,
        $loginTimestamp)
    {
        return $this->ig->request('session/login_activity/avow_login/')
        ->setSignedPost(false)
        ->addPost('login_timestamp', $loginTimestamp)
        ->addPost('login_id', $loginId)
        //->addPost('_csrftoken', $this->ig->client->getToken())
        ->addPost('_uuid', $this->ig->uuid)
        ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get details about child and main IG accounts.
     *
     * @param bool $useAuth Indicates if auth is required for this request
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function getAccountFamily(
        $useAuth = true)
    {
        return $this->ig->request('multiple_accounts/get_account_family/')
            ->getResponse(new Response\MultipleAccountFamilyResponse());
    }

    /**
     * Get unseen facebook notifications.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UnseenFacebookNotificationsResponse
     */
    public function getUnseenFacebookNotifications()
    {
        return $this->ig->request('family_navigation/get_unseen_fb_notification_info/')
            ->getResponse(new Response\UnseenFacebookNotificationsResponse());
    }

    /**
     * Get details about the currently logged in account.
     *
     * Also try People::getSelfInfo() instead, for some different information.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     *
     * @see People::getSelfInfo()
     */
    public function getCurrentUser()
    {
        return $this->ig->request('accounts/current_user/')
            ->addParam('edit', true)
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Edit your gender.
     *
     * WARNING: Remember to also call `editProfile()` *after* using this
     * function, so that you act like the real app!
     *
     * @param string $gender this can be male, female, empty or null for 'prefer not to say' or anything else for custom
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function setGender(
        $gender = '')
    {
        switch (strtolower($gender)) {
            case 'male':$gender_id = 1; break;
            case 'female':$gender_id = 2; break;
            case null:
            case '':$gender_id = 3; break;
            default:$gender_id = 4;
        }

        return $this->ig->request('accounts/set_gender/')
            ->setSignedPost(false)
            ->addPost('gender', $gender_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('custom_gender', $gender_id === 4 ? $gender : '')
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Edit your birthday.
     *
     * WARNING: Remember to also call `editProfile()` *after* using this
     * function, so that you act like the real app!
     *
     * @param string $day   Day of birth.
     * @param string $month Month of birth.
     * @param string $year  Year of birth.
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function setBirthday(
        $day,
        $month,
        $year)
    {
        return $this->ig->request('accounts/set_birthday/')
            ->setSignedPost(false)
            ->addPost('day', $day)
            ->addPost('month', $month)
            ->addPost('year', $year)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Edit your biography.
     *
     * You are able to add `@mentions` and `#hashtags` to your biography, but
     * be aware that Instagram disallows certain web URLs and shorteners.
     *
     * Also keep in mind that anyone can read your biography (even if your
     * account is private).
     *
     * WARNING: Remember to also call `editProfile()` *after* using this
     * function, so that you act like the real app!
     *
     * @param string $biography Biography text. Use "" for nothing.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     *
     * @see Account::editProfile() should be called after this function!
     */
    public function setBiography(
        $biography)
    {
        if (!is_string($biography) || mb_strlen($biography, 'utf8') > 150) {
            throw new \InvalidArgumentException('Please provide a 0 to 150 character string as biography.');
        }

        return $this->ig->request('accounts/set_biography/')
            ->addPost('raw_text', $biography)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('device_id', $this->ig->device_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Changes your account's profile picture.
     *
     * @param string $photoFilename The photo filename.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function changeProfilePicture(
        $photoFilename)
    {
        $photo = new \InstagramAPI\Media\Photo\InstagramPhoto($photoFilename);
        $internalMetadata = new InternalMetadata(Utils::generateUploadId(true));
        $internalMetadata->setPhotoDetails(Constants::FEED_TIMELINE, $photo->getFile());
        $uploadResponse = $this->ig->internal->uploadPhotoData(Constants::FEED_TIMELINE, $internalMetadata);

        return $this->ig->request('accounts/change_profile_picture/')
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('use_fbuploader', 'true')
            ->addPost('upload_id', $internalMetadata->getUploadId())
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Remove your account's profile picture.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function removeProfilePicture()
    {
        return $this->ig->request('accounts/remove_profile_picture/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Edit your profile.
     *
     * Warning: You must provide ALL parameters to this function. The values
     * which you provide will overwrite all current values on your profile.
     * You can use getCurrentUser() to see your current values first.
     *
     * @param string      $url         Website URL. Use "" for nothing.
     * @param string      $phone       Phone number. Use "" for nothing.
     * @param string      $name        Full name. Use "" for nothing.
     * @param string      $biography   Biography text. Use "" for nothing.
     * @param string      $email       Email. Required!
     * @param string|null $newUsername (optional) Rename your account to a new username,
     *                                 which you've already verified with checkUsername().
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     *
     * @see Account::getCurrentUser() to get your current account details.
     * @see Account::checkUsername() to verify your new username first.
     */
    public function editProfile(
        $url,
        $phone,
        $name,
        $biography,
        $email,
        $newUsername = null)
    {
        if ($email === null || $email === '') {
            throw new \InvalidArgumentException('No email provided.');
        }
        // We must mark the profile for editing before doing the main request.
        $userResponse = $this->ig->request('accounts/current_user/')
            ->addParam('edit', true)
            ->getResponse(new Response\UserInfoResponse());

        // Get the current user's name from the response.
        $currentUser = $userResponse->getUser();
        if (!$currentUser || !is_string($currentUser->getUsername())) {
            throw new InternalException('Unable to find current account username while preparing profile edit.');
        }
        $oldUsername = $currentUser->getUsername();

        // Determine the desired username value.
        $username = is_string($newUsername) && strlen($newUsername) > 0
                  ? $newUsername
                  : $oldUsername; // Keep current name.

        return $this->ig->request('accounts/edit_profile/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('external_url', $url)
            ->addPost('phone_number', $phone)
            ->addPost('username', $username)
            ->addPost('first_name', $name)
            ->addPost('biography', $biography)
            ->addPost('email', $email)
            ->addPost('device_id', $this->ig->device_id)
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Get anonymous profile picture.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AnonymousProfilePictureResponse
     */
    public function getAnonymousProfilePicture()
    {
        return $this->ig->request('accounts/anonymous_profile_picture/')
                        ->getResponse(new Response\AnonymousProfilePictureResponse());
    }

    /**
     * Sets your account to public.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function setPublic()
    {
        $request = $this->ig->request('accounts/set_public/')
            ->addPost('_uuid', $this->ig->uuid);
        //->addPost('_csrftoken', $this->ig->client->getToken());

        if ($this->ig->getIsAndroid()) {
            $request->addPost('_uid', $this->ig->account_id);
        }

        return $request->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Sets your account to private.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function setPrivate()
    {
        $request = $this->ig->request('accounts/set_private/')
            ->addPost('_uuid', $this->ig->uuid);
        //->addPost('_csrftoken', $this->ig->client->getToken());

        if ($this->ig->getIsAndroid()) {
            $request->addPost('_uid', $this->ig->account_id);
        }

        return $request->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Switches your account to business profile.
     *
     * In order to switch your account to Business profile you MUST
     * call Account::setBusinessInfo().
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SwitchBusinessProfileResponse
     *
     * @see Account::setBusinessInfo() sets required data to become a business profile.
     */
    public function switchToBusinessProfile()
    {
        return $this->ig->request('business_conversion/get_business_convert_social_context/')
            ->getResponse(new Response\SwitchBusinessProfileResponse());
    }

    /**
     * Switches your account to personal profile.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SwitchPersonalProfileResponse
     */
    public function switchToPersonalProfile()
    {
        return $this->ig->request('accounts/convert_to_personal/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\SwitchPersonalProfileResponse());
    }

    /**
     * Sets contact information for business profile.
     *
     * @param string $phoneNumber Phone number with country code. Format: +34123456789.
     * @param string $email       Email.
     * @param string $categoryId  TODO: Info.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CreateBusinessInfoResponse
     */
    public function setBusinessInfo(
        $phoneNumber,
        $email,
        $categoryId)
    {
        return $this->ig->request('accounts/create_business_info/')
            ->addPost('set_public', 'true')
            ->addPost('entry_point', 'setting')
            ->addPost('public_phone_contact', json_encode([
                'public_phone_number'       => $phoneNumber,
                'business_contact_method'   => 'CALL',
            ]))
            ->addPost('public_email', $email)
            ->addPost('category_id', $categoryId)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\CreateBusinessInfoResponse());
    }

    /**
     * Check if an Instagram username is available (not already registered).
     *
     * Use this before trying to rename your Instagram account,
     * to be sure that the new username is available.
     *
     * @param string $username Instagram username to check.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckUsernameResponse
     *
     * @see Account::editProfile() to rename your account.
     */
    public function checkUsername(
        $username)
    {
        $this->ig->setUserWithoutPassword($username);

        return $this->ig->request('users/check_username/')
            ->setNeedsAuth(false)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('username', $username)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->getResponse(new Response\CheckUsernameResponse());
    }

    /**
     * Check if an email is available (not already registered).
     *
     * @param string $email       Email to check.
     * @param string $waterfallId UUIDv4.
     * @param string $username    Username.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckEmailResponse
     */
    public function checkEmail(
        $email,
        $waterfallId,
        $username)
    {
        $this->ig->setUserWithoutPassword($username);

        $request = $this->ig->request('users/check_email/')
            ->setNeedsAuth(false)
            ->addPost('email', $email);

        if ($this->ig->getIsAndroid()) {
            $request->addPost('android_device_id', $this->ig->device_id)
                ->addPost('login_nonce_map', '{}')
                ->addPost('login_nonces', '[]')
                ->addPost('qe_id', $this->ig->uuid)
                ->addPost('waterfall_id', $waterfallId);
        } else {
            $request->addPost('qe_id', $this->ig->device_id);
        }

        return $request->getResponse(new Response\CheckEmailResponse());
    }

    /**
     * Get signup config.
     *
     * @param string $username Username.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getSignupConfig(
        $username)
    {
        $this->ig->setUserWithoutPassword($username);

        return $this->ig->request('consent/get_signup_config/')
            ->setNeedsAuth(false)
            ->addParam('guid', $this->ig->uuid)
            ->addParam('main_account_selected', 'false')
            ->getResponse(new Response\CheckEmailResponse());
    }

    /**
     * Get username suggestions.
     *
     * @param string $email         Email to check.
     * @param string $waterfallId   UUIDv4.
     * @param string $usernameQuery Username query for username suggestions.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UsernameSuggestionsResponse
     */
    public function getUsernameSuggestions(
        $email,
        $waterfallId,
        $usernameQuery = '')
    {
        return $this->ig->request('accounts/username_suggestions/')
            ->setNeedsAuth(false)
            ->addPost('phone_id', $this->ig->phone_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('guid', $this->ig->uuid)
            ->addPost('name', $usernameQuery)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('email', $email)
            ->addPost('waterfall_id', $waterfallId)
            ->getResponse(new Response\UsernameSuggestionsResponse());
    }

    /**
     * Get account spam filter status.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterResponse
     */
    public function getCommentFilter()
    {
        return $this->ig->request('accounts/get_comment_filter/')
            ->getResponse(new Response\CommentFilterResponse());
    }

    /**
     * Set account spam filter status (on/off).
     *
     * @param int $config_value Whether spam filter is on (0 or 1).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterSetResponse
     */
    public function setCommentFilter(
        $config_value)
    {
        return $this->ig->request('accounts/set_comment_filter/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('config_value', $config_value)
            ->getResponse(new Response\CommentFilterSetResponse());
    }

    /**
     * Get whether the comment category filter is disabled.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentCategoryFilterResponse
     */
    public function getCommentCategoryFilterDisabled()
    {
        return $this->ig->request('accounts/get_comment_category_filter_disabled/')
            ->getResponse(new Response\CommentCategoryFilterResponse());
    }

    /**
     * Get account spam filter keywords.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterKeywordsResponse
     */
    public function getCommentFilterKeywords()
    {
        return $this->ig->request('accounts/get_comment_filter_keywords/')
            ->getResponse(new Response\CommentFilterKeywordsResponse());
    }

    /**
     * Set account spam filter keywords.
     *
     * @param string $keywords List of blocked words, separated by comma.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterSetResponse
     */
    public function setCommentFilterKeywords(
        $keywords)
    {
        return $this->ig->request('accounts/set_comment_filter_keywords/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('keywords', $keywords)
            ->getResponse(new Response\CommentFilterSetResponse());
    }

    /**
     * Change your account's password.
     *
     * @param string $oldPassword Old password.
     * @param string $newPassword New password.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ChangePasswordResponse
     */
    public function changePassword(
        $oldPassword,
        $newPassword)
    {
        return $this->ig->request('accounts/change_password/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('enc_old_password', Utils::encryptPassword($oldPassword, $this->ig->settings->get('public_key_id'), $this->ig->settings->get('public_key')))
            ->addPost('enc_new_password1', Utils::encryptPassword($newPassword, $this->ig->settings->get('public_key_id'), $this->ig->settings->get('public_key')))
            ->addPost('enc_new_password2', Utils::encryptPassword($newPassword, $this->ig->settings->get('public_key_id'), $this->ig->settings->get('public_key')))
            ->getResponse(new Response\ChangePasswordResponse());
    }

    /**
     * Get account security info and backup codes.
     *
     * WARNING: STORE AND KEEP BACKUP CODES IN A SAFE PLACE. THEY ARE EXTREMELY
     *          IMPORTANT! YOU WILL GET THE CODES IN THE RESPONSE. THE BACKUP
     *          CODES LET YOU REGAIN CONTROL OF YOUR ACCOUNT IF YOU LOSE THE
     *          PHONE NUMBER! WITHOUT THE CODES, YOU RISK LOSING YOUR ACCOUNT!
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AccountSecurityInfoResponse
     *
     * @see Account::enableTwoFactorSMS()
     */
    public function getSecurityInfo()
    {
        return $this->ig->request('accounts/account_security_info/')
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\AccountSecurityInfoResponse());
    }

    /**
     * Get account security info and backup codes.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AccountSecurityInfoResponse
     *
     * @see Account::getSecurityInfo()
     */
    public function regenBackupCodes()
    {
        return $this->ig->request('accounts/regen_backup_codes/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('device_id', $this->ig->device_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\AccountSecurityInfoResponse());
    }

    /**
     * Request that Instagram enables two factor SMS authentication.
     *
     * The SMS will have a verification code for enabling two factor SMS
     * authentication. You must then give that code to enableTwoFactorSMS().
     *
     * @param string $phoneNumber Phone number with country code. Format: +34123456789.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendTwoFactorEnableSMSResponse
     *
     * @see Account::enableTwoFactorSMS()
     */
    public function sendTwoFactorEnableSMS(
        $phoneNumber)
    {
        $cleanNumber = '+'.preg_replace('/[^0-9]/', '', $phoneNumber);

        return $this->ig->request('accounts/send_two_factor_enable_sms/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('phone_number', $cleanNumber)
            ->getResponse(new Response\SendTwoFactorEnableSMSResponse());
    }

    /**
     * Enable Two Factor authentication.
     *
     * WARNING: STORE AND KEEP BACKUP CODES IN A SAFE PLACE. THEY ARE EXTREMELY
     *          IMPORTANT! YOU WILL GET THE CODES IN THE RESPONSE. THE BACKUP
     *          CODES LET YOU REGAIN CONTROL OF YOUR ACCOUNT IF YOU LOSE THE
     *          PHONE NUMBER! WITHOUT THE CODES, YOU RISK LOSING YOUR ACCOUNT!
     *
     * @param string $phoneNumber      Phone number with country code. Format: +34123456789.
     * @param string $verificationCode The code sent to your phone via `Account::sendTwoFactorEnableSMS()`.
     * @param bool   $trustDevice      If you want to trust the used Device ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AccountSecurityInfoResponse
     *
     * @see Account::sendTwoFactorEnableSMS()
     * @see Account::getSecurityInfo()
     */
    public function enableTwoFactorSMS(
        $phoneNumber,
        $verificationCode,
        $trustDevice = false)
    {
        $cleanNumber = '+'.preg_replace('/[^0-9]/', '', $phoneNumber);

        $this->ig->request('accounts/enable_sms_two_factor/')
            ->addPost('trust_this_device', ($trustDevice) ? '1' : '0')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('phone_number', $cleanNumber)
            ->addPost('verification_code', $verificationCode)
            ->getResponse(new Response\EnableTwoFactorSMSResponse());

        return $this->getSecurityInfo();
    }

    /**
     * Disable Two Factor authentication.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DisableTwoFactorSMSResponse
     */
    public function disableTwoFactorSMS()
    {
        return $this->ig->request('accounts/disable_sms_two_factor/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\DisableTwoFactorSMSResponse());
    }

    /**
     * Save presence status to the storage.
     *
     * @param bool $disabled
     */
    protected function _savePresenceStatus(
        $disabled)
    {
        try {
            $this->ig->settings->set('presence_disabled', $disabled ? '1' : '0');
        } catch (SettingsException $e) {
            // Ignore storage errors.
        }
    }

    /**
     * Get presence status.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PresenceStatusResponse
     */
    public function getPresenceStatus()
    {
        return $this->ig->request('accounts/get_presence_disabled/')
            ->setSignedGet(true)
            ->getResponse(new Response\PresenceStatusResponse());
    }

    /**
     * Enable presence.
     *
     * Allow accounts you follow and anyone you message to see when you were
     * last active on Instagram apps.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function enablePresence()
    {
        /** @var Response\GenericResponse $result */
        $result = $this->ig->request('accounts/set_presence_disabled/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('disabled', '0')
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());

        $this->_savePresenceStatus(false);

        return $result;
    }

    /**
     * Disable presence.
     *
     * You won't be able to see the activity status of other accounts.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function disablePresence()
    {
        /** @var Response\GenericResponse $result */
        $result = $this->ig->request('accounts/set_presence_disabled/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('disabled', '1')
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());

        $this->_savePresenceStatus(true);

        return $result;
    }

    /**
     * Tell Instagram to send you a message to verify your email address.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendConfirmEmailResponse
     */
    public function sendConfirmEmail()
    {
        return $this->ig->request('accounts/send_confirm_email/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('send_source', 'edit_profile')
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\SendConfirmEmailResponse());
    }

    /**
     * Tell Instagram to send you an SMS code to verify your phone number.
     *
     * @param string $phoneNumber Phone number with country code. Format: +34123456789.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendSMSCodeResponse
     */
    public function sendSMSCode(
        $phoneNumber)
    {
        $cleanNumber = '+'.preg_replace('/[^0-9]/', '', $phoneNumber);

        return $this->ig->request('accounts/send_sms_code/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('phone_number', $cleanNumber)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\SendSMSCodeResponse());
    }

    /**
     * Submit the SMS code you received to verify your phone number.
     *
     * @param string $phoneNumber      Phone number with country code. Format: +34123456789.
     * @param string $verificationCode The code sent to your phone via `Account::sendSMSCode()`.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\VerifySMSCodeResponse
     *
     * @see Account::sendSMSCode()
     */
    public function verifySMSCode(
        $phoneNumber,
        $verificationCode)
    {
        $cleanNumber = '+'.preg_replace('/[^0-9]/', '', $phoneNumber);

        return $this->ig->request('accounts/verify_sms_code/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('phone_number', $cleanNumber)
            ->addPost('verification_code', $verificationCode)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\VerifySMSCodeResponse());
    }

    /**
     * Generate TOTP Code.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\TotpCodeResponse
     */
    public function getTOTPCode()
    {
        return $this->ig->request('accounts/generate_two_factor_totp_key/')
            ->setSignedPost(false)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->getResponse(new Response\TotpCodeResponse());
    }

    /**
     * Enable TOTP Two factor authentication.
     *
     * @param string $code OTP code (6-digit code).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function enableTOTPAuthentication(
        $code)
    {
        return $this->ig->request('accounts/enable_totp_two_factor/')
            ->addPost('verification_code', $code)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Set contact point prefill.
     *
     * @param string $usage Either "prefill" or "auto_confirmation".
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function setContactPointPrefill(
        $usage)
    {
        $request = $this->ig->request('accounts/contact_point_prefill/')
            ->setNeedsAuth(false)
            ->addPost('phone_id', $this->ig->phone_id);

        if ($this->ig->getIsAndroid()) {
            $request//->addPost('_csrftoken', $this->ig->client->getToken())
                    ->addPost('usage', $usage);
        }

        return $request->getResponse(new Response\GenericResponse());
    }

    /**
     * Get name prefill.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getNamePrefill()
    {
        $request = $this->ig->request('accounts/get_name_prefill/')
            ->setNeedsAuth(false)
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('device_id', $this->ig->device_id)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     *  Get prefill candidates.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PrefillCandidatesResponse
     */
    public function getPrefillCandidates()
    {
        return $this->ig->request('accounts/get_prefill_candidates/')
            ->setNeedsAuth(false)
            ->addPost('android_device_id', $this->ig->device_id)
            ->addPost('device_id', $this->ig->uuid)
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('usages', '["account_recovery_omnibox"]')
            ->getResponse(new Response\PrefillCandidatesResponse());
    }

    /**
     * Get account badge notifications for the "Switch account" menu.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\BadgeNotificationsResponse
     */
    public function getBadgeNotifications()
    {
        return $this->ig->request('notifications/badge/')
            ->setSignedPost(false)
            ->addPost('_uuid', $this->ig->uuid)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('user_ids', $this->ig->account_id)
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('device_id', $this->ig->uuid)
            ->getResponse(new Response\BadgeNotificationsResponse());
    }

    /**
     * Get Facebook ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FacebookIdResponse
     */
    public function getFacebookId()
    {
        return $this->ig->request('fb/get_connected_fbid/')
            ->setSignedPost(false)
            ->setIsSilentFail(true)
            ->addPost('_uuid', $this->ig->uuid)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\FacebookIdResponse());
    }

    /**
     * Get linked accounts status.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LinkageStatusResponse
     */
    public function getLinkageStatus()
    {
        return $this->ig->request('linked_accounts/get_linkage_status/')
            ->getResponse(new Response\LinkageStatusResponse());
    }

    /**
     * Get cross posting destination (Cross posting to Facebook).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getCrossPostingDestinationStatus()
    {
        return $this->ig->request('ig_fb_xposting/account_linking/user_xposting_destination/')
            ->addParam('signed_body', Signatures::generateSignature().'.{}')
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * TODO.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getProcessContactPointSignals()
    {
        return $this->ig->request('accounts/process_contact_point_signals/')
            ->addPost('google_tokens', '[]')
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Send recovery flow via email.
     *
     * @param string $query Username or email.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendRecoveryFlowResponse
     */
    public function sendRecoveryFlowEmail(
        $query)
    {
        return $this->ig->request('accounts/send_recovery_flow_email/')
            ->addPost('guid', $this->ig->uuid)
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('adid', $this->ig->advertising_id)
            ->addPost('query', $query)
            ->addPost('device_id', $this->ig->device_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\SendRecoveryFlowResponse());
    }

    /**
     * Send recovery flow via phone.
     *
     * @param string $query             Username or email.
     * @param bool   $whatsAppInstalled Wether WhatsApp is installed or not.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LookupPhoneResponse
     */
    public function lookupPhone(
        $query,
        $whatsAppInstalled = false)
    {
        return $this->ig->request('users/lookup_phone/')
            ->addPost('supports_sms_code', 'true')
            ->addPost('use_whatsapp', $whatsAppInstalled)
            ->addPost('guid', $this->ig->uuid)
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('adid', $this->ig->advertising_id)
            ->addPost('query', $query)
            ->addPost('device_id', $this->ig->device_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\LookupPhoneResponse());
    }

    /**
     * Get accounts multi login.
     *
     * Returns logged in data of accounts.
     *
     * @param string $macLoginNonce Mac login nonce.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MultiAccountsResponse
     */
    public function getAccountsMultiLogin(
        $macLoginNonce)
    {
        return $this->ig->request('multiple_accounts/multi_account_login/')
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->addPost('mac_login_nonce', $macLoginNonce)
            ->addPost('logged_in_user_ids', $this->ig->account_id)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('guid', $this->ig->uuid)
            ->addPost('_uuid', $this->ig->uuid)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\MultiAccountsResponse());
    }

    /**
     * Set if user can tag you in media posts.
     *
     * @param bool $set true for enabling and false for disabling.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function updateTagSettingsAction(
        $set)
    {
        return $this->ig->request('bloks/apps/com.instagram.bullying.privacy.tags_options.update_tag_setting_action/')
            ->setSignedPost(false)
            ->addPost('tag_setting', $set ? 'on' : 'off')
            ->addPost('bloks_versioning_id', Constants::BLOCK_VERSIONING_ID)
            ->addPost('nest_data_manifest', 'true')
            ->addPost('_uuid', $this->ig->uuid)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Set if user can mention you in media posts.
     *
     * @param bool $set true for enabling and false for disabling.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function updateMentionSettingsAction(
        $set)
    {
        return $this->ig->request('bloks/apps/com.instagram.bullying.privacy.mentions_options.update_mention_settting_action/')
            ->setSignedPost(false)
            ->addPost('tag_setting', $set ? 'on' : 'off')
            ->addPost('bloks_versioning_id', Constants::BLOCK_VERSIONING_ID)
            ->addPost('nest_data_manifest', 'true')
            ->addPost('_uuid', $this->ig->uuid)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get synced Facebook pages IDs.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return string[]
     */
    public function getSyncedFacebookPagesIds()
    {
        $response = $this->ig->request('bloks/apps/com.bloks.www.fxcal.settings.post.account.async/')
            ->setSignedPost(false)
            ->addPost('params', json_encode((object) [
                'server_params' => [
                    'account_id'    => $this->ig->account_id,
                    'newly_linked'  => 0,
                    'entrypoint'    => 1,
                ],
            ]))
            ->addPost('bk_client_context', json_encode((object) [
                'bloks_version' => Constants::BLOCK_VERSIONING_ID,
                'styles_id'     => 'instagram',
                'ttrc_join_id'  => Signatures::generateUUID(),
            ]))
            ->addPost('bloks_versioning_id', Constants::BLOCK_VERSIONING_ID)
            ->addPost('_uuid', $this->ig->uuid)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());

        $re = '/\"(\d{15})\\\\/m';
        preg_match_all($re, $response->asJson(), $matches, PREG_SET_ORDER, 0);
        $ids = [];
        foreach ($matches as $id) {
            $id = $id[1];

            if ($id[0] === '1' && ($id[1] === '0' || $id[1] === '1')) {
                $ids[] = $id;
            }
        }

        return array_unique($ids);
    }
}
