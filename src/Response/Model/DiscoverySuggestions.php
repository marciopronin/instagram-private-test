<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * DiscoverySuggestions.
 *
 * @method DiscoverySuggestedGroup[] getGroups()
 * @method bool getMoreAvailable()
 * @method string getRankingAlgorithm()
 * @method bool isGroups()
 * @method bool isMoreAvailable()
 * @method bool isRankingAlgorithm()
 * @method $this setGroups(DiscoverySuggestedGroup[] $value)
 * @method $this setMoreAvailable(bool $value)
 * @method $this setRankingAlgorithm(string $value)
 * @method $this unsetGroups()
 * @method $this unsetMoreAvailable()
 * @method $this unsetRankingAlgorithm()
 */
class DiscoverySuggestions extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'groups'                 => 'DiscoverySuggestedGroup[]',
        'more_available'         => 'bool',
        'ranking_algorithm'      => 'string',
    ];
}
