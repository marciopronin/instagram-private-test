<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * SendVerifyEmailResponse.
 *
 * @method string getBody()
 * @method bool getEmailSent()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method string getTitle()
 * @method Model\_Message[] get_Messages()
 * @method bool isBody()
 * @method bool isEmailSent()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isTitle()
 * @method bool is_Messages()
 * @method $this setBody(string $value)
 * @method $this setEmailSent(bool $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setTitle(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetBody()
 * @method $this unsetEmailSent()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetTitle()
 * @method $this unset_Messages()
 */
class SendVerifyEmailResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'email_sent'        => 'bool',
        'title'             => 'string',
        'body'              => 'string',
    ];
}
