<?php

require __DIR__.'/../../vendor/autoload.php';

class ExtendedInstagram extends \InstagramAPI\Instagram
{
    public function sendLoginFlow()
    {
        $this->_sendLoginFlow(true);
    }
}


/////////////////
$debug = true;
/////////////////

/////////////////
$phone = ''; //+ccphone
$username = '';
$password = '';
$day = 01;
$month = 01;
$year = 1970;
$firstName = 'First Name';
/////////////////

$ig = new ExtendedInstagram($debug);
$ig->setUserWithoutPassword($username);

$ig->setCarrier(''); // SET CARRIER

$waterfallId = \InstagramAPI\Signatures::generateUUID();
$startTime = time();

$ig->setBackgroundState(true);
$ig->setDeviceInitState(true);
$ig->setGivenConsent(false);

// First event multi batch

$ig->event->sendInstagramDeviceIds($waterfallId); // 0
$ig->event->sendApkTestingExposure(); // 0
$ig->event->sendStringImpressions(['2131232194' => 1]); // 0
$ig->event->sendStringImpressions(['2131959156' => 1]); // 0
$ig->event->sendPhoneIdUpdate('initial_create'); // 0
$ig->event->sendStringImpressions(['2131232191' => 1, '2131232194' => 1]); // 0
$ig->event->sendStringImpressions(['2131959156' => 1]); // 0

$ig->setBackgroundState(false); // 

$ig->event->sendAppInstallations(); // 1
$ig->event->sendInstagramInstallWithReferrer($waterfallId, 0); // 1
$ig->event->sendInstagramInstallWithReferrer($waterfallId, 1); // 1
$ig->event->sendApkSignatureV2(); // 0
$ig->event->sendFlowSteps('landing', 'step_view_loaded', $waterfallId, $startTime); // 1
$ig->event->sendFlowSteps('landing', 'landing_created', $waterfallId, $startTime); // 1
$ig->event->sendPhoneId($waterfallId, $startTime, 'request'); // 1
$ig->event->sendStringImpressions(['2131232191' => 1, '2131959156' => 1]); // 1
$ig->event->sendStringImpressions(['2131953090' => 1, '2131953091' => 1, '2131954456' => 1, '2131954725' => 1, '2131959052' => 1, '2131959965' => 1, '2131960702' => 1, '2131960706' => 1, '2131966368' => 1]); // 1

$ig->event->sendFlowSteps('landing', 'sim_card_state', $waterfallId, $startTime, ['flow' => 'phone']); // 1
$ig->event->sendEmergencyPushInitialVersion(); // 0

// End of first event multi batch
$ig->event->forceSendBatch();
$ig->setGivenConsent(false); // temp

$ig->event->updateAppState('foreground', 'not_initialized');
try {
    $ig->account->setContactPointPrefill('prefill');
} catch(\Exception) {
    //pass
}


$ig->internal->fetchZeroRatingToken('token_expired', false);
$launcherResponse = $ig->internal->getMobileConfig(true)->getHttpResponse();
$ig->settings->set('public_key', $launcherResponse->getHeaderLine('ig-set-password-encryption-pub-key'));
$ig->settings->set('public_key_id', $launcherResponse->getHeaderLine('ig-set-password-encryption-key-id'));

$ig->internal->logAttribution();

$ig->event->sendNavigation('button', '<init>', 'landing_facebook');

$ig->account->sendGoogleTokenUsers();
$ig->event->sendNavigation('button', 'landing_facebook', 'email_or_phone');

$ig->account->getPrefillCandidates(['phone' => $phone]);

$ig->event->sendFlowSteps('one_page_v2', 'reg_field_interacted', $waterfallId, $startTime, ['flow' => 'phone', 'field_name' => 'phone_field']);
$ig->event->sendFlowSteps('one_page_v2', 'next_button_tapped', $waterfallId, $startTime, ['flow' => 'phone']);
$ig->event->sendFlowSteps('one_page_v2', 'contacts_import_opt_in', $waterfallId, $startTime, ['flow' => 'phone']);
$ig->event->sendFlowSteps('one_page_v2', 'step_view_loaded', $waterfallId, $startTime, ['flow' => 'phone']);

$ig->account->checkPhoneNumber($phone);

$ig->event->sendFlowSteps('phone', 'next_button_tapped', $waterfallId, $startTime, ['flow' => 'phone']);

