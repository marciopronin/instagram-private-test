<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * GetPostLiveThumbnailsResponse.
 *
 * @method mixed getMessage()
 * @method string getStatus()
 * @method string[] getThumbnails()
 * @method string getTitlePrefill()
 * @method Model\_Message[] get_Messages()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isThumbnails()
 * @method bool isTitlePrefill()
 * @method bool is_Messages()
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setThumbnails(string[] $value)
 * @method $this setTitlePrefill(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetThumbnails()
 * @method $this unsetTitlePrefill()
 * @method $this unset_Messages()
 */
class GetPostLiveThumbnailsResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'thumbnails'        => 'string[]',
        'title_prefill'     => 'string',
    ];
}
