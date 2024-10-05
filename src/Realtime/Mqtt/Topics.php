<?php

namespace InstagramAPI\Realtime\Mqtt;

class Topics
{
    public const PP = '/pp';
    public const PP_ID = '34';

    public const PUBSUB = '/pubsub';
    public const PUBSUB_ID = '88';

    public const SEND_MESSAGE = '/ig_send_message';
    public const SEND_MESSAGE_ID = '132';

    public const SEND_MESSAGE_RESPONSE = '/ig_send_message_response';
    public const SEND_MESSAGE_RESPONSE_ID = '133';

    public const IRIS_SUB = '/ig_sub_iris';
    public const IRIS_SUB_ID = '134';

    public const IRIS_SUB_RESPONSE = '/ig_sub_iris_response';
    public const IRIS_SUB_RESPONSE_ID = '135';

    public const MESSAGE_SYNC = '/ig_message_sync';
    public const MESSAGE_SYNC_ID = '146';

    public const REALTIME_SUB = '/ig_realtime_sub';
    public const REALTIME_SUB_ID = '149';

    public const GRAPHQL = '/graphql';
    public const GRAPHQL_ID = '9';

    public const REGION_HINT = '/t_region_hint';
    public const REGION_HINT_ID = '150';

    public const ID_TO_TOPIC_MAP = [
        self::PP_ID                    => self::PP,
        self::PUBSUB_ID                => self::PUBSUB,
        self::SEND_MESSAGE_ID          => self::SEND_MESSAGE,
        self::SEND_MESSAGE_RESPONSE_ID => self::SEND_MESSAGE_RESPONSE,
        self::IRIS_SUB_ID              => self::IRIS_SUB,
        self::IRIS_SUB_RESPONSE_ID     => self::IRIS_SUB_RESPONSE,
        self::MESSAGE_SYNC_ID          => self::MESSAGE_SYNC,
        self::REALTIME_SUB_ID          => self::REALTIME_SUB,
        self::GRAPHQL_ID               => self::GRAPHQL,
        self::REGION_HINT_ID           => self::REGION_HINT,
    ];

    public const TOPIC_TO_ID_MAP = [
        self::PP                    => self::PP_ID,
        self::PUBSUB                => self::PUBSUB_ID,
        self::SEND_MESSAGE          => self::SEND_MESSAGE_ID,
        self::SEND_MESSAGE_RESPONSE => self::SEND_MESSAGE_RESPONSE_ID,
        self::IRIS_SUB              => self::IRIS_SUB_ID,
        self::IRIS_SUB_RESPONSE     => self::IRIS_SUB_RESPONSE_ID,
        self::MESSAGE_SYNC          => self::MESSAGE_SYNC_ID,
        self::REALTIME_SUB          => self::REALTIME_SUB_ID,
        self::GRAPHQL               => self::GRAPHQL_ID,
        self::REGION_HINT           => self::REGION_HINT_ID,
    ];
}