$tos = $ig->account->requestRegistrationSms($phone, $waterfallId, $username)->getTosVersion();
echo 'SMS Code: ';
$smsCode = trim(fgets(STDIN));

$ig->event->sendNavigation('button', 'email_or_phone', 'phone_confirmation');
$ig->account->validateSignupSmsCode($smsCode, $phone, $waterfallId);

$ig->internal->fetchHeaders();
$ig->event->sendNavigation('button', 'phone_confirmation', 'one_page_registration');

$parts = str_split($firstName, 4);
$queryName = '';
foreach($parts as $part) {
    $queryName .= $part;
    $suggestionResponse = $ig->account->getUsernameSuggestions('', $waterfallId, $queryName);
    usleep(300000);
}
$ig->event->forceSendBatch();
$suggestions = $suggestionResponse->getSuggestionsWithMetadata()->getSuggestions();
foreach($suggestions as $suggestion) {
    if ($suggestion->getPrototype() === 'email') {
        $username = $suggestion->getUsername();
        break;
    }
}

$ig->event->sendNavigation('button', 'one_page_registration', 'add_birthday');

$currentYear = date('Y');
$currentMonth = date('m');
$currentDay = date('d');

usleep(mt_rand(1000000, 3000000));
for ($i = 1; $i <= ($currentYear - $year); $i++) {
    $ig->event->sendDobPick(sprintf('%d-%02d-%02d', $currentYear - $i, $currentMonth, $currentDay));
    usleep(mt_rand(1000, 3000));
}

usleep(mt_rand(1000000, 3000000));
$operand = ($month > $currentMonth) ? 1 : -1;
for ($i = 1; $i <= abs(($currentMonth - $month)); $i++) {
    $ig->event->sendDobPick(sprintf('%d-%02d-%02d', $year, $currentMonth + ($operand * $i), $currentDay));
    usleep(mt_rand(1000, 3000));
}

usleep(mt_rand(1000000, 3000000));
$operand = ($day > $currentDay) ? 1 : -1;
for ($i = 1; $i <= abs(($currentDay - $day)); $i++) {
    $ig->event->sendDobPick(sprintf('%d-%02d-%02d', $year, $month, $currentDay + ($operand * $i)));
    usleep(mt_rand(1000, 3000));
}

$response = $ig->internal->checkAgeEligibility($day, $month, $year);
if ($response->getEligibleToRegister() !== true) {
    exit();
}

$ig->event->sendNavigation('button', 'add_birthday', 'username_sign_up');

$ig->event->sendFlowSteps('username', 'step_view_loaded', $waterfallId, $startTime, 
    [
        'is_facebook_app_installed' => false,
        'messenger_installed'       => false,
        'whatsapp_installed'        => false,
        'fb_lite_installed'         => false,
        'flow'                      => 'phone'
    ]
);


$ig->event->sendFlowSteps('username', 'step_view_loaded', $waterfallId, $startTime, ['flow' => 'phone']);
$ig->event->sendNavigation('button', 'add_birthday', 'username_sign_up');
$ig->event->sendIgNuxFlow();
$ig->event->sendFlowSteps('username', 'ig_dynamic_onboarding_updated_steps_from_serve', $waterfallId, $startTime, ['flow' => 'phone']);
$ig->event->sendFlowSteps('username', 'reg_field_interacted', $waterfallId, $startTime, ['flow' => 'phone', 'field_name' => 'username_field']);
$ig->event->sendFlowSteps('username', 'next_button_tapped', $waterfallId, $startTime, ['flow' => 'phone']);
$ig->event->sendFlowSteps('username', 'register_with_ci_option', $waterfallId, $startTime, ['flow' => 'phone']);

$ig->internal->startNewUserFlow();
$ig->account->checkUsername($username);

$ig->internal->getOnBoardingSteps($waterfallId, 'phone');

$ig->internal->startNewUserFlow();
$ig->event->sendNavigation('button', 'username_sign_up', 'username_sign_up');

$ig->event->forceSendBatch();
usleep(mt_rand(15000000, 3000000));

try {
    $response = $ig->account->createValidated($smsCode, $username, $password, $phone, sprintf('%d-%2d-%2d', $year, $month, $day), $firstName, $waterfallId, $tos);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit();
}

