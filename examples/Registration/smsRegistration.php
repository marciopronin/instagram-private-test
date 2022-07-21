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
$phone = '+34123456789';
$username = 'InstaAPITest';
$password = 'InstaAPITest';
$day = 01;
$month = 01;
$year = 1970;
$firstName = 'First Name';
/////////////////

$ig = new ExtendedInstagram($debug);
$ig->setUserWithoutPassword($username);

$waterfallId = \InstagramAPI\Signatures::generateUUID();
$startTime = time();

$ig->event->updateAppState('foreground', 'not_initialized');
$ig->event->sendFlowSteps('landing', 'sim_card_state', $waterfallId, $startTime, ['flow' => 'phone']);

try {
    $ig->account->setContactPointPrefill('prefill');
catch(\Exception) {
    //pass
}


$ig->internal->fetchZeroRatingToken('token_expired', false);
$launcherResponse = $ig->internal->getMobileConfig(true)->getHttpResponse();
$ig->settings->set('public_key', $launcherResponse->getHeaderLine('ig-set-password-encryption-pub-key'));
$ig->settings->set('public_key_id', $launcherResponse->getHeaderLine('ig-set-password-encryption-key-id'));

$ig->account->sendGoogleTokenUsers();
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

$ig->account->validateSignupSmsCode($smsCode, $phone, $waterfallId);

$ig->internal->fetchHeaders();

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

$ig->account->getAccountFamily();
$ig->internal->getMobileConfig(false);
$ig->event->sendZeroCarrierSignal();

$ig->internal->getOnBoardingSteps($waterfallId, 'phone', [], false, false);

$feed = $this->timeline->getTimelineFeed(null, [
    'reason' => Constants::REASONS[0],
]);

$suggestedList = json_decode($ig->discover->getAyml('explore_people', null, true)->getMaxId());

$ig->people->getFriendships($suggestedList);

$userToFollow = array_rand($suggestedList);
$ig->event->sendFollowButtonTapped($userToFollow, 'discover_people_nux',
    [
        [
            'module'        => 'username_sign_up',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'username_sign_up',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'register_flow_add_profile_photo',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'username_sign_up',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'add_birthday',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'one_page_registration',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'phone_confirmation',
            'click_point'   => 'button',
        ],
    ]
);
$ig->people->follow($userToFollow);
$ig->event->sendProfileAction('follow', $userToFollow,
    [
        [
            'module'        => 'username_sign_up',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'username_sign_up',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'register_flow_add_profile_photo',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'username_sign_up',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'add_birthday',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'one_page_registration',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'phone_confirmation',
            'click_point'   => 'button',
        ],
    ]
);

$rankToken = \InstagramAPI\Signatures::generateUUID();
$ig->event->sendSearchFollowButtonClicked($userToFollow, 'discover_people_nux', $rankToken);

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
$ig->internal->getOnBoardingSteps($waterfallId, 'phone', $seenSteps, true);

$ig->sendLoginFlow();
