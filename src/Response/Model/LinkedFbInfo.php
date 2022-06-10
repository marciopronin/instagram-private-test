<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * LinkedFbInfo.
 *
 * @method LinkedFbUser getLinkedFbUser()
 * @method bool isLinkedFbUser()
 * @method $this setLinkedFbUser(LinkedFbUser $value)
 * @method $this unsetLinkedFbUser()
 */
class LinkedFbInfo extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'linked_fb_user'          => 'LinkedFbUser',
    ];
}
