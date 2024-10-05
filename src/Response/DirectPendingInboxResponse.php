<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * DirectPendingInboxResponse.
 *
 * @method Model\DirectInbox getInbox()
 * @method mixed getMessage()
 * @method mixed getPendingRequestsTotal()
 * @method string getSeqId()
 * @method mixed getSpamRequestsTotal()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isInbox()
 * @method bool isMessage()
 * @method bool isPendingRequestsTotal()
 * @method bool isSeqId()
 * @method bool isSpamRequestsTotal()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setInbox(Model\DirectInbox $value)
 * @method $this setMessage(mixed $value)
 * @method $this setPendingRequestsTotal(mixed $value)
 * @method $this setSeqId(string $value)
 * @method $this setSpamRequestsTotal(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetInbox()
 * @method $this unsetMessage()
 * @method $this unsetPendingRequestsTotal()
 * @method $this unsetSeqId()
 * @method $this unsetSpamRequestsTotal()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class DirectPendingInboxResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'seq_id'                 => 'string',
        'pending_requests_total' => '',
        'spam_requests_total'    => '',
        'inbox'                  => 'Model\DirectInbox',
    ];
}
