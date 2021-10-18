<?php

namespace InstagramAPI\Realtime\Mqttot;

use BinSoul\Net\Mqtt\Client\React\ReactFlow;
use BinSoul\Net\Mqtt\ClientIdentifierGenerator;
use BinSoul\Net\Mqtt\DefaultIdentifierGenerator;
use BinSoul\Net\Mqtt\DefaultMessage;
use BinSoul\Net\Mqtt\DefaultPacketFactory;
use BinSoul\Net\Mqtt\Flow;
use BinSoul\Net\Mqtt\Message;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\PublishRequestPacket;
use BinSoul\Net\Mqtt\StreamParser;
use BinSoul\Net\Mqtt\Subscription;
use Evenement\EventEmitter;
use Exception;
use LogicException;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use React\Promise\CancellablePromiseInterface;
use React\Promise\Deferred;
use React\Promise\ExtendedPromiseInterface;
use React\Promise\RejectedPromise;
use React\Socket\ConnectorInterface;
use React\Stream\DuplexStreamInterface;
use RuntimeException;

/**
 * Connects to a MQTT broker and subscribes to topics or publishes messages.
 *
 * The following events are emitted:
 *  - open - The network connection to the server is established.
 *  - close - The network connection to the server is closed.
 *  - warning - An event of severity "warning" occurred.
 *  - error - An event of severity "error" occurred.
 *
 * If a flow finishes it's result is also emitted, e.g.:
 *  - connect - The client connected to the broker.
 *  - disconnect - The client disconnected from the broker.
 *  - subscribe - The client subscribed to a topic filter.
 *  - unsubscribe - The client unsubscribed from topic filter.
 *  - publish - A message was published.
 *  - message - A message was received.
 */
class ReactMqttClient extends EventEmitter
{
    /** @var ConnectorInterface */
    private $_connector;
    /** @var LoopInterface */
    private $_loop;
    /** @var DuplexStreamInterface|null */
    private $_stream;
    /** @var StreamParser */
    private $_parser;
    /** @var ClientIdentifierGenerator */
    private $_identifierGenerator;

    /** @var string */
    private $_host;
    /** @var int */
    private $_port;
    /** @var RealtimeConnection|null */
    private $_connection;
    /** @var bool */
    private $_isConnected = false;
    /** @var bool */
    private $_isConnecting = false;
    /** @var bool */
    private $_isDisconnecting = false;
    /** @var callable|null */
    private $_onCloseCallback;

    /** @var TimerInterface[] */
    private $_timer = [];

    /** @var ReactFlow[] */
    private $_receivingFlows = [];
    /** @var ReactFlow[] */
    private $_sendingFlows = [];
    /** @var ReactFlow|null */
    private $_writtenFlow;
    /**
     * @var RealtimeFlowFactory|null
     */
    private $_flowFactory;

    /**
     * Constructs an instance of this class.
     *
     * @param ConnectorInterface             $connector
     * @param LoopInterface                  $loop
     * @param ClientIdentifierGenerator|null $identifierGenerator
     * @param StreamParser|null              $parser
     */
    public function __construct(
        ConnectorInterface $connector,
        LoopInterface $loop,
        ?ClientIdentifierGenerator $identifierGenerator = null,
        ?StreamParser $parser = null
    ) {
        $this->_connector = $connector;
        $this->_loop = $loop;

        $this->_parser = $parser;
        if ($this->_parser === null) {
            $this->_parser = new StreamParser(new DefaultPacketFactory());
        }

        $this->_parser->onError(function (Exception $e) {
            $this->_emitWarning($e);
        });

        $this->_identifierGenerator = $identifierGenerator;
        if ($this->_identifierGenerator === null) {
            $this->_identifierGenerator = new DefaultIdentifierGenerator();
        }

        $this->_flowFactory = new RealtimeFlowFactory($this->_identifierGenerator, new DefaultIdentifierGenerator(), new DefaultPacketFactory());
    }

