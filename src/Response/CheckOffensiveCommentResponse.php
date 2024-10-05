<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * CheckOffensiveCommentResponse.
 *
 * @method int getBullyClassifier()
 * @method bool getIsOffensive()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isBullyClassifier()
 * @method bool isIsOffensive()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setBullyClassifier(int $value)
 * @method $this setIsOffensive(bool $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetBullyClassifier()
 * @method $this unsetIsOffensive()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class CheckOffensiveCommentResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'is_offensive'      => 'bool',
        'bully_classifier'  => 'int',
    ];
}
