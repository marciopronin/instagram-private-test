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

/////// MEDIA ////////
$photoFilename = '';
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

$ig->event->sendNavigation('main_profile', 'feed_timeline', 'self_profile');

$ig->highlight->getUserFeed($ig->account_id);
$ig->people->getInfoById($ig->account_id, 'self_profile');
$ig->story->getUserStoryFeed($ig->account_id);
$userFeed = $ig->timeline->getUserFeed($ig->account_id);
$items = $userFeed->getItems();

$c = 0;
foreach ($items as $item) {
    if ($c === 5) {
        break;
    }
    $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $item, 'profile');
    $c++;
}

// You don't have to provide hashtags or locations for your story. It is
// optional! But we will show you how to do both...

// NOTE: This code will make the hashtag area 'clickable', but YOU need to
// manually draw the hashtag or a sticker-image on top of your image yourself
// before uploading, if you want the tag to actually be visible on-screen!

// NOTE: The same thing happens when a location sticker is added. And the
// "location_sticker" WILL ONLY work if you also add the "location" as shown
// below.

// NOTE: And "caption" will NOT be visible either! Like all the other story
// metadata described above, YOU must manually draw the caption on your image.

// If we want to attach a location, we must find a valid Location object first:
try {
    $location = $ig->location->search('40.7439862', '-73.998511')->getVenues()[0];
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}

// Now create the metadata array:
$metadata = [
    'location_sticker' => [
        'width'             => 0.5708333,
        'height'            => 0.07700573,
        'x'                 => 0.5,
        'y'                 => 0.5,
        'rotation'          => 0.0,
        'is_sticker'        => true,
        'type'              => 'location',
        'tap_state'         => 0,
        'tap_state_str_id'  => 'location_sticker_vibrant',
        'location_id'       => $location->getExternalId(),
    ],
    'location' => $location,

    // (optional) You can use story links ONLY if you have a business account with >= 10k followers.
    // 'link' => 'https://github.com/mgp25/Instagram-API',
];

try {
    $ig->live->getFundraiserInfo();
    $ig->creative->getFaceModels();
    $ig->creative->getSegmentationModels();
    $ig->creative->getCameraModels();
    $ig->creative->getStickerAssets();
    // This example will upload the image via our automatic photo processing
    // class. It will ensure that the story file matches the ~9:16 (portrait)
    // aspect ratio needed by Instagram stories. You have nothing to worry
    // about, since the class uses temporary files if the input needs
    // processing, and it never overwrites your original file.
    //
    // Also note that it has lots of options, so read its class documentation!
    $ig->event->sendNavigation('button', 'reel_composer_preview', 'reel_composer_camera');
    $ig->event->sendNavigation('button', 'reel_composer_preview', 'self_profile');
    $photo = new \InstagramAPI\Media\Photo\InstagramPhoto($photoFilename, ['targetFeed' => \InstagramAPI\Constants::FEED_STORY]);
    $ig->story->uploadPhoto($photo->getFile(), $metadata);

    // NOTE: Providing metadata for story uploads is OPTIONAL. If you just want
    // to upload it without any tags/location/caption, simply do the following:
    // $ig->story->uploadPhoto($photo->getFile());
} catch (\Exception $e) {
    if ($e instanceof InstagramAPI\Exception\LoginRequiredException) {
        echo 'Password was changed or cookie expired. Please login again.';
    } else {
        echo 'Something went wrong: '.$e->getMessage()."\n";
    }
}