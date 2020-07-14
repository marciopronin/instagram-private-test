<?php

namespace InstagramAPI\Realtime\Mqttot;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\BasePacket;
use BinSoul\Net\Mqtt\PacketStream;
use InvalidArgumentException;

class RealtimeConnectPacket extends BasePacket
{
    /** @var int */
    private $_protocolLevel = 3;
    /** @var string */
    private $_protocolName = 'MQTToT';
    /** @var int */
    private $_flags = 194;
    /** @var int */
    private $_keepAlive = 60;
    /** @var string */
    private $_payload;

    public static $packetType = Packet::TYPE_CONNECT;

    public function read(
        PacketStream $stream): void
    {
        parent::read($stream);
        $this->assertPacketFlags(0);
        $this->assertRemainingPacketLength();

        $originalPosition = $stream->getPosition();
        $this->_protocolName = $stream->readString();
        $this->_protocolLevel = $stream->readByte();
        $this->_flags = $stream->readByte();
        $this->_keepAlive = $stream->readWord();

        $payloadLength = $this->remainingPacketLength - ($stream->getPosition() - $originalPosition);
        $this->payload = $stream->read($payloadLength);
    }

    public function write(
        PacketStream $stream): void
    {
        $data = new PacketStream();

        $data->writeString($this->_protocolName);
        $data->writeByte($this->_protocolLevel);
        $data->writeByte($this->_flags);
        $data->writeWord($this->_keepAlive);
        $data->write($this->payload);

        $this->remainingPacketLength = $data->length();

        parent::write($stream);
        $stream->write($data->getData());
    }

    /**
     * Returns the protocol level.
     *
     * @return int
     */
    public function getProtocolLevel()
    {
        return $this->_protocolLevel;
    }

    /**
     * Sets the protocol level.
     *
     * @param int $value
     *
     * @throws InvalidArgumentException
     */
    public function setProtocolLevel(
        $value)
    {
        if ($value != 3) {
            throw new \InvalidArgumentException(sprintf('Unknown protocol level %d.', $value));
        }

        $this->_protocolLevel = $value;
    }

    /**
     * Returns the payload.
     *
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Sets the payload.
     *
     * @param string $value
     */
    public function setPayload(
        $value)
    {
        $this->payload = $value;
    }

    /**
     * Returns the flags.
     *
     * @return int
     */
    public function getFlags()
    {
        return $this->_flags;
    }

    /**
     * Sets the flags.
     *
     * @param int $value
     *
     * @throws InvalidArgumentException
     */
    public function setFlags(
        $value)
    {
        if ($value > 255) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected a flags lower than 255 but got %d.',
                    $value
                )
            );
        }

        $this->_flags = $value;
    }

    /**
     * Returns the keep alive time in seconds.
     *
     * @return int
     */
    public function getKeepAlive()
    {
        return $this->_keepAlive;
    }

    /**
     * Sets the keep alive time in seconds.
     *
     * @param int $value
     *
     * @throws InvalidArgumentException
     */
    public function setKeepAlive(
        $value)
    {
        if ($value > 65535) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected a keep alive time lower than 65535 but got %d.',
                    $value
                )
            );
        }

        $this->_keepAlive = $value;
    }

    /**
     * Returns the protocol name.
     *
     * @return string
     */
    public function getProtocolName()
    {
        return $this->_protocolName;
    }

    /**
     * Sets the protocol name.
     *
     * @param string $value
     *
     * @throws MalformedPacketException
     */
    public function setProtocolName(
        $value)
    {
        $this->assertValidStringLength($value);

        $this->_protocolName = $value;
    }
}
