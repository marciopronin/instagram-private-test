<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * WebCheckpointResponse.
 *
 * @method Model\ChallengeEntryData getEntryData()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isEntryData()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setEntryData(Model\ChallengeEntryData $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetEntryData()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class WebCheckpointResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'entry_data'           => 'Model\ChallengeEntryData',
    ];

    public function getChallengeType()
    {
        return $this->_getProperty('entry_data')->_getProperty('Challenge')[0]->_getProperty('challengeType');
    }

    public function getChallengeUrl()
    {
        return $this->_getProperty('entry_data')->_getProperty('Challenge')[0]->_getProperty('navigation')->_getProperty('forward');
    }

    public function getSiteKey()
    {
        return $this->_getProperty('entry_data')->_getProperty('Challenge')[0]->_getProperty('fields')->_getProperty('sitekey');
    }

    public function getVerificationChoice()
    {
        return $this->_getProperty('entry_data')->_getProperty('Challenge')[0]->_getProperty('fields')->_getProperty('choice');
    }
}
