<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * WebChallenge.
 *
 * @method string getChallengeType()
 * @method ChallengeFields getFields()
 * @method ChallengeNavigation getNavigation()
 * @method bool isChallengeType()
 * @method bool isFields()
 * @method bool isNavigation()
 * @method $this setChallengeType(string $value)
 * @method $this setFields(ChallengeFields $value)
 * @method $this setNavigation(ChallengeNavigation $value)
 * @method $this unsetChallengeType()
 * @method $this unsetFields()
 * @method $this unsetNavigation()
 */
class WebChallenge extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'challengeType'             => 'string',
        'navigation'                => 'ChallengeNavigation',
        'fields'                    => 'ChallengeFields',
    ];
}
