<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * CameraModelsResponse.
 *
 * @method mixed getMessage()
 * @method Model\CameraModels[] getModels()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isMessage()
 * @method bool isModels()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setMessage(mixed $value)
 * @method $this setModels(Model\CameraModels[] $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMessage()
 * @method $this unsetModels()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class CameraModelsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'models'             => 'Model\CameraModels[]',
    ];
}