<?php

namespace InstagramAPI\Realtime;

use BinSoul\Net\Mqtt\DefaultMessage;
use BinSoul\Net\Mqtt\Message as MqttMessage;
use Evenement\EventEmitterInterface;
use Fbns\Auth as AuthInterface;
use InstagramAPI\Devices\DeviceInterface;
use InstagramAPI\ExperimentsInterface;
use InstagramAPI\Instagram;
use InstagramAPI\React\PersistentInterface;
use InstagramAPI\React\PersistentTrait;
use InstagramAPI\Realtime\Command\UpdateSubscriptions;
use InstagramAPI\Realtime\Mqttot\ReactMqttClient;
use InstagramAPI\Realtime\Mqttot\RealtimeConnection;
use InstagramAPI\Realtime\Subscription\GraphQl\AppPresenceSubscription;
use InstagramAPI\Realtime\Subscription\GraphQl\DirectTypingSubscription;
use InstagramAPI\Realtime\Subscription\GraphQl\ZeroProvisionSubscription;
use InstagramAPI\Realtime\Subscription\Skywalker\DirectSubscription;
use InstagramAPI\Realtime\Subscription\Skywalker\LiveSubscription;
use InstagramAPI\Signatures;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use React\Socket\ConnectorInterface;

class Mqtt implements PersistentInterface
{
    use PersistentTrait;

    public const REALTIME_CLIENT_TYPE = 'mqtt';

    /** @var EventEmitterInterface */
    protected $_target;

    /** @var ConnectorInterface */
    protected $_connector;

    /** @var AuthInterface */
    protected $_auth;

    /** @var DeviceInterface */
    protected $_device;

    /** @var ExperimentsInterface */
    protected $_experiments;

    /** @var LoopInterface */
    protected $_loop;

    /** @var array */
    protected $_additionalOptions;

    /** @var TimerInterface */
    protected $_keepaliveTimer;

    /** @var bool */
    protected $_shutdown;

    /** @var LoggerInterface */
    protected $_logger;

    /** @var SubscriptionInterface[][] */
    protected $_subscriptions;

    /** @var ReactMqttClient */
    protected $_client;

    /** @var ParserInterface[] */
    protected $_parsers;

    /** @var HandlerInterface[] */
    protected $_handlers;

    /** @var Instagram */
    protected $_instagram;

    /** @var int */
    protected $_timeout = 0;

    /**
     * Constructor.
     *
     * @param EventEmitterInterface $target
     * @param ConnectorInterface    $connector
     * @param AuthInterface         $auth
     * @param DeviceInterface       $device
     * @param Instagram             $instagram
     * @param LoopInterface         $loop
     * @param LoggerInterface       $logger
     * @param array                 $additionalOptions Supported options:
     *                                                 - disable_presence
     *                                                 - datacenter
     */
    public function __construct(
        EventEmitterInterface $target,
        ConnectorInterface $connector,
        AuthInterface $auth,
        DeviceInterface $device,
        Instagram $instagram,
        LoopInterface $loop,
        LoggerInterface $logger,
        array $additionalOptions = []
    ) {
        $this->_target = $target;
        $this->_connector = $connector;
        $this->_auth = $auth;
        $this->_device = $device;
        $this->_loop = $loop;
        $this->_logger = $logger;
        $this->_additionalOptions = $additionalOptions;
        $this->_instagram = $instagram;

        $this->_subscriptions = [];

        $this->_loadExperiments($instagram);
        $this->_initSubscriptions();

        $this->_shutdown = false;
        $this->_client = $this->_getClient();

        $this->_parsers = [
            Mqtt\Topics::PUBSUB                => new Parser\SkywalkerParser(),
            Mqtt\Topics::SEND_MESSAGE_RESPONSE => new Parser\JsonParser(Handler\DirectHandler::MODULE),
            Mqtt\Topics::IRIS_SUB_RESPONSE     => new Parser\JsonParser(Handler\IrisHandler::MODULE),
            Mqtt\Topics::MESSAGE_SYNC          => new Parser\IrisParser(),
            Mqtt\Topics::REALTIME_SUB          => new Parser\GraphQlParser(),
            Mqtt\Topics::GRAPHQL               => new Parser\GraphQlParser(),
            Mqtt\Topics::REGION_HINT           => new Parser\RegionHintParser(),
        ];
        $this->_handlers = [
            Handler\DirectHandler::MODULE        => new Handler\DirectHandler($this->_target),
            Handler\LiveHandler::MODULE          => new Handler\LiveHandler($this->_target),
            Handler\IrisHandler::MODULE          => new Handler\IrisHandler($this->_target),
            Handler\PresenceHandler::MODULE      => new Handler\PresenceHandler($this->_target),
            Handler\RegionHintHandler::MODULE    => new Handler\RegionHintHandler($this->_target),
            Handler\ZeroProvisionHandler::MODULE => new Handler\ZeroProvisionHandler($this->_target),
        ];
    }

