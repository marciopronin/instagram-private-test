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
// ////////////////////

// ///// QUERY ////////
$query = '';
// ////////////////////

$ig = new InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

/* IMPORTANT!!!
*  This examples prepares the story to be uploaded with a story sticker
*  BUT THE LYRICS AND THE AUDIO TRACK MUST BE COMPOSED BY THE USER AS A
*  VIDEO.
*
*  The audio track can be downloaded from the music track item, 'progressive_download_url' property.
*  Lyrics and its offsets are obtained using `getLyrics()` function from Music class.
*/

$browseSessionId = InstagramAPI\Signatures::generateUUID();
$searchSessionId = InstagramAPI\Signatures::generateUUID();
$musicItems = $ig->music->search($query, $browseSessionId, $searchSessionId)->getItems();

// Music sticker
$musicSticker = [
    'x'                                         => 0,
    'y'                                         => 0,
    'z'                                         => 0,
    'width'                                     => 0,
    'height'                                    => 0,
    'rotation'                                  => 0,
    'type'                                      => 'music',
    'audio_asset_start_time_in_ms'              => $musicItems[0]->getTrack()->getHighlightStartTimesInMs()[0] - 500,
    'audio_asset_suggested_start_time_in_ms'    => $musicItems[0]->getTrack()->getHighlightStartTimesInMs()[0],
    'derived_content_start_time_in_ms'          => 0,
    'overlap_duration_in_ms'                    => 15000,
    'browse_session_id'                         => $browseSessionId,
    'alacorn_session_id'                        => '1SpiuoCTXAcxBDSz58704',
    'music_product'                             => 'post_capture_sticker',
    'audio_asset_id'                            => $musicItems[0]->getTrack()->getId(),
    'progressive_download_url'                  => $musicItems[0]->getTrack()->getProgressiveDownloadUrl(),
    'duration_in_ms'                            => $musicItems[0]->getTrack()->getDurationInMs(),
    'dash_manifest'                             => $musicItems[0]->getTrack()->getDashManifest(),
    'highlight_start_times_in_ms'               => $musicItems[0]->getTrack()->getHighlightStartTimesInMs(),
    'title'                                     => $musicItems[0]->getTrack()->getTitle(),
    'display_artist'                            => $musicItems[0]->getTrack()->getDisplayArtist(),
    'cover_artwork_uri'                         => $musicItems[0]->getTrack()->getCoverArtworkUri(),
    'cover_artwork_thumbnail_uri'               => $musicItems[0]->getTrack()->getCoverArtworkThumbnailUri(),
    'is_explicit'                               => false,
    'has_lyrics'                                => true,
    'is_original_sound'                         => false,
    'hide_remixing'                             => false,
    'should_mute_audio'                         => false,
    'product'                                   => 'story_camera_music_overlay_post_capture',
    'is_sticker'                                => false,
    'display_type'                              => 'HIDDEN',
];

// Lyric sticker
$lyricSticker = [
    [
        'x'                     => 0.5, // Range: 0.0 - 1.0. Note that x = 0.5 and y = 0.5 is center of screen.
        'y'                     => 0.5, // Also note that X/Y is setting the position of the CENTER of the clickable area.
        'z'                     => 1, // Don't change this value.
        'width'                 => 0.7, // Clickable area size, as percentage of image size: 0.0 - 1.0
        'height'                => 0.38643068, // ...
        'rotation'              => 0,
        'type'                  => 'music_lyric',
        'is_sticker'            => true, // Don't change this value.
    ],
];

// Now create the metadata array:
$metadata = [
    'story_music' => [
        // Note that you can only do one story m in this array.
        [
            'story_music_stickers'          => [$musicSticker, $lyricSticker[0]],
            'story_music_lyric_stickers'    => $lyricSticker,
            'story_music_metadata'          => [
                'audio_asset_id'    => $musicItems[0]->getTrack()->getId(),
                'song_name'         => $musicItems[0]->getTrack()->getTitle(),
                'artist_name'       => $musicItems[0]->getTrack()->getDisplayArtist(),
            ],
        ],
    ],
];

try {
    $video = new InstagramAPI\Media\Video\InstagramVideo($videoFilename, ['targetFeed' => InstagramAPI\Constants::FEED_STORY]);
    $ig->story->uploadVideo($video->getFile(), $metadata);
} catch (Exception $e) {
    if ($e instanceof InstagramAPI\Exception\LoginRequiredException) {
        echo 'Password was changed or cookie expired. Please login again.';
    } else {
        echo 'Something went wrong: '.$e->getMessage()."\n";
    }
}