// Sets active status
$ig->isMaybeLoggedIn = true;
$ig->account_id = $response->getCreatedUser()->getPk();
$ig->settings->set('account_id', $ig->account_id);
$ig->settings->set('last_login', time());
//

$ig->event->sendFlowSteps('done', 'register_account_request_submitted', $waterfallId, $startTime, ['flow' => 'phone']);
$ig->event->sendFlowSteps('done', 'register_account_created', $waterfallId, $startTime, ['flow' => 'phone', 'instagram_id' => $response->getUser()->getPk()]);
$ig->event->forceSendBatch();

$ig->people->linkAddressBook([]);
$ig->account->getAccountFamily();

$ig->internal->sendGraph('18293997048434484603993202463', [
    'input' => [
        'log_only'  => true,
        'events'    => [
            'no_advertisement_id'   => false,
            'event_name'            => 'LOGIN',
            'adid'                  => $ig->advertising_id
        ]
    ]], 'ReportAttributionEventsMutation', false);

$ig->internal->newAccountNuxSeen($waterfallId);

$ig->internal->getOnBoardingSteps($waterfallId, 'phone', []);
$ig->account->setContactPointPrefill('auto_confirmation', false);
$ig->internal->sendPrivacyConsentPromptAction('new_users_meta_flow');

$ig->internal->sendGraph('47034443410017494685272535358', [], 'AREffectConsentStateQuery', false);
$ig->internal->sendGraph('18293997048434484603993202463', [
    'input' => [
        'log_only'  => true,
        'events'    => [
            'no_advertisement_id'   => false,
            'event_name'            => 'REGISTRATION',
            'adid'                  => $ig->advertising_id
        ]
    ]], 'ReportAttributionEventsMutation', false);

$ig->internal->getMobileConfig(false);
$ig->internal->getLoomFetchConfig();
$ig->internal->sendPrivacyConsentPromptAction('new_users_meta_flow', true);

$ig->event->sendZeroCarrierSignal();

$ig->internal->getOnBoardingSteps($waterfallId, 'phone', [], false, false);

/*
$feed = $this->timeline->getTimelineFeed(null, [
    'reason' => Constants::REASONS[0],
]);
*/

$ig->event->sendNavigation('button', 'username_sign_up', 'register_flow_add_profile_photo');
$ig->event->sendNavigation('button', 'register_flow_add_profile_photo', 'discover_people_nux');

$suggestedList = json_decode($ig->discover->getAyml('explore_people', null, true)->getMaxId());

$ig->people->getFriendships($suggestedList);

$seenSteps = [ 
    [
        'step_name' => 'CHECK_FOR_PHONE',
        'value'     => 1
    ],
    [
        'step_name' => 'CREATE_PASSWORD',
        'value'     => -1
    ],
    [
        'step_name' => 'IDENTITY_SYNCING',
        'value'     => -1
    ],
    [
        'step_name' => 'FB_CONNECT',
        'value'     => 0
    ],
    [
        'step_name' => 'FB_FOLLOW',
        'value'     => -1
    ],
    [
        'step_name' => 'UNKNOWN',
        'value'     => -1
    ],
    [
        'step_name' => 'IDENTITY_SYNCING_AFTER_NUX_LINKING',
        'value'     => -1
    ],
    [
        'step_name' => 'CONTACT_INVITE',
        'value'     => -1
    ],
    [
        'step_name' => 'ACCOUNT_PRIVACY',
        'value'     => -1
    ],
    [
        'step_name' => 'TAKE_PROFILE_PHOTO',
        'value'     => -1
    ],
    [
        'step_name' => 'ADD_PHONE',
        'value'     => -1
    ],
    [
        'step_name' => 'TURN_ON_ONETAP',
        'value'     => -1
    ],
    [
        'step_name' => 'DISCOVER_PEOPLE',
        'value'     => 1
    ]
];

$ig->internal->sendGraph('45541135218358940417711832437', [
    'input' => [
        'app_scoped_id'     => $this->ig->uuid,
        'appid'             => Constants::FACEBOOK_ANALYTICS_APPLICATION_ID,
        'family_device_id'  => $this->ig->phone_id,
    ]], 'FamilyDeviceIDAppScopedDeviceIDSyncMutation', false);

$ig->internal->getOnBoardingSteps($waterfallId, 'phone', $seenSteps, true);