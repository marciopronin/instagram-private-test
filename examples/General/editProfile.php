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

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $ig->event->sendNavigation('main_profile', 'feed_timeline', 'self_profile');
    $ig->timeline->getSelfUserFeed();
    $ig->highlight->getSelfUserFeed();
    $ig->people->getInfoById($ig->account_id);
    $ig->discover->profileSuBadge();
    $ig->story->getArchiveBadgeCount();

    $navstack = [
        [
            'module'        => 'feed_timeline',
            'click_point'   => 'main_profile',
        ],
        [
            'module'        => 'login',
            'click_point'   => 'cold start',
        ],
    ];
    $ig->event->sendProfileAction('edit_profile', $ig->account_id, $navstack);

    $ig->event->sendNavigation('button', 'self_profile', 'edit_profile');
    $ig->account->getCurrentUser();
    $ig->account->setContactPointPrefill('prefill');

    if ($profilePhoto !== '') {
        $ig->account->changeProfilePicture($profilePhoto);
    }

    $ig->event->sendNavigation('button', 'self_profile', 'profile_edit_bio');
    $ig->account->setBiography($biography);
    $ig->event->sendNavigation('back', 'profile_edit_bio', 'self_profile');

    $ig->account->editProfile($url, $phone, $name, $biography, $email, $newUsername);

    $navstack = [
        /* If updating username add the commented navs
        [
            'module'        => 'edit_profile',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'profile_edit_username',
            'click_point'   => 'back',
        ],
        [
            'module'        => 'profile_edit_username',
            'click_point'   => 'button',
        ],
        */
        [
            'module'        => 'edit_profile',
            'click_point'   => 'button',
        ],
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
            'click_point'   => 'cold start',
        ],
    ];

    $ig->event->sendProfileAction('edit_profile', $ig->account_id, $navstack);

    $waterfallId = \InstagramAPI\Signatures::generateUUID();
    $startTime = round(microtime(true) * 1000);
    $ig->event->sendPhoneId($waterfallId, $startTime, 'request');
    $ig->event->sendPhoneId($waterfallId, $startTime, 'response');

    $ig->event->sendNavigation('button', 'edit_profile', 'profile_edit_bio');
    $ig->event->sendPhoneId($waterfallId, $startTime, 'request');
    $ig->event->sendNavigation('back', 'profile_edit_bio', 'edit_profile');
    $ig->event->sendPhoneId($waterfallId, $startTime, 'response');

    $ig->event->sendBadgingEvent('impression', 'photos_of_you', 0, 'profile_menu');
    $ig->event->sendNavigation('back', 'edit_profile', 'self_profile');

    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
