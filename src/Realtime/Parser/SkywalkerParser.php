<?php

namespace InstagramAPI\Realtime\Parser;

use Fbns\Thrift\Compact\Reader;
use Fbns\Thrift\Compact\Types;
use InstagramAPI\Client;
use InstagramAPI\Realtime\Message;
use InstagramAPI\Realtime\ParserInterface;

class SkywalkerParser implements ParserInterface
{
    const FIELD_TOPIC = 1;
    const FIELD_PAYLOAD = 2;

    const TOPIC_DIRECT = 1;
    const TOPIC_LIVE = 2;
    const TOPIC_LIVEWITH = 3;

    const MODULE_DIRECT = 'direct';
    const MODULE_LIVE = 'live';
    const MODULE_LIVEWITH = 'livewith';

    const TOPIC_TO_MODULE_ENUM = [
        self::TOPIC_DIRECT   => self::MODULE_DIRECT,
        self::TOPIC_LIVE     => self::MODULE_LIVE,
        self::TOPIC_LIVEWITH => self::MODULE_LIVEWITH,
    ];

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     * @throws \DomainException
     */
    public function parseMessage(
        $topic,
        $payload)
    {
        $msgTopic = $msgPayload = null;
        $reader = new Reader($payload);
        foreach ($reader()->value() as $id => $field) {
            if ($field->type() === Types::I32 && $id === self::FIELD_TOPIC) {
                $msgTopic = $field->value();
            } elseif ($field->type() === Types::BINARY && $id === self::FIELD_PAYLOAD) {
                $msgPayload = $field->value();
            }
        }

        return [$this->_createMessage($msgTopic, $msgPayload)];
    }

    /**
     * Create a message from given topic and payload.
     *
     * @param int    $topic
     * @param string $payload
     *
     * @throws \RuntimeException
     * @throws \DomainException
     *
     * @return Message
     */
    protected function _createMessage(
        $topic,
        $payload)
    {
        if ($topic === null || $payload === null) {
            throw new \RuntimeException('Incomplete Skywalker message.');
        }

        if (!array_key_exists($topic, self::TOPIC_TO_MODULE_ENUM)) {
            throw new \DomainException(sprintf('Unknown Skywalker topic "%d".', $topic));
        }

        $data = Client::api_body_decode($payload);
        if (!is_array($data)) {
            throw new \RuntimeException('Invalid Skywalker payload.');
        }

        return new Message(self::TOPIC_TO_MODULE_ENUM[$topic], $data);
    }
}
