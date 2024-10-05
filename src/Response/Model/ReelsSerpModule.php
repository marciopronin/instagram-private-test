<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * ReelsSerpModule.
 *
 * @method Clip[] getClips()
 * @method string getModuleType()
 * @method bool isClips()
 * @method bool isModuleType()
 * @method $this setClips(Clip[] $value)
 * @method $this setModuleType(string $value)
 * @method $this unsetClips()
 * @method $this unsetModuleType()
 */
class ReelsSerpModule extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'module_type'   => 'string',
        'clips'         => 'Clip[]',
    ];
}
