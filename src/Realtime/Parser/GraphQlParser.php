<?php

namespace InstagramAPI\Realtime\Parser;

use Fbns\Thrift\Compact\Reader;
use Fbns\Thrift\Compact\Types;
use InstagramAPI\Client;
use InstagramAPI\Realtime\Message;
use InstagramAPI\Realtime\ParserInterface;
use InstagramAPI\Realtime\Subscription\GraphQl\AppPresenceSubscription;
use InstagramAPI\Realtime\Subscription\GraphQl\ZeroProvisionSubscription;

class GraphQlParser implements ParserInterface
{
    public const FIELD_TOPIC = 1;
    public const FIELD_PAYLOAD = 2;

    public const TOPIC_DIRECT = 'direct';

    public const MODULE_DIRECT = 'direct';

    public const TOPIC_TO_MODULE_ENUM = [
        self::TOPIC_DIRECT                => self::MODULE_DIRECT,
        AppPresenceSubscription::QUERY    => AppPresenceSubscription::ID,
        AppPresenceSubscription::QUERY2   => AppPresenceSubscription::ID,
        ZeroProvisionSubscription::QUERY  => ZeroProvisionSubscription::ID,
    ];

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     * @throws \DomainException
     */
    public function parseMessage(
        $topic,
        $payload,
    ) {
        $messageValues = [];
        $reader = new Reader($payload);
        foreach ($reader()->value() as $id => $field) {
            if ($field->type() === Types::BINARY) {
                $messageValues[] = $field->value();
            } elseif ($field->type() === self::FIELD_PAYLOAD) {
                $messageValues[] = $field->value();
            }
        }
        $msgTopic = $messageValues[0];
        $msgPayload = $messageValues[1];

        return [$this->_createMessage($msgTopic, $msgPayload)];
    }

    /**
     * Create a message from given topic and payload.
     *
     * @param string $topic
     * @param string $payload
     *
     * @throws \RuntimeException
     * @throws \DomainException
     *
     * @return Message
     */
    protected function _createMessage(
        $topic,
        $payload,
    ) {
        if ($topic === null || $payload === null) {
            throw new \RuntimeException('Incomplete GraphQL message.');
        }

        if (!array_key_exists($topic, self::TOPIC_TO_MODULE_ENUM)) {
            throw new \DomainException(sprintf('Unknown GraphQL topic "%s".', $topic));
        }

        $data = Client::api_body_decode($payload);
        if (!is_array($data)) {
            throw new \RuntimeException('Invalid GraphQL payload.');
        }

        return new Message(self::TOPIC_TO_MODULE_ENUM[$topic], $data);
    }
}
