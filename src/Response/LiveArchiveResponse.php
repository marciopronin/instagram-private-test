<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class LiveArchiveResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'items'                  => 'Model\Item[]',
        'count'                  => 'int'
    ];
}
