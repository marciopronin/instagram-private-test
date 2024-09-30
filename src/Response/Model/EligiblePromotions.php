<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * EligiblePromotions.
 *
 * @method Edges[] getEdges()
 * @method bool isEdges()
 * @method $this setEdges(Edges[] $value)
 * @method $this unsetEdges()
 */
class EligiblePromotions extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'edges'   => 'Edges[]',
    ];
}
