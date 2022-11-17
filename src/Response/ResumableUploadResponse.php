<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * ResumableUploadResponse.
 *
 * @method int getMediaId()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method int getUploadId()
 * @method mixed getXsharingNonces()
 * @method Model\_Message[] get_Messages()
 * @method bool isMediaId()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isUploadId()
 * @method bool isXsharingNonces()
 * @method bool is_Messages()
 * @method $this setMediaId(int $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setUploadId(int $value)
 * @method $this setXsharingNonces(mixed $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMediaId()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetUploadId()
 * @method $this unsetXsharingNonces()
 * @method $this unset_Messages()
 */
class ResumableUploadResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'xsharing_nonces' => '',
        'upload_id'       => 'int',
        'media_id'        => 'int',
    ];

    /**
     * Checks if the response was successful.
     *
     * @return bool
     */
    public function isOk()
    {
        if ($this->_getProperty('upload_id') !== null || $this->_getProperty('media_id') !== null) {
            return true;
        } else {
            return false;
        }
    }
}
