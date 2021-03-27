<?php

namespace InstagramAPI\Realtime\Mqttot;

use Fbns\Mqtt\RtiConnection;
use Fbns\Thrift\Compact\Types;
use InstagramAPI\Constants;
use InstagramAPI\Instagram;
use InstagramAPI\Exception\InstagramException;

class RealtimeConnection extends RtiConnection
{
    const CLIENT_ID = 1;
    const CLIENT_INFO = 4;
    const PASSWORD = 5;
    const APP_SPECIFIC_INFO = 10;

    const USER_ID = 1;
    const USER_AGENT = 2;
    const CLIENT_CAPABILITIES = 3;
    const ENDPOINT_CAPABILITIES = 4;
    const PUBLISH_FORMAT = 5;
    const NO_AUTOMATIC_FOREGROUND = 6;
    const MAKE_USER_AVAILABLE_IN_FOREGROUND = 7;
    const DEVICE_ID = 8;
    const IS_INITIALLY_FOREGROUND = 9;
    const NETWORK_TYPE = 10;
    const NETWORK_SUBTYPE = 11;
    const CLIENT_MQTT_SESSION_ID = 12;
    const SUBSCRIBE_TOPICS = 14;
    const CLIENT_TYPE = 15;
    const APP_ID = 16;
    const DEVICE_SECRET = 20;
    const CLIENT_STACK = 21;

    /** @var string */
    private $_userAgent;
    /** @var int */
    private $_clientCapabilities;
    /** @var int */
    private $_endpointCapabilities;
    /** @var int */
    private $_publishFormat;
    /** @var bool */
    private $_noAutomaticForeground;
    /** @var bool */
    private $_makeUserAvailableInForeground;
    /** @var bool */
    private $_isInitiallyForeground;
    /** @var int */
    private $_networkType;
    /** @var int */
    private $_networkSubtype;
    /** @var int */
    private $_clientMqttSessionId;
    /** @var int[] */
    private $_subscribeTopics;
    /** @var int */
    private $_clientStack;
    /** @var string */
    private $_deviceId;
    /** @var string */
    private $_userId;
    /** @var string */
    private $_sessionId;

    /**
     * RealtimeConnection constructor.
     *
     * @param Instagram $ig Instagram API object.
     */
    public function __construct(
        $ig)
    {
        $this->_userId = $ig->account_id;
        if ($ig->client->getCookie('sessionid') === null) {
            throw new InstagramException('User cookies does not contain session_id');
        } else {
            $this->_sessionId = $ig->client->getCookie('sessionid')->getValue();
        }
        $this->_clientCapabilities = 183;
        $this->_endpointCapabilities = 0;
        $this->_publishFormat = 1;
        $this->_noAutomaticForeground = false;
        $this->_deviceId = $ig->phone_id;
        $this->_isInitiallyForeground = true;
        $this->_networkType = 1;
        $this->_networkSubtype = 0;
        $this->_subscribeTopics = [88, 135, 149, 150, 133, 146];
        $this->_clientStack = 3;
        $this->_userAgent = $ig->device->getUserAgent();
        $this->_makeUserAvailableInForeground = true;
        $this->_clientMqttSessionId = 123456789;
    }

    /**
     * @return string
     */
    public function toThrift(): string
    {
        $writer = new ExtendedThiftWriter();

        $writer->writeString(self::CLIENT_ID, substr($this->_deviceId, 0, 20));

        $writer->writeStruct(self::CLIENT_INFO);
        $writer->writeInt64(self::USER_ID, intval($this->_userId));
        $writer->writeString(self::USER_AGENT, $this->_userAgent);
        $writer->writeInt64(self::CLIENT_CAPABILITIES, $this->_clientCapabilities);
        $writer->writeInt64(self::ENDPOINT_CAPABILITIES, $this->_endpointCapabilities);
        $writer->writeInt32(self::PUBLISH_FORMAT, $this->_publishFormat);
        $writer->writeBool(self::NO_AUTOMATIC_FOREGROUND, $this->_noAutomaticForeground);
        $writer->writeBool(self::MAKE_USER_AVAILABLE_IN_FOREGROUND, $this->_makeUserAvailableInForeground);
        $writer->writeString(self::DEVICE_ID, $this->_deviceId);
        $writer->writeBool(self::IS_INITIALLY_FOREGROUND, $this->_isInitiallyForeground);
        $writer->writeInt32(self::NETWORK_TYPE, $this->_networkType);
        $writer->writeInt32(self::NETWORK_SUBTYPE, $this->_networkSubtype);
        if ($this->_clientMqttSessionId === null) {
            $_sessionId = (int) ((microtime(true) - strtotime('Last Monday')));
        } else {
            $_sessionId = $this->_clientMqttSessionId;
        }
        $writer->writeInt64(self::CLIENT_MQTT_SESSION_ID, $_sessionId);
        $writer->writeList(self::SUBSCRIBE_TOPICS, Types::I32, $this->_subscribeTopics);
        $writer->writeString(self::CLIENT_TYPE, 'cookie_auth');
        $writer->writeInt64(self::APP_ID, intval(Constants::FACEBOOK_ANALYTICS_APPLICATION_ID));
        $writer->writeString(self::DEVICE_SECRET, '');
        $writer->writeInt8(self::CLIENT_STACK, $this->_clientStack);
        $writer->writeStop();

        $writer->writeString(self::PASSWORD, 'sessionid='.$this->_sessionId);
        $writer->writeStrStrMap(self::APP_SPECIFIC_INFO, [
            ['app_version', Constants::IG_VERSION],
            ['X-IG-Capabilities', Constants::X_IG_Capabilities],
            ['everclear_subscriptions',
                '{'.
                    '"inapp_notification_subscribe_comment":"17899377895239777",'.
                    '"inapp_notification_subscribe_comment_mention_and_reply":"17899377895239777",'.
                    '"video_call_participant_state_delivery":"17977239895057311",'.
                    '"presence_subscribe":"17846944882223835"'.
                '}', ],
            ['User-Agent', $this->_userAgent],
            ['Accept-Language', Constants::USER_AGENT_LOCALE],
            ['platform', 'android'],
            ['ig_mqtt_route', 'django'],
            ['pubsub_msg_type_blacklist', 'direct, typing_type'],
            ['auth_cache_enabled', '0'],
        ]);
        $writer->writeStop();

        return (string) $writer;
    }
}
