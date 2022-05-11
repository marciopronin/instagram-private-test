<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * SuggestionsWithMetadata.
 *
 * @method UsernameSuggestions[] getSuggestions()
 * @method bool isSuggestions()
 * @method $this setSuggestions(UsernameSuggestions[] $value)
 * @method $this unsetSuggestions()
 */
class SuggestionsWithMetadata extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'suggestions'         => 'UsernameSuggestions[]',
    ];
}
