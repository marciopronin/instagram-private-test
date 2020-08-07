<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * UserCard.
 *
 * @method string getAlgorithm()
 * @method string getCaption()
 * @method bool getFollowedBy()
 * @method mixed getIcon()
 * @method bool getIsNewSuggestion()
 * @method string[] getLargeUrls()
 * @method string[] getMediaIds()
 * @method Media[] getMediaInfos()
 * @method string getSocialContext()
 * @method string[] getThumbnailUrls()
 * @method User getUser()
 * @method string getUuid()
 * @method float getValue()
 * @method bool isAlgorithm()
 * @method bool isCaption()
 * @method bool isFollowedBy()
 * @method bool isIcon()
 * @method bool isIsNewSuggestion()
 * @method bool isLargeUrls()
 * @method bool isMediaIds()
 * @method bool isMediaInfos()
 * @method bool isSocialContext()
 * @method bool isThumbnailUrls()
 * @method bool isUser()
 * @method bool isUuid()
 * @method bool isValue()
 * @method $this setAlgorithm(string $value)
 * @method $this setCaption(string $value)
 * @method $this setFollowedBy(bool $value)
 * @method $this setIcon(mixed $value)
 * @method $this setIsNewSuggestion(bool $value)
 * @method $this setLargeUrls(string[] $value)
 * @method $this setMediaIds(string[] $value)
 * @method $this setMediaInfos(Media[] $value)
 * @method $this setSocialContext(string $value)
 * @method $this setThumbnailUrls(string[] $value)
 * @method $this setUser(User $value)
 * @method $this setUuid(string $value)
 * @method $this setValue(float $value)
 * @method $this unsetAlgorithm()
 * @method $this unsetCaption()
 * @method $this unsetFollowedBy()
 * @method $this unsetIcon()
 * @method $this unsetIsNewSuggestion()
 * @method $this unsetLargeUrls()
 * @method $this unsetMediaIds()
 * @method $this unsetMediaInfos()
 * @method $this unsetSocialContext()
 * @method $this unsetThumbnailUrls()
 * @method $this unsetUser()
 * @method $this unsetUuid()
 * @method $this unsetValue()
 */
class UserCard extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'user'              => 'User',
        'algorithm'         => 'string',
        'social_context'    => 'string',
        'caption'           => 'string',
        'icon'              => '',
        'media_ids'         => 'string[]',
        'thumbnail_urls'    => 'string[]',
        'large_urls'        => 'string[]',
        'media_infos'       => 'Media[]',
        'value'             => 'float',
        'is_new_suggestion' => 'bool',
        'uuid'              => 'string',
        'followed_by'       => 'bool',
    ];
}
