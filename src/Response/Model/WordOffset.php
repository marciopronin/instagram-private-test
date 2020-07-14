<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * WordOffset.
 *
 * @method int getEndIndex()
 * @method int getEndOffsetMs()
 * @method int getStartIndex()
 * @method int getStartOffsetMs()
 * @method bool getTrailingSpace()
 * @method bool isEndIndex()
 * @method bool isEndOffsetMs()
 * @method bool isStartIndex()
 * @method bool isStartOffsetMs()
 * @method bool isTrailingSpace()
 * @method $this setEndIndex(int $value)
 * @method $this setEndOffsetMs(int $value)
 * @method $this setStartIndex(int $value)
 * @method $this setStartOffsetMs(int $value)
 * @method $this setTrailingSpace(bool $value)
 * @method $this unsetEndIndex()
 * @method $this unsetEndOffsetMs()
 * @method $this unsetStartIndex()
 * @method $this unsetStartOffsetMs()
 * @method $this unsetTrailingSpace()
 */
class WordOffset extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'start_index'                        => 'int',
        'end_index'                          => 'int',
        'start_offset_ms'                    => 'int',
        'end_offset_ms'                      => 'int',
        'trailing_space'                     => 'bool',
    ];
}