    /**
     * Return the host.
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->_host;
    }

    /**
     * Return the port.
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->_port;
    }

    /**
     * Indicates if the client is connected.
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->_isConnected;
    }

    /**
     * Returns the underlying stream or null if the client is not connected.
     *
     * @return DuplexStreamInterface|null
     */
    public function getStream(): ?DuplexStreamInterface
    {
        return $this->_stream;
    }

    /**
     * Connects to a broker.
     *
     * @param string             $host
     * @param int                $port
     * @param RealtimeConnection $connection
     * @param int                $timeout
     *
     * @return ExtendedPromiseInterface
     */
    public function connect(
        string $host,
        int $port = 1883,
        RealtimeConnection $connection = null,
        int $timeout = 5): ExtendedPromiseInterface
    {
        if ($this->_isConnected || $this->_isConnecting) {
            return new RejectedPromise(new LogicException('The client is already connected.'));
        }

        $this->_isConnecting = true;
        $this->_isConnected = false;

        $this->_host = $host;
        $this->_port = $port;

        $deferred = new Deferred();

        $this->_establishConnection($this->_host, $this->_port, $timeout)
            ->then(function (DuplexStreamInterface $stream) use ($connection, $deferred, $timeout) {
                $this->_stream = $stream;

                $this->emit('open', [$connection, $this]);

                $this->_registerClient($connection, $timeout)
                    ->then(function ($result) use ($connection, $deferred) {
                        $this->_isConnecting = false;
                        $this->_isConnected = true;
                        $this->_connection = $connection;

                        $this->emit('connect', [$connection, $this]);
                        $deferred->resolve($result ?: $connection);
                    })
                    ->otherwise(function (Exception $e) use ($connection, $deferred) {
                        $this->_isConnecting = false;

                        $this->_emitError($e);
                        $deferred->reject($e);

                        if ($this->_stream !== null) {
                            $this->_stream->close();
                        }

                        $this->emit('close', [$connection, $this]);
                    });
            })
            ->otherwise(function (Exception $e) use ($deferred) {
                $this->_isConnecting = false;

                $this->_emitError($e);
                $deferred->reject($e);
            });

        return $deferred->promise();
    }

    /**
     * Disconnects from a broker.
     *
     * @param int $timeout
     *
     * @return ExtendedPromiseInterface
     */
    public function disconnect(
        int $timeout = 5): ExtendedPromiseInterface
    {
        if (!$this->_isConnected || $this->_isDisconnecting) {
            return new RejectedPromise(new LogicException('The client is not connected.'));
        }

        $this->_isDisconnecting = true;

        $deferred = new Deferred();

        $isResolved = false;
        /** @var mixed $flowResult */
        $flowResult = null;

        $this->_onCloseCallback = function ($connection) use ($deferred, &$isResolved, &$flowResult) {
            if (!$isResolved) {
                $isResolved = true;

                if ($connection) {
                    $this->emit('disconnect', [$connection, $this]);
                }

                $deferred->resolve($flowResult ?: $connection);
            }
        };

        $this->_startFlow($this->_flowFactory->buildOutgoingDisconnectFlow($this->_connection), true)
            ->then(function ($result) use ($timeout, &$flowResult) {
                $flowResult = $result;

                $this->_timer[] = $this->_loop->addTimer(
                    $timeout,
                    function () {
                        if ($this->_stream !== null) {
                            $this->_stream->close();
                        }
                    }
                );
            })
            ->otherwise(function ($exception) use ($deferred, &$isResolved) {
                if (!$isResolved) {
                    $isResolved = true;
                    $this->_isDisconnecting = false;
                    $deferred->reject($exception);
                }
            });

        return $deferred->promise();
    }

    /**
     * Subscribes to a topic filter.
     *
     * @param Subscription $subscription
     *
     * @return ExtendedPromiseInterface
     */
    public function subscribe(
        Subscription $subscription): ExtendedPromiseInterface
    {
        if (!$this->_isConnected) {
            return new RejectedPromise(new LogicException('The client is not connected.'));
        }

        return $this->_startFlow($this->_flowFactory->buildOutgoingSubscribeFlow([$subscription]));
    }

