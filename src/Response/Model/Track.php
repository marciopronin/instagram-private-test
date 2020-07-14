<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Track.
 *
 * @method bool getAllowsSaving()
 * @method string getAudioAssetId()
 * @method string getAudioClusterId()
 * @method string getCoverArtworkThumbnailUri()
 * @method string getCoverArtworkUri()
 * @method mixed getDarkMessage()
 * @method string getDashManifest()
 * @method string getDisplayArtist()
 * @method int getDurationInMs()
 * @method bool getHasLyrics()
 * @method int[] getHighlightStartTimesInMs()
 * @method string getId()
 * @method bool getIsExplicit()
 * @method string getProgressiveDownloadUrl()
 * @method string getSubtitle()
 * @method string getTitle()
 * @method bool isAllowsSaving()
 * @method bool isAudioAssetId()
 * @method bool isAudioClusterId()
 * @method bool isCoverArtworkThumbnailUri()
 * @method bool isCoverArtworkUri()
 * @method bool isDarkMessage()
 * @method bool isDashManifest()
 * @method bool isDisplayArtist()
 * @method bool isDurationInMs()
 * @method bool isHasLyrics()
 * @method bool isHighlightStartTimesInMs()
 * @method bool isId()
 * @method bool isIsExplicit()
 * @method bool isProgressiveDownloadUrl()
 * @method bool isSubtitle()
 * @method bool isTitle()
 * @method $this setAllowsSaving(bool $value)
 * @method $this setAudioAssetId(string $value)
 * @method $this setAudioClusterId(string $value)
 * @method $this setCoverArtworkThumbnailUri(string $value)
 * @method $this setCoverArtworkUri(string $value)
 * @method $this setDarkMessage(mixed $value)
 * @method $this setDashManifest(string $value)
 * @method $this setDisplayArtist(string $value)
 * @method $this setDurationInMs(int $value)
 * @method $this setHasLyrics(bool $value)
 * @method $this setHighlightStartTimesInMs(int[] $value)
 * @method $this setId(string $value)
 * @method $this setIsExplicit(bool $value)
 * @method $this setProgressiveDownloadUrl(string $value)
 * @method $this setSubtitle(string $value)
 * @method $this setTitle(string $value)
 * @method $this unsetAllowsSaving()
 * @method $this unsetAudioAssetId()
 * @method $this unsetAudioClusterId()
 * @method $this unsetCoverArtworkThumbnailUri()
 * @method $this unsetCoverArtworkUri()
 * @method $this unsetDarkMessage()
 * @method $this unsetDashManifest()
 * @method $this unsetDisplayArtist()
 * @method $this unsetDurationInMs()
 * @method $this unsetHasLyrics()
 * @method $this unsetHighlightStartTimesInMs()
 * @method $this unsetId()
 * @method $this unsetIsExplicit()
 * @method $this unsetProgressiveDownloadUrl()
 * @method $this unsetSubtitle()
 * @method $this unsetTitle()
 */
class Track extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'audio_cluster_id'              => 'string',
        'id'                            => 'string',
        'title'                         => 'string',
        'subtitle'                      => 'string',
        'display_artist'                => 'string',
        'cover_artwork_uri'             => 'string',
        'cover_artwork_thumbnail_uri'   => 'string',
        'progressive_download_url'      => 'string',
        'highlight_start_times_in_ms'   => 'int[]',
        'is_explicit'                   => 'bool',
        'dash_manifest'                 => 'string',
        'has_lyrics'                    => 'bool',
        'audio_asset_id'                => 'string',
        'duration_in_ms'                => 'int',
        'dark_message'                  => '',
        'allows_saving'                 => 'bool',
    ];
}
