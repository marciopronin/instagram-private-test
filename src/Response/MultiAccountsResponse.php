<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * MultiAccountsResponse.
 *
 * @method Model\User getCurrentAccount()
 * @method mixed getDeviceBasedHeader()
 * @method Model\LoggedInAccount[] getLoggedInAccounts()
 * @method mixed getLoginDeferredAccounts()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isCurrentAccount()
 * @method bool isDeviceBasedHeader()
 * @method bool isLoggedInAccounts()
 * @method bool isLoginDeferredAccounts()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setCurrentAccount(Model\User $value)
 * @method $this setDeviceBasedHeader(mixed $value)
 * @method $this setLoggedInAccounts(Model\LoggedInAccount[] $value)
 * @method $this setLoginDeferredAccounts(mixed $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetCurrentAccount()
 * @method $this unsetDeviceBasedHeader()
 * @method $this unsetLoggedInAccounts()
 * @method $this unsetLoginDeferredAccounts()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class MultiAccountsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'logged_in_accounts'        => 'Model\LoggedInAccount[]',
        'login_deferred_accounts'   => '',
        'device_based_header'       => '',
        'current_account'           => 'Model\User',
    ];
}
