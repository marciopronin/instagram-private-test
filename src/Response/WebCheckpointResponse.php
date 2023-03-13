<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * WebCheckpointResponse.
 *
 * @method Model\WebChallenge getChallenge()
 * @method string getChallengeType()
 * @method Model\WebChallengeConfig getConfig()
 * @method Model\ChallengeEntryData getEntryData()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isChallenge()
 * @method bool isChallengeType()
 * @method bool isConfig()
 * @method bool isEntryData()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setChallenge(Model\WebChallenge $value)
 * @method $this setChallengeType(string $value)
 * @method $this setConfig(Model\WebChallengeConfig $value)
 * @method $this setEntryData(Model\ChallengeEntryData $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetChallenge()
 * @method $this unsetChallengeType()
 * @method $this unsetConfig()
 * @method $this unsetEntryData()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class WebCheckpointResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'entry_data'           => 'Model\ChallengeEntryData',
        'challenge'            => 'Model\WebChallenge',
        'challengeType'        => 'string',
        'config'               => 'Model\WebChallengeConfig',
    ];

    public function getChallengeType()
    {
        if ($this->_getProperty('entry_data') !== null) {
            return $this->_getProperty('entry_data')->_getProperty('Challenge')[0]->_getProperty('challengeType');
        } elseif ($this->_getProperty('challenge') !== null) {
            return $this->_getProperty('challenge')->_getProperty('challengeType');
        } else {
            return $this->_getProperty('challengeType');
        }
    }

    public function getChallengeUrl()
    {
        if ($this->_getProperty('entry_data') !== null) {
            return $this->_getProperty('entry_data')->_getProperty('Challenge')[0]->_getProperty('navigation')->_getProperty('forward');
        } else {
            return $this->_getProperty('challenge')->_getProperty('navigation')->_getProperty('forward');
        }
    }

    public function getSiteKey()
    {
        return $this->_getProperty('entry_data')->_getProperty('Challenge')[0]->_getProperty('fields')->_getProperty('sitekey');
    }

    public function getVerificationChoice()
    {
        if ($this->_getProperty('entry_data') !== null) {
            return $this->_getProperty('entry_data')->_getProperty('Challenge')[0]->_getProperty('fields')->_getProperty('choice');
        } else {
            return $this->_getProperty('challenge')->_getProperty('fields')->_getProperty('choice');
        }
    }

    public function getCsrftoken()
    {
        if ($this->_getProperty('config') !== null) {
            return $this->_getProperty('config')->_getProperty('csrf_token');
        } else {
            return null;
        }
    }
}
