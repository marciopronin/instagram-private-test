<?php

require __DIR__.'/../../vendor/autoload.php';

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

$ig = new \InstagramAPI\Instagram($debug);
$ig->setUserWithoutPassword($username);

$launcherResponse = $ig->internal->sendLauncherSync(true)->getHttpResponse();
$ig->settings->set('public_key', $launcherResponse->getHeaderLine('ig-set-password-encryption-pub-key'));
$ig->settings->set('public_key_id', $launcherResponse->getHeaderLine('ig-set-password-encryption-key-id'));

$waterfallId = \InstagramAPI\Signatures::generateUUID();

$ig->event->sendFlowSteps('one_page_v2', 'reg_field_interacted', $waterfallId, $startTime, ['flow' => 'phone', 'field_name' => 'phone_field']);
$ig->event->sendFlowSteps('one_page_v2', 'next_button_tapped', $waterfallId, $startTime, ['flow' => 'phone']);
$ig->event->sendFlowSteps('one_page_v2', 'contacts_import_opt_in', $waterfallId, $startTime, ['flow' => 'phone']);
$ig->event->sendFlowSteps('one_page_v2', 'step_view_loaded', $waterfallId, $startTime, ['flow' => 'phone']);

$ig->account->checkPhoneNumber($phone);

$tos = $ig->account->requestRegistrationSms($phone, $waterfallId, $username)->getTosVersion();
$ig->account->validateSignupSmsCode($smsCode, $phone, $waterfallId);

$ig->event->sendNavigation('button', 'one_page_registration', 'add_birthday');

$currentYear = date('Y');
$currentMonth = date('m');
$currentDay = date('d');

for ($i = 1; $i <= ($currentYear - $year); $i++) {
    $ig->event->sendDobPick(sprintf('%d-%02d-%02d', $currentYear - $i, $currentMonth, $currentDay));
}

$operand = ($month > $currentMonth) ? 1 : -1;
for ($i = 1; $i <= abs(($currentMonth - $month)); $i++) {
    $ig->event->sendDobPick(sprintf('%d-%02d-%02d', $year, $currentMonth + ($operand * $i), $currentDay));
}

$operand = ($day > $currentDay) ? 1 : -1;
for ($i = 1; $i <= abs(($currentDay - $day)); $i++) {
    $ig->event->sendDobPick(sprintf('%d-%02d-%02d', $year, $month, $currentDay + ($operand * $i)));
}

$response = $ig->internal->checkAgeEligibility($day, $month, $year);
if ($response->getEligibleToRegister() !== true) {
    exit();
}

$ig->event->sendFlowSteps('username', 'step_view_loaded', $waterfallId, $startTime, ['flow' => 'phone']);
$ig->event->sendNavigation('button', 'add_birthday', 'username_sign_up');
$ig->event->sendIgNuxFlow();
$ig->event->sendFlowSteps('username', 'ig_dynamic_onboarding_updated_steps_from_serve', $waterfallId, $startTime, ['flow' => 'phone']);
$ig->event->sendFlowSteps('username', 'reg_field_interacted', $waterfallId, $startTime, ['flow' => 'phone', 'field_name' => 'username_field']);
$ig->event->sendFlowSteps('username', 'next_button_tapped', $waterfallId, $startTime, ['flow' => 'phone']);
$ig->event->sendFlowSteps('username', 'register_with_ci_option', $waterfallId, $startTime, ['flow' => 'phone']);

$ig->internal->startNewUserFlow();
$ig->internal->getOnBoardingSteps($waterfallId);
$ig->event->forceSendBatch();

try {
    $response = $ig->account->createValidated($smsCode, $username, $password, $phone, sprintf('%d-%2d-%2d', $year, $month, $day), $firstName, $waterfallId, $tos);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit();
}
$ig->event->sendFlowSteps('done', 'register_account_request_submitted', $waterfallId, $startTime, ['flow' => 'phone']);
$ig->event->sendFlowSteps('done', 'register_account_created', $waterfallId, $startTime, ['flow' => 'phone', 'instagram_id' => $response->getUser()->getPk()]);
$ig->event->forceSendBatch();