    /**
     * Unsubscribes from a topic filter.
     *
     * @param Subscription $subscription
     *
     * @return ExtendedPromiseInterface
     */
    public function unsubscribe(
        Subscription $subscription): ExtendedPromiseInterface
    {
        if (!$this->_isConnected) {
            return new RejectedPromise(new LogicException('The client is not connected.'));
        }

        $deferred = new Deferred();

        $this->_startFlow($this->_flowFactory->buildOutgoingUnsubscribeFlow([$subscription]))
            ->then(static function (array $subscriptions) use ($deferred) {
                $deferred->resolve(array_shift($subscriptions));
            })
            ->otherwise(static function ($exception) use ($deferred) {
                $deferred->reject($exception);
            });

        return $deferred->promise();
    }

    /**
     * Sends a ping.
     *
     * @return ExtendedPromiseInterface
     */
    public function sendPing()
    {
        if (!$this->_isConnected) {
            return new RejectedPromise(new LogicException('The client is not connected.'));
        }

        return $this->_startFlow($this->_flowFactory->buildOutgoingPingFlow());
    }

    /**
     * Publishes a message.
     *
     * @param Message $message
     *
     * @return ExtendedPromiseInterface
     */
    public function publish(
        Message $message): ExtendedPromiseInterface
    {
        if (!$this->_isConnected) {
            return new RejectedPromise(new LogicException('The client is not connected.'));
        }

        return $this->_startFlow($this->_flowFactory->buildOutgoingPublishFlow($message));
    }

    /**
     * Calls the given generator periodically and publishes the return value.
     *
     * @param int      $interval
     * @param Message  $message
     * @param callable $generator
     *
     * @return ExtendedPromiseInterface
     */
    public function publishPeriodically(
        int $interval,
        Message $message,
        callable $generator): ExtendedPromiseInterface
    {
        if (!$this->_isConnected) {
            return new RejectedPromise(new LogicException('The client is not connected.'));
        }

        $deferred = new Deferred();

        $this->_timer[] = $this->_loop->addPeriodicTimer(
            $interval,
            function () use ($message, $generator, $deferred) {
                $this->publish($message->withPayload((string) $generator($message->getTopic())))->then(
                    static function ($value) use ($deferred) {
                        $deferred->notify($value);
                    },
                    static function (Exception $e) use ($deferred) {
                        $deferred->reject($e);
                    }
                );
            }
        );

        return $deferred->promise();
    }

    /**
     * Emits warnings.
     *
     * @param Exception $e
     *
     * @return void
     */
    private function _emitWarning(
        Exception $e): void
    {
        $this->emit('warning', [$e, $this]);
    }

    /**
     * Emits errors.
     *
     * @param Exception $e
     *
     * @return void
     */
    private function _emitError(
        Exception $e): void
    {
        $this->emit('error', [$e, $this]);
    }

    /**
     * Establishes a network connection to a server.
     *
     * @param string $host
     * @param int    $port
     * @param int    $timeout
     *
     * @return ExtendedPromiseInterface
     */
    private function _establishConnection(
        string $host,
        int $port,
        int $timeout): ExtendedPromiseInterface
    {
        $deferred = new Deferred();

        $future = null;
        $timer = $this->_loop->addTimer(
            $timeout,
            static function () use ($deferred, $timeout, &$future) {
                $exception = new RuntimeException(sprintf('Connection timed out after %d seconds.', $timeout));
                $deferred->reject($exception);
                if ($future instanceof CancellablePromiseInterface) {
                    $future->cancel();
                }
                $future = null;
            }
        );

        $future = $this->_connector->connect($host.':'.$port)
            ->always(function () use ($timer) {
                $this->_loop->cancelTimer($timer);
            })
            ->then(function (DuplexStreamInterface $stream) use ($deferred) {
                $stream->on('data', function ($data) {
                    $this->_handleReceive($data);
                });

                $stream->on('close', function () {
                    $this->_handleClose();
                });

                $stream->on('error', function (Exception $e) {
                    $this->_handleError($e);
                });

                $deferred->resolve($stream);
            })
            ->otherwise(static function (Exception $e) use ($deferred) {
                $deferred->reject($e);
            });

        return $deferred->promise();
    }

