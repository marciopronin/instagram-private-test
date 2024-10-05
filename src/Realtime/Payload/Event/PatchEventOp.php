<?php

namespace InstagramAPI\Realtime\Payload\Event;

use InstagramAPI\AutoPropertyMapper;

/**
 * PatchEventOp.
 *
 * @method mixed getDoublePublish()
 * @method mixed getOp()
 * @method mixed getPath()
 * @method mixed getTs()
 * @method mixed getValue()
 * @method bool isDoublePublish()
 * @method bool isOp()
 * @method bool isPath()
 * @method bool isTs()
 * @method bool isValue()
 * @method $this setDoublePublish(mixed $value)
 * @method $this setOp(mixed $value)
 * @method $this setPath(mixed $value)
 * @method $this setTs(mixed $value)
 * @method $this setValue(mixed $value)
 * @method $this unsetDoublePublish()
 * @method $this unsetOp()
 * @method $this unsetPath()
 * @method $this unsetTs()
 * @method $this unsetValue()
 */
class PatchEventOp extends AutoPropertyMapper
{
    public const ADD = 'add';
    public const REMOVE = 'remove';
    public const REPLACE = 'replace';
    public const NOTIFY = 'notify';

    public const JSON_PROPERTY_MAP = [
        'op'            => '',
        'path'          => '',
        'value'         => '',
        'ts'            => '',
        'doublePublish' => '',
    ];
}