    /** {@inheritdoc} */
    public function getLoop()
    {
        return $this->_loop;
    }

    /** {@inheritdoc} */
    public function isActive()
    {
        return !$this->_shutdown;
    }

    /**
     * Add a subscription to the list.
     *
     * @param SubscriptionInterface $subscription
     */
    public function addSubscription(
        SubscriptionInterface $subscription
    ) {
        $this->_doAddSubscription($subscription, true);
    }

    /**
     * Remove a subscription from the list.
     *
     * @param SubscriptionInterface $subscription
     */
    public function removeSubscription(
        SubscriptionInterface $subscription
    ) {
        $this->_doRemoveSubscription($subscription, true);
    }

    /**
     * Set an additional option.
     *
     * @param string $option
     * @param mixed  $value
     */
    public function setAdditionalOption(
        $option,
        $value
    ) {
        $this->_additionalOptions[$option] = $value;
    }

    /**
     * Add a subscription to the list and send a command (optional).
     *
     * @param SubscriptionInterface $subscription
     * @param bool                  $sendCommand
     */
    protected function _doAddSubscription(
        SubscriptionInterface $subscription,
        $sendCommand
    ) {
        $topic = $subscription->getTopic();
        $id = $subscription->getId();

        // Check whether we already subscribed to it.
        if (isset($this->_subscriptions[$topic][$id])) {
            return;
        }

        // Add the subscription to the list.
        if (!isset($this->_subscriptions[$topic])) {
            $this->_subscriptions[$topic] = [];
        }
        $this->_subscriptions[$topic][$id] = $subscription;

        // Send a command when needed.
        if (!$sendCommand || $this->isConnected()) {
            return;
        }

        $this->_updateSubscriptions($topic, [$subscription], []);
    }

    /**
     * Remove a subscription from the list and send a command (optional).
     *
     * @param SubscriptionInterface $subscription
     * @param bool                  $sendCommand
     */
    protected function _doRemoveSubscription(
        SubscriptionInterface $subscription,
        $sendCommand
    ) {
        $topic = $subscription->getTopic();
        $id = $subscription->getId();

        // Check whether we are subscribed to it.
        if (!isset($this->_subscriptions[$topic][$id])) {
            return;
        }

        // Remove the subscription from the list.
        unset($this->_subscriptions[$topic][$id]);
        if (!count($this->_subscriptions[$topic])) {
            unset($this->_subscriptions[$topic]);
        }

        // Send a command when needed.
        if (!$sendCommand || $this->isConnected()) {
            return;
        }

        $this->_updateSubscriptions($topic, [], [$subscription]);
    }

    /**
     * Cancel a keepalive timer (if any).
     */
    protected function _cancelKeepaliveTimer()
    {
        if ($this->_keepaliveTimer !== null) {
            $this->_logger->debug('Existing keepalive timer has been canceled.');
            $this->_loop->cancelTimer($this->_keepaliveTimer);
            /*
            if ($this->_keepaliveTimer->isActive()) {
                $this->_logger->debug('Existing keepalive timer has been canceled.');
                $this->_keepaliveTimer->cancel();
            }
            */
            $this->_keepaliveTimer = null;
        }
    }

    /**
     * Set up a new keepalive timer.
     */
    protected function _setKeepaliveTimer()
    {
        $this->_cancelKeepaliveTimer();
        $keepaliveInterval = Mqtt\Config::MQTT_KEEPALIVE;
        $this->_logger->debug(sprintf('Setting up keepalive timer to %d seconds', $keepaliveInterval));
        $this->_keepaliveTimer = $this->_loop->addTimer($keepaliveInterval, function () {
            $this->_logger->info('Keepalive timer has been fired.');
            $this->_disconnect();
        });
    }

    /**
     * Try to establish a connection.
     */
    protected function _connect()
    {
        $this->_setReconnectTimer(function () {
            $this->_logger->info(sprintf('Connecting to %s:%d...', Mqtt\Config::DEFAULT_HOST, Mqtt\Config::DEFAULT_PORT));

            $timeout = ($this->_timeout === 0) ? Mqtt\Config::CONNECTION_TIMEOUT : $this->_timeout;

            return $this->_client->connect(Mqtt\Config::DEFAULT_HOST, Mqtt\Config::DEFAULT_PORT, new RealtimeConnection($this->_instagram), $timeout);
        });
    }

