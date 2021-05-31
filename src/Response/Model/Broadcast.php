<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Broadcast.
 *
 * @method string getBroadcastMessage()
 * @method User getBroadcastOwner()
 * @method string getBroadcastStatus()
 * @method bool getCanReply()
 * @method bool getCanReshare()
 * @method bool getCanShareToIgtv()
 * @method mixed getCobroadcasters()
 * @method string getCoverFrameUrl()
 * @method string getDashAbrPlaybackUrl()
 * @method string getDashLivePredictivePlaybackUrl()
 * @method string getDashManifest()
 * @method string getDashPlaybackUrl()
 * @method string getEncodingTag()
 * @method string getExpireAt()
 * @method bool getHideFromFeedUnit()
 * @method string getId()
 * @method bool getInternalOnly()
 * @method bool getIsGamingContent()
 * @method bool getIsPlayerLiveTraceEnabled()
 * @method string getLastSeenBroadcastTs()
 * @method string getMediaId()
 * @method mixed getMuted()
 * @method int getNumberOfQualities()
 * @method string getOrganicTrackingToken()
 * @method string getProgressivePlaybackUrl()
 * @method string getPublishedTime()
 * @method mixed getRankedPosition()
 * @method string getRtmpPlaybackUrl()
 * @method mixed getSeenRankedPosition()
 * @method int getTotalUniqueViewerCount()
 * @method int getViewerCount()
 * @method bool isBroadcastMessage()
 * @method bool isBroadcastOwner()
 * @method bool isBroadcastStatus()
 * @method bool isCanReply()
 * @method bool isCanReshare()
 * @method bool isCanShareToIgtv()
 * @method bool isCobroadcasters()
 * @method bool isCoverFrameUrl()
 * @method bool isDashAbrPlaybackUrl()
 * @method bool isDashLivePredictivePlaybackUrl()
 * @method bool isDashManifest()
 * @method bool isDashPlaybackUrl()
 * @method bool isEncodingTag()
 * @method bool isExpireAt()
 * @method bool isHideFromFeedUnit()
 * @method bool isId()
 * @method bool isInternalOnly()
 * @method bool isIsGamingContent()
 * @method bool isIsPlayerLiveTraceEnabled()
 * @method bool isLastSeenBroadcastTs()
 * @method bool isMediaId()
 * @method bool isMuted()
 * @method bool isNumberOfQualities()
 * @method bool isOrganicTrackingToken()
 * @method bool isProgressivePlaybackUrl()
 * @method bool isPublishedTime()
 * @method bool isRankedPosition()
 * @method bool isRtmpPlaybackUrl()
 * @method bool isSeenRankedPosition()
 * @method bool isTotalUniqueViewerCount()
 * @method bool isViewerCount()
 * @method $this setBroadcastMessage(string $value)
 * @method $this setBroadcastOwner(User $value)
 * @method $this setBroadcastStatus(string $value)
 * @method $this setCanReply(bool $value)
 * @method $this setCanReshare(bool $value)
 * @method $this setCanShareToIgtv(bool $value)
 * @method $this setCobroadcasters(mixed $value)
 * @method $this setCoverFrameUrl(string $value)
 * @method $this setDashAbrPlaybackUrl(string $value)
 * @method $this setDashLivePredictivePlaybackUrl(string $value)
 * @method $this setDashManifest(string $value)
 * @method $this setDashPlaybackUrl(string $value)
 * @method $this setEncodingTag(string $value)
 * @method $this setExpireAt(string $value)
 * @method $this setHideFromFeedUnit(bool $value)
 * @method $this setId(string $value)
 * @method $this setInternalOnly(bool $value)
 * @method $this setIsGamingContent(bool $value)
 * @method $this setIsPlayerLiveTraceEnabled(bool $value)
 * @method $this setLastSeenBroadcastTs(string $value)
 * @method $this setMediaId(string $value)
 * @method $this setMuted(mixed $value)
 * @method $this setNumberOfQualities(int $value)
 * @method $this setOrganicTrackingToken(string $value)
 * @method $this setProgressivePlaybackUrl(string $value)
 * @method $this setPublishedTime(string $value)
 * @method $this setRankedPosition(mixed $value)
 * @method $this setRtmpPlaybackUrl(string $value)
 * @method $this setSeenRankedPosition(mixed $value)
 * @method $this setTotalUniqueViewerCount(int $value)
 * @method $this setViewerCount(int $value)
 * @method $this unsetBroadcastMessage()
 * @method $this unsetBroadcastOwner()
 * @method $this unsetBroadcastStatus()
 * @method $this unsetCanReply()
 * @method $this unsetCanReshare()
 * @method $this unsetCanShareToIgtv()
 * @method $this unsetCobroadcasters()
 * @method $this unsetCoverFrameUrl()
 * @method $this unsetDashAbrPlaybackUrl()
 * @method $this unsetDashLivePredictivePlaybackUrl()
 * @method $this unsetDashManifest()
 * @method $this unsetDashPlaybackUrl()
 * @method $this unsetEncodingTag()
 * @method $this unsetExpireAt()
 * @method $this unsetHideFromFeedUnit()
 * @method $this unsetId()
 * @method $this unsetInternalOnly()
 * @method $this unsetIsGamingContent()
 * @method $this unsetIsPlayerLiveTraceEnabled()
 * @method $this unsetLastSeenBroadcastTs()
 * @method $this unsetMediaId()
 * @method $this unsetMuted()
 * @method $this unsetNumberOfQualities()
 * @method $this unsetOrganicTrackingToken()
 * @method $this unsetProgressivePlaybackUrl()
 * @method $this unsetPublishedTime()
 * @method $this unsetRankedPosition()
 * @method $this unsetRtmpPlaybackUrl()
 * @method $this unsetSeenRankedPosition()
 * @method $this unsetTotalUniqueViewerCount()
 * @method $this unsetViewerCount()
 */
class Broadcast extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'broadcast_owner'           => 'User',
        'cobroadcasters'            => '',
        /*
         * A string such as "active" or "post_live".
         */
        'broadcast_status'                  => 'string',
        'is_gaming_content'                 => 'bool',
        'is_player_live_trace_enabled'      => 'bool',
        'dash_live_predictive_playback_url' => 'string',
        'cover_frame_url'                   => 'string',
        'published_time'                    => 'string',
        'hide_from_feed_unit'               => 'bool',
        'broadcast_message'                 => 'string',
        'muted'                             => '',
        'media_id'                          => 'string',
        'id'                                => 'string',
        'rtmp_playback_url'                 => 'string',
        'dash_abr_playback_url'             => 'string',
        'dash_playback_url'                 => 'string',
        'ranked_position'                   => '',
        'organic_tracking_token'            => 'string',
        'seen_ranked_position'              => '',
        'viewer_count'                      => 'int',
        'dash_manifest'                     => 'string',
        'progressive_playback_url'          => 'string',
        'last_seen_broadcast_ts'            => 'string',
        'can_reply'                         => 'bool',
        'can_reshare'                       => 'bool',
        'can_share_to_igtv'                 => 'bool',
        /*
         * Unix timestamp of when the "post_live" will expire.
         */
        'expire_at'                 => 'string',
        'encoding_tag'              => 'string',
        'total_unique_viewer_count' => 'int',
        'internal_only'             => 'bool',
        'number_of_qualities'       => 'int',
    ];
}
