<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Suggestion.
 *
 * @method string getAlgorithm()
 * @method string getCaption()
 * @method Hashtag getHashtag()
 * @method mixed getIcon()
 * @method bool getIsNewSuggestion()
 * @method string[] getLargeUrls()
 * @method mixed getMediaIds()
 * @method MediaData getMediaInfos()
 * @method string getSocialContext()
 * @method string getSubtitle()
 * @method string[] getThumbnailUrls()
 * @method string getTitle()
 * @method User getUser()
 * @method string getUuid()
 * @method float getValue()
 * @method bool isAlgorithm()
 * @method bool isCaption()
 * @method bool isHashtag()
 * @method bool isIcon()
 * @method bool isIsNewSuggestion()
 * @method bool isLargeUrls()
 * @method bool isMediaIds()
 * @method bool isMediaInfos()
 * @method bool isSocialContext()
 * @method bool isSubtitle()
 * @method bool isThumbnailUrls()
 * @method bool isTitle()
 * @method bool isUser()
 * @method bool isUuid()
 * @method bool isValue()
 * @method $this setAlgorithm(string $value)
 * @method $this setCaption(string $value)
 * @method $this setHashtag(Hashtag $value)
 * @method $this setIcon(mixed $value)
 * @method $this setIsNewSuggestion(bool $value)
 * @method $this setLargeUrls(string[] $value)
 * @method $this setMediaIds(mixed $value)
 * @method $this setMediaInfos(MediaData $value)
 * @method $this setSocialContext(string $value)
 * @method $this setSubtitle(string $value)
 * @method $this setThumbnailUrls(string[] $value)
 * @method $this setTitle(string $value)
 * @method $this setUser(User $value)
 * @method $this setUuid(string $value)
 * @method $this setValue(float $value)
 * @method $this unsetAlgorithm()
 * @method $this unsetCaption()
 * @method $this unsetHashtag()
 * @method $this unsetIcon()
 * @method $this unsetIsNewSuggestion()
 * @method $this unsetLargeUrls()
 * @method $this unsetMediaIds()
 * @method $this unsetMediaInfos()
 * @method $this unsetSocialContext()
 * @method $this unsetSubtitle()
 * @method $this unsetThumbnailUrls()
 * @method $this unsetTitle()
 * @method $this unsetUser()
 * @method $this unsetUuid()
 * @method $this unsetValue()
 */
class Suggestion extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'media_infos'       => '',
        'social_context'    => 'string',
        'algorithm'         => 'string',
        'thumbnail_urls'    => 'string[]',
        'value'             => 'float',
        'caption'           => 'string',
        'user'              => 'User',
        'large_urls'        => 'string[]',
        'media_ids'         => '',
        'icon'              => '',
        'is_new_suggestion' => 'bool',
        'uuid'              => 'string',
        'title'             => 'string',
        'subtitle'          => 'string',
        'hashtag'           => 'Hashtag',
        'media_infos'       => 'MediaData',
    ];
}
