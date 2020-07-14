<?php

namespace InstagramAPI\Realtime\Parser;

use Fbns\Thrift\Compact\Reader;
use Fbns\Thrift\Compact\Types;
use InstagramAPI\Realtime\Handler\RegionHintHandler;
use InstagramAPI\Realtime\Message;
use InstagramAPI\Realtime\ParserInterface;

class RegionHintParser implements ParserInterface
{
    const FIELD_TOPIC = 1;

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
        $region = null;
        $reader = new Reader($payload);
        foreach ($reader()->value() as $id => $field) {
            if ($field->type() === Types::BINARY && $id === self::FIELD_TOPIC) {
                $region = $field->value();
            }
        }

        return [$this->_createMessage($region)];
    }

    /**
     * Create a message from given topic and payload.
     *
     * @param string $region
     *
     * @throws \RuntimeException
     * @throws \DomainException
     *
     * @return Message
     */
    protected function _createMessage(
        $region)
    {
        if ($region === null) {
            throw new \RuntimeException('Incomplete region hint message.');
        }

        return new Message(RegionHintHandler::MODULE, $region);
    }
}
