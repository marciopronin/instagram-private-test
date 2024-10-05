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
$videoFilename = '';
$externalMetadata = [];
// ////////////////////

class ExtendedInstagram extends InstagramAPI\Instagram
{
    public function uploadStoryVideo(
        $targetFeed,
        $videoFilename,
        ?InternalMetadata $internalMetadata = null,
        array $externalMetadata = [],
    ) {
        // Make sure we only allow these particular feeds for this function.
        if ($targetFeed !== Constants::FEED_TIMELINE
            && $targetFeed !== Constants::FEED_STORY
            && $targetFeed !== Constants::FEED_DIRECT_STORY
            && $targetFeed !== Constants::FEED_TV
            && $targetFeed !== Constants::FEED_REELS
        ) {
            throw new InvalidArgumentException(sprintf('Bad target feed "%s".', $targetFeed));
        }

        // Attempt to upload the video.
        $internalMetadata = $this->internal->uploadVideo($targetFeed, $videoFilename, $internalMetadata);

        // Attempt to upload the thumbnail, associated with our video's ID.
        $pdqHashes = $this->internal->uploadVideoThumbnail($targetFeed, $internalMetadata, $externalMetadata);

        return [$internalMetadata, $pdqHashes];
    }
}

$ig = new ExtendedInstagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}
$video = new InstagramAPI\Media\Photo\InstagramVideo($videoFilename, ['targetFeed' => InstagramAPI\Constants::FEED_STORY]);
$uploadData = $ig->uploadStoryVideo(Constants::FEED_STORY, $videoFilename, null, $externalMetadata);
// MANAGED CONFIGURE STARTS HERE
$ig->internal->configureSingleVideo(Constants::FEED_STORY, $uploadData[0], $externalMetadata);
$ig->internal->updateMediaWithPdqHashes($uploadData[0]->getUploadId(), $uploadData[1]);
