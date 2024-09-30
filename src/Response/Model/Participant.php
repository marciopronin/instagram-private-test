<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Participant.
 *
 * @method int getAnswer()
 * @method string getId()
 * @method int getTs()
 * @method User getUser()
 * @method bool isAnswer()
 * @method bool isId()
 * @method bool isTs()
 * @method bool isUser()
 * @method $this setAnswer(int $value)
 * @method $this setId(string $value)
 * @method $this setTs(int $value)
 * @method $this setUser(User $value)
 * @method $this unsetAnswer()
 * @method $this unsetId()
 * @method $this unsetTs()
 * @method $this unsetUser()
 */
class Participant extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'answer'              => 'int',
        'id'                  => 'string',
        'user'                => 'User',
        'ts'                  => 'int',
    ];
}
