<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Phrase.
 *
 * @method int getEndTimeInMs()
 * @method string getPhrase()
 * @method int getStartTimeInMs()
 * @method WordOffset[] getWordOffsets()
 * @method bool isEndTimeInMs()
 * @method bool isPhrase()
 * @method bool isStartTimeInMs()
 * @method bool isWordOffsets()
 * @method $this setEndTimeInMs(int $value)
 * @method $this setPhrase(string $value)
 * @method $this setStartTimeInMs(int $value)
 * @method $this setWordOffsets(WordOffset[] $value)
 * @method $this unsetEndTimeInMs()
 * @method $this unsetPhrase()
 * @method $this unsetStartTimeInMs()
 * @method $this unsetWordOffsets()
 */
class Phrase extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'start_time_in_ms'          => 'int',
        'end_time_in_ms'            => 'int',
        'phrase'                    => 'string',
        'word_offsets'              => 'WordOffset[]',
    ];
}
