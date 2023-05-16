<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * InterestTopic.
 *
 * @method string getEmoji()
 * @method string getId()
 * @method string getName()
 * @method InterestSubtopic[] getSubtopics()
 * @method bool isEmoji()
 * @method bool isId()
 * @method bool isName()
 * @method bool isSubtopics()
 * @method $this setEmoji(string $value)
 * @method $this setId(string $value)
 * @method $this setName(string $value)
 * @method $this setSubtopics(InterestSubtopic[] $value)
 * @method $this unsetEmoji()
 * @method $this unsetId()
 * @method $this unsetName()
 * @method $this unsetSubtopics()
 */
class InterestTopic extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'name'      => 'string',
        'emoji'     => 'string',
        'subtopics' => 'InterestSubtopic[]',
        'id'        => 'string',
    ];
}
