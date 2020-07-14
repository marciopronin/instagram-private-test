<?php

namespace InstagramAPI\Realtime\Mqttot;

use BinSoul\Net\Mqtt\ClientIdentifierGenerator;
use BinSoul\Net\Mqtt\Connection;
use BinSoul\Net\Mqtt\Flow\IncomingPingFlow;
use BinSoul\Net\Mqtt\Flow\IncomingPublishFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingDisconnectFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingPingFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingPublishFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingSubscribeFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingUnsubscribeFlow;
use BinSoul\Net\Mqtt\Message;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\PacketIdentifierGenerator;

class RealtimeFlowFactory
{
    /**
     * @var ClientIdentifierGenerator
     */
    private $_clientIdentifierGenerator;
    /**
     * @var PacketIdentifierGenerator
     */
    private $_packetIdentifierGenerator;
    /**
     * @var PacketFactory
     */
    private $_packetFactory;

    /**
     * Constructs an instance of this class.
     *
     * @param ClientIdentifierGenerator $clientIdentifierGenerator
     * @param PacketIdentifierGenerator $packetIdentifierGenerator
     * @param PacketFactory             $packetFactory
     */
    public function __construct(
        ClientIdentifierGenerator $clientIdentifierGenerator,
        PacketIdentifierGenerator $packetIdentifierGenerator,
        PacketFactory $packetFactory
    ) {
        $this->_clientIdentifierGenerator = $clientIdentifierGenerator;
        $this->_packetIdentifierGenerator = $packetIdentifierGenerator;
        $this->_packetFactory = $packetFactory;
    }

    public function buildIncomingPingFlow(): IncomingPingFlow
    {
        return new IncomingPingFlow($this->_packetFactory);
    }

    public function buildIncomingPublishFlow(
        Message $message,
        int $identifier = null): IncomingPublishFlow
    {
        return new IncomingPublishFlow($this->_packetFactory, $message, $identifier);
    }

    public function buildOutgoingConnectFlow(
        RealtimeConnection $connection): RealtimeConnectFlow
    {
        return new RealtimeConnectFlow($this->_packetFactory, $connection);
    }

    public function buildOutgoingDisconnectFlow(
        Connection $connection): OutgoingDisconnectFlow
    {
        return new OutgoingDisconnectFlow($this->_packetFactory, $connection);
    }

    public function buildOutgoingPingFlow(): OutgoingPingFlow
    {
        return new OutgoingPingFlow($this->_packetFactory);
    }

    public function buildOutgoingPublishFlow(
        Message $message): OutgoingPublishFlow
    {
        return new OutgoingPublishFlow($this->_packetFactory, $message, $this->_packetIdentifierGenerator);
    }

    public function buildOutgoingSubscribeFlow(
        array $subscriptions): OutgoingSubscribeFlow
    {
        return new OutgoingSubscribeFlow($this->_packetFactory, $subscriptions, $this->_packetIdentifierGenerator);
    }

    public function buildOutgoingUnsubscribeFlow(
        array $subscriptions): OutgoingUnsubscribeFlow
    {
        return new OutgoingUnsubscribeFlow($this->_packetFactory, $subscriptions, $this->_packetIdentifierGenerator);
    }
}
