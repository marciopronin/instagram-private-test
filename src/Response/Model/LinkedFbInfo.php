<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * LinkedFbInfo.
 *
 * @method FacebookUser getLinkedFbPage()
 * @method LinkedFbUser getLinkedFbUser()
 * @method bool isLinkedFbPage()
 * @method bool isLinkedFbUser()
 * @method $this setLinkedFbPage(FacebookUser $value)
 * @method $this setLinkedFbUser(LinkedFbUser $value)
 * @method $this unsetLinkedFbPage()
 * @method $this unsetLinkedFbUser()
 */
class LinkedFbInfo extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'linked_fb_user'          => 'LinkedFbUser',
        'linked_fb_page'          => 'FacebookUser',
    ];
}
