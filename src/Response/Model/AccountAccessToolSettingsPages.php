<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * AccountAccessToolSettingsPages.
 *
 * @method AccountAccessToolSettingsProperties getData()
 * @method AccountAccessToolSettingsProperties getDateJoined()
 * @method bool getIsBlocked()
 * @method string getPageName()
 * @method AccountAccessToolSettingsProperties getSwitchedToBusiness()
 * @method bool isData()
 * @method bool isDateJoined()
 * @method bool isIsBlocked()
 * @method bool isPageName()
 * @method bool isSwitchedToBusiness()
 * @method $this setData(AccountAccessToolSettingsProperties $value)
 * @method $this setDateJoined(AccountAccessToolSettingsProperties $value)
 * @method $this setIsBlocked(bool $value)
 * @method $this setPageName(string $value)
 * @method $this setSwitchedToBusiness(AccountAccessToolSettingsProperties $value)
 * @method $this unsetData()
 * @method $this unsetDateJoined()
 * @method $this unsetIsBlocked()
 * @method $this unsetPageName()
 * @method $this unsetSwitchedToBusiness()
 */
class AccountAccessToolSettingsPages extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'date_joined'                 => 'AccountAccessToolSettingsProperties',
        'switched_to_business'        => 'AccountAccessToolSettingsProperties',
        'is_blocked'                  => 'bool',
        'page_name'                   => 'string',
        'data'                        => 'AccountAccessToolSettingsProperties',
    ];
}
