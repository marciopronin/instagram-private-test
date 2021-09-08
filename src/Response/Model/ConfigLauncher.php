<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class ConfigLauncher extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'params'    => 'UnpredictableKeys\LauncherSyncParamsUnpredictableContainer',
    ];
}
