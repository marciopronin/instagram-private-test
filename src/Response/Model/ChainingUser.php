<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * ChainingUser.
 *
 * @method ChainingInfo getChainingInfo()
 * @method string getFullName()
 * @method bool getIsPrivate()
 * @method bool getIsVerified()
 * @method string getPg()
 * @method string getProfileChainingSecondaryLabel()
 * @method string getProfilePicId()
 * @method string getProfilePicUrl()
 * @method string getSocialContext()
 * @method string getUsername()
 * @method bool isChainingInfo()
 * @method bool isFullName()
 * @method bool isIsPrivate()
 * @method bool isIsVerified()
 * @method bool isPg()
 * @method bool isProfileChainingSecondaryLabel()
 * @method bool isProfilePicId()
 * @method bool isProfilePicUrl()
 * @method bool isSocialContext()
 * @method bool isUsername()
 * @method $this setChainingInfo(ChainingInfo $value)
 * @method $this setFullName(string $value)
 * @method $this setIsPrivate(bool $value)
 * @method $this setIsVerified(bool $value)
 * @method $this setPg(string $value)
 * @method $this setProfileChainingSecondaryLabel(string $value)
 * @method $this setProfilePicId(string $value)
 * @method $this setProfilePicUrl(string $value)
 * @method $this setSocialContext(string $value)
 * @method $this setUsername(string $value)
 * @method $this unsetChainingInfo()
 * @method $this unsetFullName()
 * @method $this unsetIsPrivate()
 * @method $this unsetIsVerified()
 * @method $this unsetPg()
 * @method $this unsetProfileChainingSecondaryLabel()
 * @method $this unsetProfilePicId()
 * @method $this unsetProfilePicUrl()
 * @method $this unsetSocialContext()
 * @method $this unsetUsername()
 */
class ChainingUser extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'pg'                                   => 'string',
        'username'                             => 'string',
        'full_name'                            => 'string',
        'is_private'                           => 'bool',
        'profile_pic_url'                      => 'string',
        'profile_pic_id'                       => 'string',
        'is_verified'                          => 'bool',
        'chaining_info'                        => 'ChainingInfo',
        'profile_chaining_secondary_label'     => 'string',
        'social_context'                       => 'string',
    ];
}
