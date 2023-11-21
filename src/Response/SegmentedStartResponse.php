<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * SegmentedStartResponse.
 *
 * @method mixed getMessage()
 * @method int getOffset()
 * @method string getStatus()
 * @method string getStreamId()
 * @method Model\_Message[] get_Messages()
 * @method bool isMessage()
 * @method bool isOffset()
 * @method bool isStatus()
 * @method bool isStreamId()
 * @method bool is_Messages()
 * @method $this setMessage(mixed $value)
 * @method $this setOffset(int $value)
 * @method $this setStatus(string $value)
 * @method $this setStreamId(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMessage()
 * @method $this unsetOffset()
 * @method $this unsetStatus()
 * @method $this unsetStreamId()
 * @method $this unset_Messages()
 */
class SegmentedStartResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'stream_id' => 'string',
        'offset'    => 'int',
    ];

    /**
     * Checks if the response was successful.
     *
     * @return bool
     */
    public function isOk()
    {
        //$streamId = $this->_getProperty('stream_id');
        $streamId = $this->_getProperty('offset');
        if ($streamId !== null && $streamId !== '') {
            return true;
        } else {
            // Set a nice message for exceptions.
            if ($this->getMessage() === null) {
                //$this->setMessage('Stream ID for segmented uploader is missing or invalid.');
                $this->setMessage('Offset for segmented uploader is missing or invalid.');
            }

            return false;
        }
    }
}
