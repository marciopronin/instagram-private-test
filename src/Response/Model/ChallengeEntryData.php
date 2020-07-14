<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * ChallengeEntryData.
 *
 * @method WebChallenge[] getChallenge()
 * @method bool isChallenge()
 * @method $this setChallenge(WebChallenge[] $value)
 * @method $this unsetChallenge()
 */
class ChallengeEntryData extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'Challenge'             => 'WebChallenge[]',
    ];
}
