<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * WebFormDataResponse.
 *
 * @method Model\FormData getFormData()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isFormData()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setFormData(Model\FormData $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetFormData()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class WebFormDataResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'form_data'  => 'Model\FormData',
    ];
}
