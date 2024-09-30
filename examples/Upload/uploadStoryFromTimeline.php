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

$ig = new InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

$metadata = [];

try {
    $ig->event->sendNavigation('your_story_dialog_option', 'feed_timeline', 'stories_precapture_camera');
    // OPTIONAL FOR EMULATION PURPOSES ONLY
    /*
    $ig->live->getFundraiserInfo();
    $ig->creative->getFaceModels();
    $ig->creative->getSegmentationModels();
    $ig->creative->getCameraModels();
    $ig->creative->getStickerAssets();
    */

    $ig->event->sendNavigation('button', 'stories_precapture_camera', 'reel_composer_preview');
    $ig->event->sendNavigation('button', 'reel_composer_preview', 'private_stories_share_sheet');

    $photo = new InstagramAPI\Media\Photo\InstagramPhoto($photoFilename, ['targetFeed' => InstagramAPI\Constants::FEED_STORY]);
    $ig->story->uploadPhoto($photo->getFile(), $metadata);

    $ig->event->sendNavigation('button', 'private_stories_share_sheet', 'direct_story_audience_picker');
    $ig->event->sendNavigation('story_posted_from_camera', 'direct_story_audience_picker', 'feed_timeline');

    $ig->event->forceSendBatch();
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
