<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Voter.
 *
 * @method User getUser()
 * @method float getVote()
 * @method bool isUser()
 * @method bool isVote()
 * @method $this setUser(User $value)
 * @method $this setVote(float $value)
 * @method $this unsetUser()
 * @method $this unsetVote()
 */
class Voter extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'user'  => 'User',
        'vote'  => 'float',
    ];
}
