<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Gating.
 *
 * @method string[] getButtons()
 * @method string getDescription()
 * @method string getGatingType()
 * @method string getTitle()
 * @method bool isButtons()
 * @method bool isDescription()
 * @method bool isGatingType()
 * @method bool isTitle()
 * @method $this setButtons(string[] $value)
 * @method $this setDescription(string $value)
 * @method $this setGatingType(string $value)
 * @method $this setTitle(string $value)
 * @method $this unsetButtons()
 * @method $this unsetDescription()
 * @method $this unsetGatingType()
 * @method $this unsetTitle()
 */
class Gating extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'gating_type' => 'string',
        'description' => 'string',
        'buttons'     => 'string[]',
        'title'       => 'string',
    ];
}
