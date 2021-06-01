<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class TwoFactorNotificationStatusResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'review_status' => 'int',
    ];
}
