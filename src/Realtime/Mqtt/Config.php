<?php

namespace InstagramAPI\Realtime\Mqtt;

class Config
{
    /* MQTT server options */
    public const DEFAULT_HOST = 'edge-mqtt.facebook.com';
    public const DEFAULT_PORT = 443;

    /* MQTT protocol options */
    public const MQTT_KEEPALIVE = 900;
    public const MQTT_VERSION = 3;

    /* MQTT client options */
    public const NETWORK_TYPE_WIFI = 1;
    public const CLIENT_TYPE = 'cookie_auth';
    public const PUBLISH_FORMAT = 'jz';
    public const CONNECTION_TIMEOUT = 5;
}
