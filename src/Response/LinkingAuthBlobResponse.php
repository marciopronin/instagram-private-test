<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * LinkingAuthBlobResponse.
 *
 * @method string getJsonSerializedBlob()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isJsonSerializedBlob()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setJsonSerializedBlob(string $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetJsonSerializedBlob()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class LinkingAuthBlobResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'json_serialized_blob'  => 'string',
    ];
}
