<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../../vendor/autoload.php';

// ///// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
// ////////////////////

// ///// DATA ///////
$url = null;
$phone = null;
$name = null;
$biography = null;
$email = ''; // REQUIRED.
$newUsername = null;

$profilePhoto = ''; // If you don't want to change it, leave it like this.
// //////////////////

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
    sleep(mt_rand(1, 2));
    $ig->event->sendNavigation('main_profile', 'feed_timeline', 'self_profile');
    $ig->event->sendNavigation('button', 'self_profile', 'self_profile', null, null, ['class_selector' => 'ProfileMediaTabFragment']);
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
    sleep(mt_rand(1, 2));
    $ig->event->sendProfileAction('edit_profile', $ig->account_id, $navstack);
    $ig->event->sendNavigation('button', 'self_profile', 'edit_profile');

    $ig->account->setContactPointPrefill('prefill');
    sleep(1);
    $ig->event->sendNavigation('button', 'edit_profile', 'personal_information');
    $ig->account->getCurrentUser();

    $ig->event->sendNavigation('button', 'personal_information', 'change_email');
    sleep(mt_rand(3, 5));
    $emailCheck = $ig->account->sendConfirmEmail($email);
    if ($emailCheck->getTitle() === 'Email Not Confirmed') {
        echo 'Email being used in other account! Exit!';
        exit();
    }
    $ig->event->sendNavigation('back', 'change_email', 'personal_information');
    $userData = $ig->account->getCurrentUser();
    $emailLink = trim(fgets(STDIN));
    $ig->setNavChain('');
    $ig->event->sendNavigation('back', 'self_profile', 'cold_start');
    $ig->account->confirmEmail($emailLink);

    sleep(mt_rand(1, 2));
    if ($biography !== null) {
        sleep(mt_rand(3, 5));
        $ig->event->sendProfileAction('edit_profile', $ig->account_id, $navstack);
        $ig->event->sendNavigation('button', 'self_profile', 'edit_profile');
        $ig->event->sendNavigation('button', 'edit_profile', 'profile_edit_bio');

        $ig->event->sendPhoneId($waterfallId, $startTime, 'request');
        $ig->event->sendPhoneId($waterfallId, $startTime, 'response');
        $ig->account->setBiography($biography);
        $ig->event->sendNavigation('back', 'profile_edit_bio', 'edit_profile');
    }

    $ig->event->sendPhoneId($waterfallId, $startTime, 'request');
    $ig->event->sendPhoneId($waterfallId, $startTime, 'response');

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
    if ($phone === null) {
        if ($userData->getUser()->getPhoneNumber() !== null) {
            $phone = $userData->getUser()->getPhoneNumber();
        }
    }
    if ($email === null) {
        if ($userData->getUser()->getEmail() !== null) {
            $email = $userData->getUser()->getEmail();
        }
    }
    if ($name === null) {
        if ($userData->getUser()->getFullName() !== null) {
            $name = $userData->getUser()->getFullName();
        }
    }
    if ($name === null) {
        if ($userData->getUser()->getFullName() !== null) {
            $name = $userData->getUser()->getFullName();
        }
    }
    if ($biography === null) {
        if ($userData->getUser()->getBiography() !== null) {
            $biography = $userData->getUser()->getBiography();
        }
    }
    $ig->account->editProfile($url, $phone, $name, $biography, $email, $newUsername);

    if ($profilePhoto !== '') {
        $ig->event->sendNavigation('button', 'edit_profile', 'edit_profile');
        $ig->event->sendNavigation('new_profile_photo', 'edit_profile', 'edit_profile');
        $ig->event->sendNavigation('button', 'edit_profile', 'tabbed_gallery_camera');
        $ig->event->sendNavigation('button', 'tabbed_gallery_camera', 'tabbed_gallery_camera');

        $ig->event->sendNavigation('button', 'tabbed_gallery_camera', 'photo_filter');

        $ig->account->changeProfilePicture($profilePhoto);

        $ig->event->sendNavigation('button', 'photo_filter', 'edit_profile');
    }

    $ig->event->sendBadgingEvent('impression', 'photos_of_you', 0, 'profile_menu');
    $ig->event->sendNavigation('button', 'edit_profile', 'self_profile');

    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
