<?php

require __DIR__.'/../../vendor/autoload.php';

/////////////////
$debug = true;
/////////////////

/////////////////
$username = 'username_handler'; // DO NOT CHANGE THIS! USERNAME WILL BE TAKEN AUTOMATICALLY FROM SUGGESTIONS.
$password = 'InstaAPITest';
$day = 01;
$month = 01;
$year = 1970;
$firstName = 'First Name';
$email = 'InstaAPI@Test.com';
/////////////////

$ig = new \InstagramAPI\Instagram($debug);
$ig->setUserWithoutPassword($username);

$mobileconfigResponse = $ig->internal->getMobileConfig(true)->getHttpResponse();
$ig->settings->set('public_key', $mobileconfigResponse->getHeaderLine('ig-set-password-encryption-pub-key'));
$ig->settings->set('public_key_id', $mobileconfigResponse->getHeaderLine('ig-set-password-encryption-key-id'));

$startTime = round(microtime(true) * 1000);
$waterfallId = \InstagramAPI\Signatures::generateUUID();

$ig->event->sendNavigation('warm_start', 'app_background_detector', 'email_or_phone');

$ig->event->sendFlowSteps('phone', 'switch_to_email', $waterfallId, $startTime);
$ig->event->sendFlowSteps('email', 'step_view_loaded', $waterfallId, $startTime, 
    [
        'is_facebook_app_installed' => false,
        'messenger_installed'       => false,
        'whatsapp_installed'        => false,
        'fb_lite_installed'         => false,
        'flow'                      => 'email'
    ]
);
$ig->event->sendFlowSteps('email', 'attempt_read_email_for_prefill', $waterfallId, $startTime, ['source' => 'android_account_manager']);
$ig->event->sendFlowSteps('email', 'attempt_read_email_for_prefill', $waterfallId, $startTime, ['source' => 'fb_first_party']);
$ig->event->sendFlowSteps('email', 'attempt_read_email_for_prefill', $waterfallId, $startTime, ['source' => 'uig_via_phone_id']);
$ig->event->sendFlowSteps('email', 'sim_card_state', $waterfallId, $startTime, ['flow' => 'email']);
$ig->event->sendFlowSteps('email', 'email_field_prefilled', $waterfallId, $startTime);
$ig->event->sendFlowSteps('email', 'reg_field_interacted', $waterfallId, $startTime, 
    [
        'flow'          => 'email',
        'field_name'    => 'email_field'
    ]
);

usleep(mt_rand(1500000, 3000000));
try {
    $response = $ig->account->checkEmail($email, $waterfallId, $username);
} catch(Exception) {
    echo 'Throttle. Retry later';
    exit();
}

$ig->event->sendFlowSteps('email', 'next_button_tapped', $waterfallId, $startTime, ['flow' => 'email']);

$ig->account->getSignupConfig($username);

if ($response->getValid() && $response->getAvailable()) {
    $ig->account->sendEmailVerificationCode($email, $waterfallId);

    $ig->event->sendFlowSteps('sign_up_email_code_confirmation', 'step_view_loaded', $waterfallId, $startTime, 
        [
            'is_facebook_app_installed' => false,
            'messenger_installed'       => false,
            'whatsapp_installed'        => false,
            'fb_lite_installed'         => false,
            'flow'                      => 'email'
        ]
    );

    $code = trim(fgets(STDIN));
    $ig->event->sendNavigation('button', 'email_or_phone', 'email_verify');
    $signupCode = $ig->account->checkConfirmationCode($code, $email, $waterfallId)->getSignupCode();

    $ig->event->sendNavigation('button', 'email_verify', 'one_page_registration');

    $parts = str_split($firstName, 4);
    $queryName = '';
    foreach($parts as $part) {
        $queryName .= $part;
        $suggestionResponse = $ig->account->getUsernameSuggestions($email, $waterfallId, $queryName);
        usleep(300000);
    }
    $suggestions = $suggestionResponse->getSuggestionsWithMetadata()->getSuggestions();
    foreach($suggestions as $suggestion) {
        if ($suggestion->getPrototype() === 'email') {
            $username = $suggestion->getUsername();
            break;
        }
    }

    $ig->event->sendFlowSteps('one_page_v2', 'reg_field_interacted', $waterfallId, $startTime, 
        [
            'flow'          => 'email', 
            'field_name'    => 'fullname_field'
        ]
    );
    $ig->event->sendFlowSteps('one_page_v2', 'register_password_focused', $waterfallId, $startTime, ['flow' => 'email']);
    $ig->event->sendFlowSteps('one_page_v2', 'reg_field_interacted', $waterfallId, $startTime, 
        [
            'flow'          => 'email', 
            'field_name'    => 'password_field'
        ]
    );
    $ig->event->sendFlowSteps('one_page_v2', 'register_password_focused', $waterfallId, $startTime, ['flow' => 'email']);
    $ig->event->sendFlowSteps('one_page_v2', 'next_button_tapped', $waterfallId, $startTime, ['flow' => 'email']);
    $ig->event->sendFlowSteps('one_page_v2', 'contacts_import_opt_in', $waterfallId, $startTime, ['flow' => 'email']);
    $ig->event->sendFlowSteps('one_page_v2', 'valid_password', $waterfallId, $startTime, ['flow' => 'email']);
    $ig->event->sendFlowSteps('one_page_v2', 'step_view_loaded', $waterfallId, $startTime,
        [
            'is_facebook_app_installed' => false,
            'messenger_installed'       => false,
            'whatsapp_installed'        => false,
            'fb_lite_installed'         => false,
            'flow'                      => 'email'
        ]
    );

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
            'flow'                      => 'email'
        ]
    );
    $ig->event->sendNavigation('button', 'add_birthday', 'username_sign_up');
    $ig->event->sendIgNuxFlow();
    $ig->event->sendFlowSteps('username', 'ig_dynamic_onboarding_updated_steps_from_serve', $waterfallId, $startTime, ['flow' => 'email']);
    $ig->event->sendFlowSteps('username', 'reg_field_interacted', $waterfallId, $startTime, 
        [
            'flow'          => 'email',
            'field_name'    => 'username_field'
        ]
    );
    $ig->event->sendFlowSteps('username', 'next_button_tapped', $waterfallId, $startTime, ['flow' => 'email']);
    $ig->event->sendFlowSteps('username', 'register_with_ci_option', $waterfallId, $startTime, ['flow' => 'email']);

    $ig->internal->startNewUserFlow();
    $ig->internal->getOnBoardingSteps($waterfallId);
    $ig->event->forceSendBatch();

    usleep(mt_rand(2000000, 5000000));
    try {
        $response = $ig->account->create($username, $password, $signupCode, $email, sprintf('%d-%2d-%2d', $year, $month, $day), $firstName, $waterfallId);
    } catch (\Exception $e) {
        echo 'Something went wrong: '.$e->getMessage()."\n";
        exit();
    }
    $ig->event->sendFlowSteps('done', 'register_account_request_submitted', $waterfallId, $startTime, ['flow' => 'email']);
    $ig->event->sendFlowSteps('done', 'register_account_created', $waterfallId, $startTime, 
        [
            'flow'          => 'email',
            'instagram_id'  => $response->getUser()->getPk()
        ]
    );
    $ig->event->forceSendBatch();
} else {
    echo 'Email not available';
}
