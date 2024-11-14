<?php

namespace InstagramAPI\Middleware;

use GuzzleHttp\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ProxyHandler
{
    /**
     * The Instagram class instance we belong to.
     *
     * @var \InstagramAPI\Instagram
     */
    protected $_parent;

    /**
     * Optional callback functions.
     *
     * @var array
     */
    private $_callbacks;

    /**
     * Complete list of all supported callbacks.
     */
    public const SUPPORTED_CALLBACKS = [
        'onRequest',
        'onResponse',
        'onRejected',
    ];

    /**
     * Constructor.
     *
     * @param mixed $parent
     * @param array $callbacks
     */
    public function __construct(
        $parent,
        array $callbacks = []
    ) {
        $this->_parent = $parent;

        // Store any user-provided callbacks.
        $this->_callbacks = $callbacks;
    }

    /**
     * Internal: Triggers a callback.
     *
     * All callback functions are given the storage handler instance as their
     * one and only argument.
     *
     * @param string $cbName The name of the callback.
     * @param mixed  $data
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    protected function _triggerCallback(
        $cbName,
        $data = []
    ) {
        // Reject anything that isn't in our list of VALID callbacks.
        if (!in_array($cbName, self::SUPPORTED_CALLBACKS)) {
            return;
        }

        // Trigger the callback with a reference to our StorageHandler instance.
        if (isset($this->_callbacks[$cbName])) {
            try {
                $this->_callbacks[$cbName]($this->_parent, $data);
            } catch (\Exception $e) {
                // pass
            }
        }
    }

    /**
     * Middleware setup function.
     *
     * Called by Guzzle when it needs to add an instance of our middleware to
     * its stack. We simply return a callable which will process all requests.
     *
     * @param callable $handler
     *
     * @return callable
     */
    public function __invoke(
        callable $handler
    ) {
        return function (
            RequestInterface $request,
            array $options
        ) use ($handler) {
            $this->_triggerCallback('onRequest');

            return $handler($request, $options)->then(
                function (ResponseInterface $response) use ($request) {
                    $this->_triggerCallback('onResponse', [
                        'request'  => $request,
                        'response' => $response,
                    ]);

                    return $response;
                },
                function ($reason) use ($request) {
                    $this->_triggerCallback('onRejected', [
                        'request' => $request,
                        'reason'  => $reason,
                    ]);

                    return Promise\Create::rejectionFor($reason);
                }
            );
        };
    }
}
