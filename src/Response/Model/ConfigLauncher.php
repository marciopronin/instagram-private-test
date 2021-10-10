<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * ConfigLauncher.
 *
 * @method UnpredictableKeys\LauncherSyncParamsUnpredictableContainer getParams()
 * @method bool isParams()
 * @method $this setParams(UnpredictableKeys\LauncherSyncParamsUnpredictableContainer $value)
 * @method $this unsetParams()
 */
class ConfigLauncher extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'params'    => 'UnpredictableKeys\LauncherSyncParamsUnpredictableContainer',
    ];
}
