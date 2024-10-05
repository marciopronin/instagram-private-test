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

// ///// MEDIA ////////
$photoFilename = '';
// ////////////////////

// ///// TAG NAME ////////
$tagName = ''; // Without #
// //////////////////////////

$ig = new InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

// Now create the metadata array:
$metadata = [
    'hashtags' => [
        [
            'tag_name'         => $tagName,
            'x'                => 0.5,
            'y'                => 0.5,
            'width'            => 1.0,
            'height'           => 1.0,
            'rotation'         => 0.0,
            'is_sticker'       => true,
            'tap_state'        => 0,
            'tap_state_str_id' => 'hashtag_sticker_hero', // hashtag_sticker_subtle, hashtag_sticker_rainbow, hashtag_sticker_gradient
            'type'             => 'hashtag',
        ],
    ],
];

try {
    // This example will upload the image via our automatic photo processing
    // class. It will ensure that the story file matches the ~9:16 (portrait)
    // aspect ratio needed by Instagram stories. You have nothing to worry
    // about, since the class uses temporary files if the input needs
    // processing, and it never overwrites your original file.
    //
    // Also note that it has lots of options, so read its class documentation!
    $photo = new InstagramAPI\Media\Photo\InstagramPhoto($photoFilename, ['targetFeed' => InstagramAPI\Constants::FEED_STORY]);
    $ig->story->uploadPhoto($photo->getFile(), $metadata);

    // NOTE: Providing metadata for story uploads is OPTIONAL. If you just want
    // to upload it without any tags/location/caption, simply do the following:
    // $ig->story->uploadPhoto($photo->getFile());
} catch (Exception $e) {
    if ($e instanceof InstagramAPI\Exception\LoginRequiredException) {
        echo 'Password was changed or cookie expired. Please login again.';
    } else {
        echo 'Something went wrong: '.$e->getMessage()."\n";
    }
}
