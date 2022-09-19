<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../../vendor/autoload.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////

/////// DATA ///////
$url = null; // Leave it like this if you want to clear the value.
$phone = null; // Leave it like this if you want to clear the value.
$name = null; // Leave it like this if you want to clear the value.
$biography = null; // Leave it like this if you want to clear the value.
$email = ''; // REQUIRED.
$newUsername = null;

$profilePhoto = ''; // If you don't want to change it, leave it like this.
////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
$waterfallId = \InstagramAPI\Signatures::generateUUID();
$startTime = round(microtime(true) * 1000);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $ig->event->sendNavigation('main_profile', 'feed_timeline', 'self_profile');
    $ig->event->sendNavigation('button', 'feed_timeline', 'self_profile', null, null, ['class_selector' => 'ProfileMediaTabFragment']);
    $ig->timeline->getSelfUserFeed();
    $ig->highlight->getSelfUserFeed();
    $ig->people->getInfoById($ig->account_id, 'self_profile', 'self_profile');
    $ig->story->getArchiveBadgeCount();

    $navstack = [
        [
            'module'        => 'feed_timeline',
            'click_point'   => 'main_profile',
        ],
        [
            'module'        => 'login',
            'click_point'   => 'cold_start',
        ],
    ];
    $ig->event->sendProfileAction('edit_profile', $ig->account_id, $navstack);

    $ig->account->getCurrentUser();
    $ig->account->setContactPointPrefill('prefill');

    $ig->event->sendNavigation('button', 'self_profile', 'edit_profile');

    $ig->event->sendNavigation('button', 'edit_profile', 'personal_information');
    $ig->event->sendNavigation('button', 'personal_information', 'change_email');

    $ig->account->sendConfirmEmail($email);
    $securityCode = trim(fgets(STDIN));
    $ig->account->verifyEmailCode($email, $securityCode);

    $ig->event->sendNavigation('back', 'change_email', 'personal_information');
    $ig->event->sendNavigation('back', 'personal_information', 'edit_profile');

    $ig->event->sendNavigation('button', 'edit_profile', 'profile_edit_bio');

    $ig->event->sendPhoneId($waterfallId, $startTime, 'request');
    $ig->event->sendPhoneId($waterfallId, $startTime, 'response');

    $ig->account->setBiography($biography);
    $ig->event->sendNavigation('back', 'profile_edit_bio', 'edit_profile');

    $ig->event->sendPhoneId($waterfallId, $startTime, 'request');
    $ig->event->sendPhoneId($waterfallId, $startTime, 'response');

    $ig->account->editProfile($url, $phone, $name, $biography, $email, $newUsername);

    $navstack = [
        [
            'module'        => 'self_profile',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'feed_timeline',
            'click_point'   => 'main_profile',
        ],
        [
            'module'        => 'login',
            'click_point'   => 'cold_start',
        ],
    ];

    $ig->event->sendProfileAction('edit_profile', $ig->account_id, $navstack);

    if ($profilePhoto !== '') {
        $ig->event->sendNavigation('button', 'edit_profile', 'edit_profile', null, null, ['class_selector' => 'ProfileMediaTabFragment']);
        $ig->event->sendNavigation('new_profile_photo', 'edit_profile', 'edit_profile']);
        $ig->event->sendNavigation('button', 'edit_profile', 'tabbed_gallery_camera']);
        $ig->event->sendNavigation('button', 'tabbed_gallery_camera', 'tabbed_gallery_camera']);

        $ig->event->sendNavigation('button', 'tabbed_gallery_camera', 'photo_filter']);

        $ig->account->changeProfilePicture($profilePhoto);

        $ig->event->sendNavigation('button', 'photo_filter', 'edit_profile']);
    }

    $ig->event->sendBadgingEvent('impression', 'photos_of_you', 0, 'profile_menu');
    $ig->event->sendNavigation('back', 'edit_profile', 'self_profile');

    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