    /**
     * Registers a new client with the broker.
     *
     * @param RealtimeConnection $connection
     * @param int                $timeout
     *
     * @return ExtendedPromiseInterface
     */
    private function _registerClient(
        RealtimeConnection $connection,
        int $timeout): ExtendedPromiseInterface
    {
        $deferred = new Deferred();

        $responseTimer = $this->_loop->addTimer(
            $timeout,
            static function () use ($deferred, $timeout) {
                $exception = new RuntimeException(sprintf('No response after %d seconds.', $timeout));
                $deferred->reject($exception);
            }
        );

        $this->_startFlow($this->_flowFactory->buildOutgoingConnectFlow($connection), true)
            ->always(function () use ($responseTimer) {
                $this->_loop->cancelTimer($responseTimer);
            })->then(function ($result) use ($connection, $deferred) {
                $this->_timer[] = $this->_loop->addPeriodicTimer(
                    floor(60 * 0.75),
                    function () {
                        $this->_startFlow($this->_flowFactory->buildOutgoingPingFlow());
                    }
                );

                $deferred->resolve($result ?: $connection);
            })->otherwise(static function (Exception $e) use ($deferred) {
                $deferred->reject($e);
            });

        return $deferred->promise();
    }

    /**
     * Handles incoming data.
     *
     * @param string $data
     *
     * @return void
     */
    private function _handleReceive(
        string $data): void
    {
        if (!$this->_isConnected && !$this->_isConnecting) {
            return;
        }

        $flowCount = count($this->_receivingFlows);

        $packets = $this->_parser->push($data);
        foreach ($packets as $packet) {
            $this->_handlePacket($packet);
        }

        if ($flowCount > count($this->_receivingFlows)) {
            $this->_receivingFlows = array_values($this->_receivingFlows);
        }

        $this->_handleSend();
    }

    /**
     * Handles an incoming packet.
     *
     * @param Packet $packet
     *
     * @return void
     */
    private function _handlePacket(
        Packet $packet): void
    {
        switch ($packet->getPacketType()) {
            case Packet::TYPE_PUBLISH:
                if (!($packet instanceof PublishRequestPacket)) {
                    throw new RuntimeException(sprintf('Expected %s but got %s.', PublishRequestPacket::class, get_class($packet)));
                }

                $message = new DefaultMessage(
                    $packet->getTopic(),
                    $packet->getPayload(),
                    $packet->getQosLevel(),
                    $packet->isRetained(),
                    $packet->isDuplicate()
                );

                $this->_startFlow($this->_flowFactory->buildIncomingPublishFlow($message, $packet->getIdentifier()));
                break;
            case Packet::TYPE_CONNACK:
            case Packet::TYPE_PINGRESP:
            case Packet::TYPE_SUBACK:
            case Packet::TYPE_UNSUBACK:
            case Packet::TYPE_PUBREL:
            case Packet::TYPE_PUBACK:
            case Packet::TYPE_PUBREC:
            case Packet::TYPE_PUBCOMP:
                $flowFound = false;
                foreach ($this->_receivingFlows as $index => $flow) {
                    if ($flow->accept($packet)) {
                        $flowFound = true;

                        unset($this->_receivingFlows[$index]);
                        $this->_continueFlow($flow, $packet);

                        break;
                    }
                }

                if (!$flowFound) {
                    $this->_emitWarning(
                        new LogicException(sprintf('Received unexpected packet of type %d.', $packet->getPacketType()))
                    );
                }
                break;
            default:
                $this->_emitWarning(
                    new LogicException(sprintf('Cannot handle packet of type %d.', $packet->getPacketType()))
                );
        }
    }

