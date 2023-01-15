<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * DiscoveryAccountsCategories.
 *
 * @method bool getHasMoreSuggestions()
 * @method string getName()
 * @method int getPreviewNo()
 * @method SuggestionCard[] getSuggestionCards()
 * @method string getTitle()
 * @method bool isHasMoreSuggestions()
 * @method bool isName()
 * @method bool isPreviewNo()
 * @method bool isSuggestionCards()
 * @method bool isTitle()
 * @method $this setHasMoreSuggestions(bool $value)
 * @method $this setName(string $value)
 * @method $this setPreviewNo(int $value)
 * @method $this setSuggestionCards(SuggestionCard[] $value)
 * @method $this setTitle(string $value)
 * @method $this unsetHasMoreSuggestions()
 * @method $this unsetName()
 * @method $this unsetPreviewNo()
 * @method $this unsetSuggestionCards()
 * @method $this unsetTitle()
 */
class DiscoveryAccountsCategories extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'name'                  => 'string',
        'title'                 => 'string',
        'suggestion_cards'      => 'SuggestionCard[]',
        'has_more_suggestions'  => 'bool',
        'preview_no'            => 'int',
    ];
}
