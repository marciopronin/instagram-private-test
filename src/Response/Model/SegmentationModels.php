<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * SegmentationModels.
 *
 * @method AssetModel getSegInitNetPb()
 * @method AssetModel getSegPredictNetPb()
 * @method bool isSegInitNetPb()
 * @method bool isSegPredictNetPb()
 * @method $this setSegInitNetPb(AssetModel $value)
 * @method $this setSegPredictNetPb(AssetModel $value)
 * @method $this unsetSegInitNetPb()
 * @method $this unsetSegPredictNetPb()
 */
class SegmentationModels extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'seg_predict_net.pb'                   => 'AssetModel',
        'seg_init_net.pb'                      => 'AssetModel',
    ];
}
