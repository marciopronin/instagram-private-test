<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * FollowChainingRecsResponse.
 *
 * @method mixed getMessage()
 * @method string getStatus()
 * @method string getStyle()
 * @method Model\Suggestion[] getSuggestionsWithMedia()
 * @method string getTitle()
 * @method string getType()
 * @method string getViewAllText()
 * @method Model\_Message[] get_Messages()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isStyle()
 * @method bool isSuggestionsWithMedia()
 * @method bool isTitle()
 * @method bool isType()
 * @method bool isViewAllText()
 * @method bool is_Messages()
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setStyle(string $value)
 * @method $this setSuggestionsWithMedia(Model\Suggestion[] $value)
 * @method $this setTitle(string $value)
 * @method $this setType(string $value)
 * @method $this setViewAllText(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetStyle()
 * @method $this unsetSuggestionsWithMedia()
 * @method $this unsetTitle()
 * @method $this unsetType()
 * @method $this unsetViewAllText()
 * @method $this unset_Messages()
 */
class FollowChainingRecsResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'title'                     => 'string',
        'type'                      => 'string',
        'suggestions_with_media'    => 'Model\Suggestion[]',
        'style'                     => 'string',
        'view_all_text'             => 'string',
    ];
}
