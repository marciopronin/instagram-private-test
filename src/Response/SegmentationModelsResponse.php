<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * SegmentationModelsResponse.
 *
 * @method mixed getMessage()
 * @method Model\SegmentationModels getSegmentationModels()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isMessage()
 * @method bool isSegmentationModels()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setMessage(mixed $value)
 * @method $this setSegmentationModels(Model\SegmentationModels $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMessage()
 * @method $this unsetSegmentationModels()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class SegmentationModelsResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'segmentation_models'             => 'Model\SegmentationModels',
    ];
}
