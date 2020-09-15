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

// NOTE: This code will make a story poll sticker with the two answers provided,
// but YOU need to manually draw the question on top of your image yourself
// before uploading if you want the question to be visible.

// Now create the metadata array:
$metadata = [
    'story_sliders' => [
        // Note that you can only do one story poll in this array.
        [
            'question'              => 'Is this API great?', // Story poll question. You need to manually to draw it on top of your image.
            'viewer_vote'           => 0, // Don't change this value.
            'viewer_can_vote'       => false, // Don't change this value.
            'slider_vote_count'     => 0, // Don't change this value.
            'slider_vote_average'   => 0, // Don't change this value.
            'background_color'      => '#ffffff',
            'text_color'            => '#000000',
            'emoji'                 => '😍',
            'x'                     => 0.5, // Range: 0.0 - 1.0. Note that x = 0.5 and y = 0.5 is center of screen.
            'y'                     => 0.5004223, // Also note that X/Y is setting the position of the CENTER of the clickable area
            'width'                 => 0.7777778, // Clickable area size, as percentage of image size: 0.0 - 1.0
            'height'                => 0.22212838, // ...
            'rotation'              => 0.0,
            'is_sticker'            => true, // Don't change this value.
        ],
    ],
];

$ig->event->sendNavigation('your_story_dialog_option', 'feed_timeline', 'quick_capture_fragment');

$sessionId = \InstagramAPI\Signatures::generateUUID();
$ig->event->sendIGStartCameraSession($sessionId);

$loggerId = \InstagramAPI\Signatures::generateUUID();
$productSessionId = \InstagramAPI\Signatures::generateUUID();
$eventTime = mt_rand(200, 300) * 1000;
$ig->event->sendCameraWaterfall('instagram_stories', 'add_outputs', $loggerId, $productSessionId, 'instagram_stories', $eventTime);

$ig->event->sendCameraWaterfall('instagram_stories', 'set_input', $loggerId, $productSessionId, 'instagram_stories', $eventTime + 1);

$waterfallId = \InstagramAPI\Signatures::generateUUID();
$startTime = round(microtime(true) * 1000);
$ig->event->sendNametagSessionStart('ig_nametag_session_start', $waterfallId, $startTime, $startTime, 'story_camera');

$cameraShareId = \InstagramAPI\Signatures::generateUUID();
$ig->event->sendIgCameraShareMedia($cameraShareId, 1);

$ig->event->sendNavigation('button', 'reel_composer_preview', 'reel_composer_camera');

$ig->event->sendNametagSessionStart('ig_nametag_session_end', $waterfallId, null, null, null);

try {
    // This example will upload the image via our automatic photo processing
    // class. It will ensure that the story file matches the ~9:16 (portrait)
    // aspect ratio needed by Instagram stories. You have nothing to worry
    // about, since the class uses temporary files if the input needs
    // processing, and it never overwrites your original file.
    //
    // Also note that it has lots of options, so read its class documentation!
    $photo = new \InstagramAPI\Media\Photo\InstagramPhoto($photoFilename, ['targetFeed' => \InstagramAPI\Constants::FEED_STORY]);
    $ig->story->uploadPhoto($photo->getFile(), $metadata);

    $ig->event->sendIgCameraEndPostCaptureSession($sessionId);
    $ig->event->sendIgCameraEndSession($sessionId);

    $ig->event->sendNavigation('story_posted_from_camera', 'quick_capture_fragment', 'feed_timeline');

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
