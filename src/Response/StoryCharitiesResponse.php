<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * StoryCharitiesResponse.
 *
 * @method mixed getFollowedCharities()
 * @method int getMaxId()
 * @method mixed getMessage()
 * @method bool getMoreAvailable()
 * @method Model\CharitySection[] getNullstateCharitiesSections()
 * @method Model\User[] getSearchedCharities()
 * @method string getSearchedCharitiesSectionTitle()
 * @method string getStatus()
 * @method mixed getSuggestedCharities()
 * @method Model\_Message[] get_Messages()
 * @method bool isFollowedCharities()
 * @method bool isMaxId()
 * @method bool isMessage()
 * @method bool isMoreAvailable()
 * @method bool isNullstateCharitiesSections()
 * @method bool isSearchedCharities()
 * @method bool isSearchedCharitiesSectionTitle()
 * @method bool isStatus()
 * @method bool isSuggestedCharities()
 * @method bool is_Messages()
 * @method $this setFollowedCharities(mixed $value)
 * @method $this setMaxId(int $value)
 * @method $this setMessage(mixed $value)
 * @method $this setMoreAvailable(bool $value)
 * @method $this setNullstateCharitiesSections(Model\CharitySection[] $value)
 * @method $this setSearchedCharities(Model\User[] $value)
 * @method $this setSearchedCharitiesSectionTitle(string $value)
 * @method $this setStatus(string $value)
 * @method $this setSuggestedCharities(mixed $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetFollowedCharities()
 * @method $this unsetMaxId()
 * @method $this unsetMessage()
 * @method $this unsetMoreAvailable()
 * @method $this unsetNullstateCharitiesSections()
 * @method $this unsetSearchedCharities()
 * @method $this unsetSearchedCharitiesSectionTitle()
 * @method $this unsetStatus()
 * @method $this unsetSuggestedCharities()
 * @method $this unset_Messages()
 */
class StoryCharitiesResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'followed_charities'                => '',
        'suggested_charities'               => '',
        'nullstate_charities_sections'      => 'Model\CharitySection[]',
        'searched_charities_section_title'  => 'string',
        'searched_charities'                => 'Model\User[]',
        'max_id'                            => 'int',
        'more_available'                    => 'bool',
    ];
}
