<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * PrivateStoriesMembersResponse.
 *
 * @method Model\User[] getMembers()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\User[] getSuggestedUsers()
 * @method Model\_Message[] get_Messages()
 * @method bool isMembers()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isSuggestedUsers()
 * @method bool is_Messages()
 * @method $this setMembers(Model\User[] $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setSuggestedUsers(Model\User[] $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMembers()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetSuggestedUsers()
 * @method $this unset_Messages()
 */
class PrivateStoriesMembersResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'members'             => 'Model\User[]',
        'suggested_users'     => 'Model\User[]',
    ];
}