    /**
     * Perform first connection in a row.
     */
    public function start()
    {
        $this->_shutdown = false;
        $this->_reconnectInterval = 0;
        $this->_connect();
    }

    /**
     * Whether connection is established.
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->_client->isConnected();
    }

    /**
     * Disconnect from server.
     */
    protected function _disconnect()
    {
        $this->_cancelKeepaliveTimer();
        $this->_client->disconnect();
    }

    /**
     * Proxy for _disconnect().
     */
    public function stop()
    {
        $this->_logger->info('Shutting down...');
        $this->_shutdown = true;
        $this->_cancelReconnectTimer();
        $this->_disconnect();
    }

    /**
     * Sends a ping.
     */
    public function sendPing()
    {
        $this->_logger->info(sprintf('Sending ping to broker.'));
        $this->_client->sendPing();
    }

    /**
     * Send the command.
     *
     * @param CommandInterface $command
     *
     * @throws \LogicException
     */
    public function sendCommand(
        CommandInterface $command
    ) {
        if (!$this->isConnected()) {
            throw new \LogicException('Tried to send the command while offline.');
        }

        $this->_publish(
            $command->getTopic(),
            json_encode($command, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $command->getQosLevel()
        );
    }

    /**
     * Set timeout.
     *
     * @param int $timeout
     */
    public function setTimeout(
        $timeout
    ) {
        $this->_timeout = $timeout;
    }

    /**
     * Load the experiments.
     *
     * @param ExperimentsInterface $experiments
     */
    protected function _loadExperiments(
        ExperimentsInterface $experiments
    ) {
        $this->_experiments = $experiments;
    }

    protected function _initSubscriptions()
    {
        $subscriptionId = Signatures::generateUUID();
        // Set up PubSub topics.
        $liveSubscription = new LiveSubscription($this->_auth->getUserId());
        $this->_doAddSubscription($liveSubscription, false);

        // Direct subscription is always enabled.
        $this->_doAddSubscription(new DirectSubscription($this->_auth->getUserId()), false);

        // Set up GraphQL topics.
        $zeroProvisionSubscription = new ZeroProvisionSubscription($this->_auth->getDeviceId());
        $this->_doAddSubscription($zeroProvisionSubscription, false);
        $graphQlTypingSubscription = new DirectTypingSubscription($this->_auth->getUserId());
        $this->_doAddSubscription($graphQlTypingSubscription, false);

        $appPresenceSubscription = new AppPresenceSubscription($subscriptionId);
        if (empty($this->_additionalOptions['disable_presence'])) {
            $this->_doAddSubscription($appPresenceSubscription, false);
        } else {
            $this->_doRemoveSubscription($appPresenceSubscription, false);
        }
    }

    /**
     * Create a new MQTT client.
     *
     * @return ReactMqttClient
     */
    protected function _getClient()
    {
        $client = new ReactMqttClient($this->_connector, $this->_loop, null, new Mqtt\StreamParser());

        $client->on('error', function (\Exception $e) {
            $this->_target->emit('error', [$e]);
            // $this->_logger->error($e->getMessage());
        });
        $client->on('warning', function (\Exception $e) {
            $this->_logger->warning($e->getMessage());
        });
        $client->on('open', function () {
            $this->_logger->info('Connection has been established');
        });
        $client->on('close', function () {
            $this->_logger->info('Connection has been closed');
            $this->_cancelKeepaliveTimer();
            if (!$this->_reconnectInterval) {
                $this->_connect();
            }
        });
        $client->on('connect', function () {
            $this->_logger->info('Connected to a broker');
            $this->_setKeepaliveTimer();
            $this->_restoreAllSubscriptions();
            $this->_target->emit('connect');
        });
        $client->on('ping', function () {
            $this->_logger->debug('Ping flow completed');
            $this->_setKeepaliveTimer();
        });
        $client->on('publish', function () {
            $this->_logger->debug('Publish flow completed');
            $this->_setKeepaliveTimer();
        });
        $client->on('message', function (MqttMessage $message) {
            $this->_setKeepaliveTimer();
            $this->_onReceive($message);
        });
        $client->on('disconnect', function () {
            $this->_logger->info('Disconnected from broker');
        });

        return $client;
    }

    /**
     * Mass update subscriptions statuses.
     *
     * @param string                  $topic
     * @param SubscriptionInterface[] $subscribe
     * @param SubscriptionInterface[] $unsubscribe
     */
    protected function _updateSubscriptions(
        $topic,
        array $subscribe,
        array $unsubscribe
    ) {
        if (count($subscribe)) {
            $this->_logger->info(sprintf('Subscribing to %s topics %s', $topic, implode(', ', $subscribe)));
        }
        if (count($unsubscribe)) {
            $this->_logger->info(sprintf('Unsubscribing from %s topics %s', $topic, implode(', ', $subscribe)));
        }

        try {
            $this->sendCommand(new UpdateSubscriptions($topic, $subscribe, $unsubscribe));
        } catch (\Exception $e) {
            $this->_logger->warning($e->getMessage());
        }
    }

    /**
     * Subscribe to all topics.
     */
    protected function _restoreAllSubscriptions()
    {
        foreach ($this->_subscriptions as $topic => $subscriptions) {
            $this->_updateSubscriptions($topic, $subscriptions, []);
        }
    }

    /**
     * Unsubscribe from all topics.
     */
    protected function _removeAllSubscriptions()
    {
        foreach ($this->_subscriptions as $topic => $subscriptions) {
            $this->_updateSubscriptions($topic, [], $subscriptions);
        }
    }

    /**
     * Maps human readable topic to its identifier.
     *
     * @param string $topic
     *
     * @return string
     */
    protected function _mapTopic(
        $topic
    ) {
        if (array_key_exists($topic, Mqtt\Topics::TOPIC_TO_ID_MAP)) {
            $result = Mqtt\Topics::TOPIC_TO_ID_MAP[$topic];
            $this->_logger->debug(sprintf('Topic "%s" has been mapped to "%s"', $topic, $result));
        } else {
            $result = $topic;
            $this->_logger->warning(sprintf('Topic "%s" does not exist in the enum', $topic));
        }

        return $result;
    }

    /**
     * Maps topic ID to human readable name.
     *
     * @param string $topic
     *
     * @return string
     */
    protected function _unmapTopic(
        $topic
    ) {
        if (array_key_exists($topic, Mqtt\Topics::ID_TO_TOPIC_MAP)) {
            $result = Mqtt\Topics::ID_TO_TOPIC_MAP[$topic];
            $this->_logger->debug(sprintf('Topic ID "%s" has been unmapped to "%s"', $topic, $result));
        } else {
            $result = $topic;
            $this->_logger->warning(sprintf('Topic ID "%s" does not exist in the enum', $topic));
        }

        return $result;
    }

    /**
     * @param string $topic
     * @param string $payload
     * @param int    $qosLevel
     */
    protected function _publish(
        $topic,
        $payload,
        $qosLevel
    ) {
        $this->_logger->info(sprintf('Sending message "%s" to topic "%s"', $payload, $topic));
        $payload = zlib_encode($payload, ZLIB_ENCODING_DEFLATE, 9);
        // We need to map human readable topic name to its ID because of bandwidth saving.
        $topic = $this->_mapTopic($topic);
        $this->_client->publish(new DefaultMessage($topic, $payload, $qosLevel));
    }

    /**
     * Incoming message handler.
     *
     * @param MqttMessage $msg
     */
    protected function _onReceive(
        MqttMessage $msg
    ) {
        $payload = @zlib_decode($msg->getPayload());
        if ($payload === false) {
            $this->_logger->warning('Failed to inflate the payload');

            return;
        }
        $this->_handleMessage($this->_unmapTopic($msg->getTopic()), $payload);
    }

    /**
     * @param string $topic
     * @param string $payload
     */
    protected function _handleMessage(
        $topic,
        $payload
    ) {
        $this->_logger->debug(
            sprintf('Received a message from topic "%s"', $topic),
            [base64_encode($payload)]
        );
        if (!isset($this->_parsers[$topic])) {
            $this->_logger->warning(
                sprintf('No parser for topic "%s" found, skipping the message(s)', $topic),
                [base64_encode($payload)]
            );

            return;
        }

        try {
            $messages = $this->_parsers[$topic]->parseMessage($topic, $payload);
        } catch (\Exception $e) {
            $this->_logger->warning($e->getMessage(), [$topic, base64_encode($payload)]);

            return;
        }

        foreach ($messages as $message) {
            $module = $message->getModule();
            if (!isset($this->_handlers[$module])) {
                $this->_logger->warning(
                    sprintf('No handler for module "%s" found, skipping the message', $module),
                    [$message->getData()]
                );

                continue;
            }

            $this->_logger->info(
                sprintf('Processing a message for module "%s"', $module),
                [$message->getData()]
            );

            try {
                $this->_handlers[$module]->handleMessage($message);
            } catch (Handler\HandlerException $e) {
                $this->_logger->warning($e->getMessage(), [$message->getData()]);
            } catch (\Exception $e) {
                $this->_target->emit('warning', [$e]);
            }
        }
    }

    /** {@inheritdoc} */
    public function getLogger()
    {
        return $this->_logger;
    }
}
