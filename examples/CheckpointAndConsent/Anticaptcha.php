<?php

declare(strict_types=1);

/*
 * This file is part of PHP Instagram API.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * AntiCaptchaTaskProtocol interface.
 */
interface AntiCaptchaTaskProtocol
{
    public function getPostData();

    public function getTaskSolution();
}

/**
 * Anticaptcha.
 */
class Anticaptcha
{
    /**
     * Host.
     *
     * @var string
     */
    private $_host = 'api.anti-captcha.com';

    /**
     * Scheme.
     *
     * @var string
     */
    private $_scheme = 'https';

    /**
     * Client key.
     *
     * @var string
     */
    private $_clientKey;

    /**
     * Verbose mode.
     *
     * @var bool
     */
    private $_verboseMode = false;

    /**
     * Error message.
     *
     * @var string
     */
    private $_errorMessage;

    /**
     * Task ID.
     *
     * @var string
     */
    private $_taskId;

    /**
     * Task info.
     *
     * @var string
     */
    public $taskInfo;

    /**
     * Submit new task and receive tracking ID.
     *
     * @return bool
     */
    public function createTask()
    {
        $postData = [
            'clientKey' => $this->_clientKey,
            'task'      => $this->getPostData(),
        ];
        $submitResult = $this->jsonPostRequest('createTask', $postData);

        if ($submitResult === false) {
            $this->debout('API error', 'red');

            return false;
        }

        if ($submitResult->errorId === 0) {
            $this->_taskId = $submitResult->taskId;
            $this->debout(sprintf('created task with ID %s', $this->_taskId), 'yellow');

            return true;
        }
        $this->debout(sprintf('API error %s : $s', $submitResult->errorCode, $submitResult->errorDescription), 'red');
        $this->setErrorMessage($submitResult->errorDescription);

        return false;
    }

    /**
     * Waits for the result.
     *
     * @param int $maxSeconds    Max seconds.
     * @param int $currentSecond Current second.
     *
     * @return bool
     */
    public function waitForResult(
        $maxSeconds = 300,
        $currentSecond = 0
    ) {
        $postData = [
            'clientKey' => $this->_clientKey,
            'taskId'    => $this->_taskId,
        ];
        if ($currentSecond === 0) {
            $this->debout('waiting 5 seconds..');
            sleep(3);
        } else {
            sleep(1);
        }
        $this->debout('requesting task status');
        $postResult = $this->jsonPostRequest('getTaskResult', $postData);

        if ($postResult === false) {
            $this->debout('API error', 'red');

            return false;
        }

        $this->taskInfo = $postResult;

        if ($this->taskInfo->errorId === 0) {
            if ($this->taskInfo->status === 'processing') {
                $this->debout('task is still processing');
                // repeating attempt
                return $this->waitForResult($maxSeconds, $currentSecond + 1);
            }
            if ($this->taskInfo->status === 'ready') {
                $this->debout('task is complete', 'green');

                return true;
            }
            $this->setErrorMessage('unknown API status, update your software');

            return false;
        }
        $this->debout('API error {$this->taskInfo->errorCode} : {$this->taskInfo->errorDescription}', 'red');
        $this->setErrorMessage($this->taskInfo->errorDescription);

        return false;
    }

    /**
     * Get balance.
     *
     * @return string|bool
     */
    public function getBalance()
    {
        $postData = [
            'clientKey' => $this->_clientKey,
        ];
        $result = $this->jsonPostRequest('getBalance', $postData);
        if ($result === false) {
            $this->debout('API error', 'red');

            return false;
        }
        if ($result->errorId === 0) {
            return $result->balance;
        }

        return false;
    }

    /**
     * Send POST request and return response.
     *
     * @param string $methodName Method name.
     * @param array  $postData   POST data.
     *
     * @return array
     */
    public function jsonPostRequest(
        $methodName,
        $postData
    ) {
        $url = "{$this->_scheme}://{$this->_host}/{$methodName}";
        if ($this->_verboseMode) {
            echo "making request to {$url} with following payload:\n";
            print_r($postData);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        $postDataEncoded = json_encode($postData);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataEncoded);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json',
            'Content-Length: '.strlen($postDataEncoded),
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $result = curl_exec($ch);
        $curlError = curl_error($ch);

        if ($curlError !== '') {
            $this->debout('Network error: $curlError');

            return false;
        }
        curl_close($ch);

        return json_decode($result);
    }

    /**
     * Set verbose mode.
     *
     * @param bool $mode Verbose mode.
     */
    public function setVerboseMode(
        $mode
    ): void {
        $this->_verboseMode = $mode;
    }

    /**
     * Set debug message.
     *
     * @param string $message Message.
     * @param string $color   Color.
     */
    public function debout(
        $message,
        $color = 'white'
    ) {
        if (!$this->_verboseMode) {
            return false;
        }
        if ($color !== 'white' && $color !== '') {
            $CLIcolors = [
                'cyan'   => '0;36',
                'green'  => '0;32',
                'blue'   => '0;34',
                'red'    => '0;31',
                'yellow' => '1;33',
            ];

            $CLIMsg = "\033[".$CLIcolors[$color]."m{$message}\033[0m";
        } else {
            $CLIMsg = $message;
        }
        echo $CLIMsg."\n";
    }

    /**
     * Set error message.
     *
     * @param string $message Message.
     */
    public function setErrorMessage(
        $message
    ): void {
        $this->_errorMessage = $message;
    }

    /**
     * Get error message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    /**
     * Get task ID.
     *
     * @return string
     */
    public function getTaskId()
    {
        return $this->_taskId;
    }

    /**
     * Set task ID.
     *
     * @param string $taskId Task ID.
     */
    public function setTaskId(
        $taskId
    ): void {
        $this->_taskId = $taskId;
    }

    /**
     * Set host.
     *
     * @param string $host Host.
     */
    public function setHost(
        $host
    ): void {
        $this->_host = $host;
    }

    /**
     * Set scheme.
     *
     * @param string $scheme Scheme.
     */
    public function setScheme(
        $scheme
    ): void {
        $this->_scheme = $scheme;
    }

    /**
     * Set client access key, must be 32 bytes long.
     *
     * @param string $key
     */
    public function setKey(
        $key
    ): void {
        $this->_clientKey = $key;
    }
}