    /**
     * Handles outgoing packets.
     *
     * @return void
     */
    private function _handleSend(): void
    {
        $flow = null;
        if ($this->_writtenFlow !== null) {
            $flow = $this->_writtenFlow;
            $this->_writtenFlow = null;
        }

        if (count($this->_sendingFlows) > 0) {
            $this->_writtenFlow = array_shift($this->_sendingFlows);
            $this->_stream->write($this->_writtenFlow->getPacket());
        }

        if ($flow !== null) {
            if ($flow->isFinished()) {
                $this->_loop->futureTick(function () use ($flow) {
                    $this->_finishFlow($flow);
                });
            } else {
                $this->_receivingFlows[] = $flow;
            }
        }
    }

    /**
     * Handles closing of the stream.
     *
     * @return void
     */
    private function _handleClose(): void
    {
        foreach ($this->_timer as $timer) {
            $this->_loop->cancelTimer($timer);
        }

        $connection = $this->_connection;

        $this->_isConnecting = false;
        $this->_isDisconnecting = false;
        $this->_isConnected = false;
        $this->_connection = null;
        $this->_stream = null;

        if ($this->_onCloseCallback !== null) {
            call_user_func($this->_onCloseCallback, $connection);
            $this->_onCloseCallback = null;
        }

        if ($connection !== null) {
            $this->emit('close', [$connection, $this]);
        }
    }

    /**
     * Handles errors of the stream.
     *
     * @param Exception $e
     *
     * @return void
     */
    private function _handleError(
        Exception $e): void
    {
        $this->_emitError($e);
    }

    /**
     * Starts the given flow.
     *
     * @param Flow $flow
     * @param bool $isSilent
     *
     * @return ExtendedPromiseInterface
     */
    private function _startFlow(
        Flow $flow,
        bool $isSilent = false): ExtendedPromiseInterface
    {
        try {
            $packet = $flow->start();
        } catch (Exception $e) {
            $this->_emitError($e);

            return new RejectedPromise($e);
        }

        $deferred = new Deferred();
        $internalFlow = new ReactFlow($flow, $deferred, $packet, $isSilent);

        if ($packet !== null) {
            if ($this->_writtenFlow !== null) {
                $this->_sendingFlows[] = $internalFlow;
            } else {
                $this->_stream->write($packet);
                $this->_writtenFlow = $internalFlow;
                $this->_handleSend();
            }
        } else {
            $this->_loop->futureTick(function () use ($internalFlow) {
                $this->_finishFlow($internalFlow);
            });
        }

        return $deferred->promise();
    }

    /**
     * Continues the given flow.
     *
     * @param ReactFlow $flow
     * @param Packet    $packet
     *
     * @return void
     */
    private function _continueFlow(
        ReactFlow $flow,
        Packet $packet): void
    {
        try {
            $response = $flow->next($packet);
        } catch (Exception $e) {
            $this->_emitError($e);

            return;
        }

        if ($response !== null) {
            if ($this->_writtenFlow !== null) {
                $this->_sendingFlows[] = $flow;
            } else {
                $this->_stream->write($response);
                $this->_writtenFlow = $flow;
                $this->_handleSend();
            }
        } elseif ($flow->isFinished()) {
            $this->_loop->futureTick(function () use ($flow) {
                $this->_finishFlow($flow);
            });
        }
    }

    /**
     * Finishes the given flow.
     *
     * @param ReactFlow $flow
     *
     * @return void
     */
    private function _finishFlow(
        ReactFlow $flow): void
    {
        if ($flow->isSuccess()) {
            if (!$flow->isSilent()) {
                $this->emit($flow->getCode(), [$flow->getResult(), $this]);
            }

            $flow->getDeferred()->resolve($flow->getResult());
        } else {
            $result = new RuntimeException($flow->getErrorMessage());
            $this->_emitWarning($result);

            $flow->getDeferred()->reject($result);
        }
    }
}
