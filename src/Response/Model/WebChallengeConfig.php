<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * WebChallengeConfig.
 *
 * @method string getCsrfToken()
 * @method mixed getViewer()
 * @method string getViewerId()
 * @method bool isCsrfToken()
 * @method bool isViewer()
 * @method bool isViewerId()
 * @method $this setCsrfToken(string $value)
 * @method $this setViewer(mixed $value)
 * @method $this setViewerId(string $value)
 * @method $this unsetCsrfToken()
 * @method $this unsetViewer()
 * @method $this unsetViewerId()
 */
class WebChallengeConfig extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'csrf_token'    => 'string',
        'viewer'        => '',
        'viewerId'      => 'string',
    ];
}
