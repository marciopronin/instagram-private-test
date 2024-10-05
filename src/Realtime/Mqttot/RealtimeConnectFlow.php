<?php

namespace InstagramAPI\Realtime\Mqttot;

use BinSoul\Net\Mqtt\Flow\AbstractFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\ConnectResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;

class RealtimeConnectFlow extends AbstractFlow
{
    /** @var RealtimeConnection */
    private $_connection;

    /**
     * Constructs an instance of this class.
     *
     * @param PacketFactory      $packetFactory
     * @param RealtimeConnection $connection
     */
    public function __construct(
        PacketFactory $packetFactory,
        RealtimeConnection $connection
    ) {
        parent::__construct($packetFactory);

        $this->_connection = $connection;
    }

    public function getCode(): string
    {
        return 'connect';
    }

    public function start(): ?Packet
    {
        $packet = new RealtimeConnectPacket();
        $packet->setPayload(zlib_encode($this->_connection->toThrift(), ZLIB_ENCODING_DEFLATE, 9));

        return $packet;
    }

    public function accept(
        Packet $packet
    ): bool {
        return $packet->getPacketType() === Packet::TYPE_CONNACK;
    }

    public function next(
        Packet $packet
    ): ?Packet {
        /** @var ConnectResponsePacket $packet */
        if ($packet->isSuccess()) {
            $this->succeed($this->_connection);
        } else {
            $this->fail($packet->getErrorName());
        }

        return null;
    }
}
