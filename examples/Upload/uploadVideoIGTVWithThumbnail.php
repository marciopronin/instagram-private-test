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
$videoFilename = '';
$coverPhoto = '';
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $cover = new \InstagramAPI\Media\Photo\InstagramPhoto($coverPhoto, ['targetFeed' => \InstagramAPI\Constants::FEED_TV]);
    $metadata = [
        'cover_photo'   => $cover->getFile(), // Video thumbnail
        'title'         => 'Test title',
        'share_to_feed' => 0,
        'caption'       => 'Caption text',
    ];

    $video = new \InstagramAPI\Media\Video\InstagramVideo($videoFilename, ['targetFeed' => \InstagramAPI\Constants::FEED_TV]);
    $ig->tv->uploadVideo($video->getFile(), $metadata);
} catch (\Exception $e) {
    if ($e instanceof InstagramAPI\Exception\LoginRequiredException) {
        echo 'Password was changed or cookie expired. Please login again.';
    } else {
        echo 'Something went wrong: '.$e->getMessage()."\n";
    }
}
