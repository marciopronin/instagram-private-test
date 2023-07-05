<?php

namespace InstagramAPI;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Utils as GuzzleUtils;
use InstagramAPI\Exception\InstagramException;
use InstagramAPI\Exception\LoginRequiredException;
use InstagramAPI\Exception\ServerMessageThrower;
use InstagramAPI\Middleware\FakeCookies;
use InstagramAPI\Middleware\ZeroRating;
use LazyJsonMapper\Exception\LazyJsonMapperException;
use Psr\Http\Message\RequestInterface as HttpRequestInterface;
use Psr\Http\Message\ResponseInterface as HttpResponseInterface;

/**
 * This class handles core API network communication.
 *
 * WARNING TO CONTRIBUTORS: This class is a wrapper for the HTTP client, and
 * handles raw networking, cookies, HTTP requests and responses. Don't put
 * anything related to high level API functions (such as file uploads) here.
 * Most of the higher level code belongs in either the Request class or in the
 * individual endpoint functions.
 *
 * @author mgp25: Founder, Reversing, Project Leader (https://github.com/mgp25)
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class Client
{
    /**
     * How frequently we're allowed to auto-save the cookie jar, in seconds.
     *
     * @var int
     */
    const COOKIE_AUTOSAVE_INTERVAL = 45;

    /**
     * The Instagram class instance we belong to.
     *
     * @var \InstagramAPI\Instagram
     */
    protected $_parent;

    /**
     * What user agent to identify our client as.
     *
     * @var string
     */
    protected $_userAgent;

    /**
     * The SSL certificate verification behavior of requests.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#verify
     *
     * @var bool|string
     */
    protected $_verifySSL;

    /**
     * Proxy to use for all requests. Optional.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#proxy
     *
     * @var string|array|null
     */
    protected $_proxy;

    /**
     * Resolving host. Optional.
     *
     * @see https://curl.haxx.se/libcurl/c/CURLOPT_RESOLVE.html
     *
     * @var string|null
     */
    protected $_resolveHost;

    /**
     * Network interface override to use.
     *
     * Only works if Guzzle is using the cURL backend. But that's
     * almost always the case, on most PHP installations.
     *
     * @see http://php.net/curl_setopt CURLOPT_INTERFACE
     *
     * @var string|null
     */
    protected $_outputInterface;

    /**
     * @var \GuzzleHttp\Client
     */
    private $_guzzleClient;

    /**
     * @var \InstagramAPI\Middleware\FakeCookies
     */
    private $_fakeCookies;

    /**
     * @var \InstagramAPI\Middleware\ZeroRating
     */
    private $_zeroRating;

    /**
     * @var \GuzzleHttp\Cookie\CookieJar
     */
    private $_cookieJar;

    /**
     * The timestamp of when we last saved our cookie jar to disk.
     *
     * Used for automatically saving the jar after any API call, after enough
     * time has elapsed since our last save.
     *
     * @var int
     */
    private $_cookieJarLastSaved;

    /**
     * The flag to force cURL to reopen a fresh connection.
     *
     * @var bool
     */
    private $_resetConnection;

    /**
     * The most recent request processed.
     *
     * Used for debugging failed requests in exceptions without needing to
     * enable debug mode.
     *
     * @var Request
     */
    private $_lastRequest;

    /**
     * The flag will use same pigeon Timestamp.
     *
     * @var bool
     */
    private $_pigeonBatch;

    /**
     * The Pigeon Timestamp.
     *
     * @var float
     */
    private $_pigeonTimestamp;

    /**
     * The Pigeon Session ID.
     *
     * @var string
     */
    private $_pigeonSession;

    /**
     * Total time elapsed.
     *
     * @var string
     */
    public $totalTime = 0;

    /**
     * Total Bytes received.
     *
     * @var string
     */
    public $totalBytes = 0;

    /**
     * Bytes received in the latest response.
     *
     * @var string
     */
    public $bandwidthB = 0;

    /**
     * Time elapsed in the latest response.
     *
     * @var string
     */
    public $bandwidthM = 0;

    /**
     * IG WWW Claim.
     *
     * @var string
     */
    public $wwwClaim = '';

    /**
     * Direct Region Hint.
     *
     * @var string
     */
    protected $_directRegionHint = '';

    /**
     * SHBID.
     *
     * @var string
     */
    protected $_shbid = '';

    /**
     * SHBTS.
     *
     * @var string
     */
    protected $_shbts = '';

    /**
     * RUR.
     *
     * @var string
     */
    protected $_rur = '';

    /**
     * MID.
     *
     * @var string
     */
    protected $_mid = '';

    /**
     * Request ID for logger.
     *
     * @var int
     */
    protected $_requestId = 0;

    /**
     * Request UUID.
     *
     * @var string
     */
    protected $_requestUuid = '';

    /**
     * Flow User Counter.
     *
     * @var int
     */
    protected $_flowUserCounter = 0;

    /**
     * IG User Salt ID.
     *
     * @var int
     */
    protected $_ig_user_salt_ids = '';

    /**
     * Constructor.
     *
     * @param \InstagramAPI\Instagram $parent
     * @param array Options to be passed to the Guzzle Client.
     * @param mixed $options
     */
    public function __construct(
        $parent,
        $options = [])
    {
        $this->_parent = $parent;

        // Defaults.
        $this->_verifySSL = true;
        $this->_proxy = null;
        $this->_resolveHost = null;

        // Set Pigeon Session ID.
        $this->_pigeonSession = sprintf('%s-%s-0', 'UFS', Signatures::generateUUID());
        $this->_requestUuid = uniqid();

        // Create a default handler stack with Guzzle's auto-selected "best
        // possible transfer handler for the user's system", and with all of
        // Guzzle's default middleware (cookie jar support, etc).
        $stack = HandlerStack::create();

        // Create our cookies middleware and add it to the stack.
        $this->_fakeCookies = new FakeCookies();
        $stack->push($this->_fakeCookies, 'fake_cookies');

        $this->_zeroRating = new ZeroRating($parent);
        $stack->push($this->_zeroRating, 'zero_rewrite');

        // Default request options (immutable after client creation).
        $defaultOptions = [
            'handler'         => $stack, // Our middleware is now injected.
            'allow_redirects' => [
                'max' => 8, // Allow up to eight redirects (that's plenty).
            ],
            'connect_timeout' => 30.0, // Give up trying to connect after 30s.
            'decode_content'  => true, // Decode gzip/deflate/etc HTTP responses.
            'timeout'         => 240.0, // Maximum per-request time (seconds).
            // Tells Guzzle to stop throwing exceptions on non-"2xx" HTTP codes,
            // thus ensuring that it only triggers exceptions on socket errors!
            // We'll instead MANUALLY be throwing on certain other HTTP codes.
            'http_errors'     => false,
        ];

        $options = array_merge($defaultOptions, $options);
        // In case user replaces handler by mistake.
        $options['handler'] = $stack; // Our middleware is now injected.

        if (\InstagramAPI\Instagram::$curlDebug === true) {
            $options['debug'] = true;
        }

        $this->_guzzleClient = new GuzzleClient($options);

        $this->_resetConnection = false;
    }

    /**
     * Resets certain Client settings via the current Settings storage.
     *
     * Used whenever we switch active user, to configure our internal state.
     *
     * @param bool $resetCookieJar (optional) Whether to clear current cookies.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    public function updateFromCurrentSettings(
        $resetCookieJar = false)
    {
        // Update our internal client state from the new user's settings.
        $this->_userAgent = $this->_parent->device->getUserAgent();
        $this->loadCookieJar($resetCookieJar);

        // Verify that the jar contains a non-expired csrftoken for the API
        // domain. Instagram gives us a 1-year csrftoken whenever we log in.
        // If it's missing, we're definitely NOT logged in! But even if all of
        // these checks succeed, the cookie may still not be valid. It's just a
        // preliminary check to detect definitely-invalid session cookies!
        if ($this->_parent->settings->get('authorization_header') !== null) {
            $authorizationData = json_decode(base64_decode(explode(':', $this->_parent->settings->get('authorization_header'))[2]), true);
        }
        if (!isset($authorizationData['sessionid'])) {
            if ($this->getToken() === null) {
                $this->_parent->isMaybeLoggedIn = false;
            }
        }

        // Load rewrite rules (if any).
        $this->zeroRating()->update($this->_parent->settings->getRewriteRules());
    }

    /**
     * Loads all cookies via the current Settings storage.
     *
     * @param bool $resetCookieJar (optional) Whether to clear current cookies.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    public function loadCookieJar(
        $resetCookieJar = false)
    {
        // Mark any previous cookie jar for garbage collection.
        $this->_cookieJar = null;

        // Delete all current cookies from the storage if this is a reset.
        if ($resetCookieJar) {
            $this->_parent->settings->setCookies('');
        }

        // Get all cookies for the currently active user.
        $cookieData = $this->_parent->settings->getCookies();

        // Attempt to restore the cookies, otherwise create a new, empty jar.
        $restoredCookies = is_string($cookieData) ? @json_decode($cookieData, true) : null;
        if (!is_array($restoredCookies)) {
            $restoredCookies = [];
        }

        // Memory-based cookie jar which must be manually saved later.
        $this->_cookieJar = new CookieJar(false, $restoredCookies);

        // Reset the "last saved" timestamp to the current time to prevent
        // auto-saving the cookies again immediately after this jar is loaded.
        $this->_cookieJarLastSaved = time();
    }

    /**
     * Retrieve Pigeon Session ID.
     *
     * @return string
     */
    public function getPigeonSession()
    {
        return $this->_pigeonSession;
    }

    /**
     * Retrieve the CSRF token from the current cookie jar.
     *
     * Note that Instagram gives you a 1-year token expiration timestamp when
     * you log in. But if you log out, they set its timestamp to "0" which means
     * that the cookie is "expired" and invalid. We ignore token cookies if they
     * have been logged out, or if they have expired naturally.
     *
     * @return string|null The token if found and non-expired, otherwise NULL.
     */
    public function getToken()
    {
        $cookie = $this->getCookie('csrftoken', 'i.instagram.com');
        if ($cookie === null || $cookie->getValue() === '') {
            return null;
        }

        return $cookie->getValue();
    }

    /**
     * Retrieve the MID token from the current cookie jar.
     *
     * @return string|null The MID if found and non-expired, otherwise NULL.
     */
    public function getMid()
    {
        $cookie = $this->getCookie('mid', 'i.instagram.com');
        if ($cookie === null || $cookie->getValue() === '') {
            return null;
        }

        return $cookie->getValue();
    }

    /**
     * Searches for a specific cookie in the current jar.
     *
     * @param string      $name   The name of the cookie.
     * @param string|null $domain (optional) Require a specific domain match.
     * @param string|null $path   (optional) Require a specific path match.
     *
     * @return \GuzzleHttp\Cookie\SetCookie|null A cookie if found and non-expired, otherwise NULL.
     */
    public function getCookie(
        $name,
        $domain = null,
        $path = null)
    {
        $foundCookie = null;
        if ($this->_cookieJar instanceof CookieJar) {
            /** @var SetCookie $cookie */
            foreach ($this->_cookieJar->getIterator() as $cookie) {
                if ($cookie->getName() === $name
                    && !$cookie->isExpired()
                    && ($domain === null || $cookie->matchesDomain($domain))
                    && ($path === null || $cookie->matchesPath($path))) {
                    // Loop-"break" is omitted intentionally, because we might
                    // have more than one cookie with the same name, so we will
                    // return the LAST one. This is necessary because Instagram
                    // has changed their cookie domain from `i.instagram.com` to
                    // `.instagram.com` and we want the *most recent* cookie.
                    // Guzzle's `CookieJar::setCookie()` always places the most
                    // recently added/modified cookies at the *end* of array.
                    $foundCookie = $cookie;
                }
            }
        }

        return $foundCookie;
    }

    /**
     * Gives you all cookies in the Jar encoded as a JSON string.
     *
     * This allows custom Settings storages to retrieve all cookies for saving.
     *
     * @throws \InvalidArgumentException If the JSON cannot be encoded.
     *
     * @return string
     */
    public function getCookieJarAsJSON()
    {
        if (!$this->_cookieJar instanceof CookieJar) {
            return '[]';
        }

        // Gets ALL cookies from the jar, even temporary session-based cookies.
        $cookies = $this->_cookieJar->toArray();

        // Throws if data can't be encoded as JSON (will never happen).
        $jsonStr = \GuzzleHttp\json_encode($cookies);

        return $jsonStr;
    }

    /**
     * Tells current settings storage to store cookies if necessary.
     *
     * NOTE: This Client class is NOT responsible for calling this function!
     * Instead, our parent "Instagram" instance takes care of it and saves the
     * cookies "onCloseUser", so that cookies are written to storage in a
     * single, efficient write when the user's session is finished. We also call
     * it during some important function calls such as login/logout. Client also
     * automatically calls it when enough time has elapsed since last save.
     *
     * @throws \InvalidArgumentException                 If the JSON cannot be encoded.
     * @throws \InstagramAPI\Exception\SettingsException
     */
    public function saveCookieJar()
    {
        // Tell the settings storage to persist the latest cookies.
        $cookies = json_decode($this->getCookieJarAsJSON(), true);
        $newCookies = [];
        foreach ($cookies as $cookie) {
            if ($cookie['Value'] !== '""') {
                $newCookies[] = $cookie;
            }
        }

        $newCookies = json_encode($newCookies);
        $this->_parent->settings->setCookies($newCookies);
        // Reset the "last saved" timestamp to the current time.
        $this->_cookieJarLastSaved = time();
    }

    /**
     * Controls the SSL verification behavior of the Client.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#verify
     *
     * @param bool|string $state TRUE to verify using PHP's default CA bundle,
     *                           FALSE to disable SSL verification (this is
     *                           insecure!), String to verify using this path to
     *                           a custom CA bundle file.
     */
    public function setVerifySSL(
        $state)
    {
        $this->_verifySSL = $state;
    }

    /**
     * Gets the current SSL verification behavior of the Client.
     *
     * @return bool|string
     */
    public function getVerifySSL()
    {
        return $this->_verifySSL;
    }

    /**
     * Set the proxy to use for requests.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#proxy
     *
     * @param string|array|null $value String or Array specifying a proxy in
     *                                 Guzzle format, or NULL to disable proxying.
     */
    public function setProxy(
        $value)
    {
        $this->_proxy = $value;
        $this->_resetConnection = true;
    }

    /**
     * Gets the current proxy used for requests.
     *
     * @return string|array|null
     */
    public function getProxy()
    {
        return $this->_proxy;
    }

    /**
     * Set the resolving host.
     *
     * @see https://curl.haxx.se/libcurl/c/CURLOPT_RESOLVE.html
     *
     * @param string $value String specifying the host used for resolving.
     */
    public function setResolveHost(
        $value)
    {
        $this->_resolveHost = $value;
    }

    /**
     * Gets the current resolving host.
     *
     * @return string
     */
    public function getResolveHost()
    {
        return $this->_resolveHost;
    }

    /**
     * Sets the network interface override to use.
     *
     * Only works if Guzzle is using the cURL backend. But that's
     * almost always the case, on most PHP installations.
     *
     * @see http://php.net/curl_setopt CURLOPT_INTERFACE
     *
     * @param string|null $value Interface name, IP address or hostname, or NULL to
     *                           disable override and let Guzzle use any interface.
     */
    public function setOutputInterface(
        $value)
    {
        $this->_outputInterface = $value;
        $this->_resetConnection = true;
    }

    /**
     * Gets the current network interface override used for requests.
     *
     * @return string|null
     */
    public function getOutputInterface()
    {
        return $this->_outputInterface;
    }

    /**
     * Output debugging information.
     *
     * @param string                $method        "GET" or "POST".
     * @param string                $url           The URL or endpoint used for the request.
     * @param string|null           $uploadedBody  What was sent to the server. Use NULL to
     *                                             avoid displaying it.
     * @param int|null              $uploadedBytes How many bytes were uploaded. Use NULL to
     *                                             avoid displaying it.
     * @param HttpResponseInterface $response      The Guzzle response object from the request.
     * @param string                $responseBody  The actual text-body reply from the server.
     * @param bool                  $debug         CLI Debug.
     */
    protected function _printDebug(
        $method,
        $url,
        $uploadedBody,
        $uploadedBytes,
        HttpResponseInterface $response,
        $responseBody,
        $debug)
    {
        $path = Debug::$debugLogPath;
        if ($this->_parent->settings->getStorage() instanceof \InstagramAPI\Settings\Storage\File) {
            if ($path === null) {
                $path = $this->_parent->settings->getUserPath($this->_parent->username);
            }
        }
        Debug::printRequest($method, $url, $path, $debug);

        // Display the data body that was uploaded, if provided for debugging.
        // NOTE: Only provide this from functions that submit meaningful BODY data!
        if (is_string($uploadedBody)) {
            Debug::printPostData($uploadedBody, $path, $debug);
        }

        // Display the number of bytes uploaded in the data body, if provided for debugging.
        // NOTE: Only provide this from functions that actually upload files!
        if ($uploadedBytes !== null) {
            Debug::printUpload(Utils::formatBytes($uploadedBytes), $path, $debug);
        }

        // Display the number of bytes received from the response, and status code.
        if ($response->hasHeader('x-encoded-content-length')) {
            $bytes = Utils::formatBytes((int) $response->getHeaderLine('x-encoded-content-length'));
        } elseif ($response->hasHeader('Content-Length')) {
            $bytes = Utils::formatBytes((int) $response->getHeaderLine('Content-Length'));
        } else {
            $bytes = 0;
        }
        Debug::printHttpCode($response->getStatusCode(), $bytes, $path, $debug);

        // Display the actual API response body.
        Debug::printResponse($responseBody, $this->_parent->truncatedDebug, $path, $debug);
    }

    /**
     * Maps a server response onto a specific kind of result object.
     *
     * The result is placed directly inside `$responseObject`.
     *
     * @param Response              $responseObject An instance of a class object whose
     *                                              properties to fill with the response.
     * @param string                $rawResponse    A raw JSON response string
     *                                              from Instagram's server.
     * @param HttpResponseInterface $httpResponse   HTTP response object.
     * @param bool                  $silentFail     Silent fail flag.
     *
     * @throws InstagramException In case of invalid or failed API response.
     */
    public function mapServerResponse(
        Response $responseObject,
        $rawResponse,
        HttpResponseInterface $httpResponse,
        $silentFail)
    {
        // Attempt to decode the raw JSON to an array.
        // Important: Special JSON decoder which handles 64-bit numbers!
        $jsonArray = $this->api_body_decode($rawResponse, true);

        // If the server response is not an array, it means that JSON decoding
        // failed or some other bad thing happened. So analyze the HTTP status
        // code (if available) to see what really happened.
        if (!is_array($jsonArray)) {
            $httpStatusCode = $httpResponse !== null ? $httpResponse->getStatusCode() : null;
            switch ($httpStatusCode) {
                case 400:
                    throw new \InstagramAPI\Exception\BadRequestException('Invalid request options.');
                case 404:
                    throw new \InstagramAPI\Exception\NotFoundException('Requested resource does not exist.');
                default:
                    throw new \InstagramAPI\Exception\EmptyResponseException('No response from server. Either a connection or configuration error.');
            }
        }

        // Perform mapping of all response properties.
        try {
            // Assign the new object data. Only throws if custom _init() fails.
            // NOTE: False = assign data without automatic analysis.
            $responseObject->assignObjectData($jsonArray, false); // Throws.

            // Use API developer debugging? We'll throw if class lacks property
            // definitions, or if they can't be mapped as defined in the class
            // property map. But we'll ignore missing properties in our custom
            // UnpredictableKeys containers, since those ALWAYS lack keys. ;-)
            if ($this->_parent->apiDeveloperDebug) {
                // Perform manual analysis (so that we can intercept its analysis result).
                $analysis = $responseObject->exportClassAnalysis(); // Never throws.

                // Remove all "missing_definitions" errors for UnpredictableKeys containers.
                // NOTE: We will keep any "bad_definitions" errors for them.
                foreach ($analysis->missing_definitions as $className => $x) {
                    if (strpos($className, '\\Response\\Model\\UnpredictableKeys\\') !== false) {
                        unset($analysis->missing_definitions[$className]);
                    }
                }

                // If any problems remain after that, throw with all combined summaries.
                if ($analysis->hasProblems()) {
                    throw new LazyJsonMapperException(
                        $analysis->generateNiceSummariesAsString()
                    );
                }
            }
        } catch (LazyJsonMapperException $e) {
            // Since there was a problem, let's help our developers by
            // displaying the server's JSON data in a human-readable format,
            // which makes it easy to see the structure and necessary changes
            // and speeds up the job of updating responses and models.
            try {
                // Decode to stdClass to properly preserve empty objects `{}`,
                // otherwise they would appear as empty `[]` arrays in output.
                // NOTE: Large >32-bit numbers will be transformed into strings,
                // which helps us see which numeric values need "string" type.
                $jsonObject = $this->api_body_decode($rawResponse, false);
                if (is_object($jsonObject)) {
                    $prettyJson = @json_encode(
                        $jsonObject,
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                    );
                    if ($prettyJson !== false) {
                        Debug::printResponse(
                            'Human-Readable Response:'.PHP_EOL.$prettyJson,
                            false // Not truncated.
                        );
                    }
                }
            } catch (\Exception $e) {
                // Ignore errors.
            }

            // Exceptions will only be thrown if API developer debugging is
            // enabled and finds a problem. Either way, we should re-wrap the
            // exception to our native type instead. The message gives enough
            // details and we don't need to know the exact Lazy sub-exception.
            throw new InstagramException($e->getMessage());
        }

        // Save the HTTP response object as the "getHttpResponse()" value.
        $responseObject->setHttpResponse($httpResponse);

        // Throw an exception if the API response was unsuccessful.
        // NOTE: It will contain the full server response object too, which
        // means that the user can look at the full response details via the
        // exception itself.
        if (!$responseObject->isOk() || ($responseObject->hasStepName() && $responseObject->getStepName()) || ($responseObject->hasEntryData())) {
            if ($responseObject instanceof \InstagramAPI\Response\DirectSendItemResponse && $responseObject->getPayload() !== null) {
                if (is_array($responseObject->getPayload())) {
                    $message = $responseObject->getPayload()['message'];
                } else {
                    $message = $responseObject->getPayload()->getMessage();
                }
            } else {
                $message = $responseObject->getMessage();
            }

            if ($silentFail !== true) {
                try {
                    ServerMessageThrower::autoThrow(
                        get_class($responseObject),
                        $message,
                        $responseObject,
                        $httpResponse
                    );
                } catch (LoginRequiredException $e) {
                    // Instagram told us that our session is invalid (that we are
                    // not logged in). Update our cached "logged in?" state. This
                    // ensures that users with various retry-algorithms won't hammer
                    // their server. When this flag is false, ALL further attempts
                    // at AUTHENTICATED requests will be aborted by our library.
                    $this->_parent->isMaybeLoggedIn = false;
                    $this->_parent->settings->set('mid', '');
                    $this->_parent->settings->set('rur', '');
                    $this->_parent->settings->set('www_claim', '');
                    //$this->_parent->settings->set('account_id', '');
                    $this->_parent->settings->set('authorization_header', 'Bearer IGT:2:'); // Header won't be added into request until a new authorization is obtained.
                    //$this->_parent->account_id = null;

                    throw $e; // Re-throw.
                }
            }
        }
    }

    /**
     * Helper which builds in the most important Guzzle options.
     *
     * Takes care of adding all critical options that we need on every request.
     * Such as cookies and the user's proxy. But don't call this function
     * manually. It's automatically called by _guzzleRequest()!
     *
     * @param array $guzzleOptions  The options specific to the current request.
     * @param bool  $disableCookies Disable cookies.
     *
     * @return array A guzzle options array.
     */
    protected function _buildGuzzleOptions(
        array $guzzleOptions = [],
        $disableCookies = false)
    {
        $curlOptions = [];

        if (\InstagramAPI\Instagram::$disableHttp2 === false) {
            $curlOptions = [
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_2_0,
            ];
        }

        if ($this->_resolveHost !== null) {
            $curlOptions[CURLOPT_RESOLVE] = is_array($this->_resolveHost) ? $this->_resolveHost : [$this->_resolveHost];
        }

        $criticalOptions = [
            'cookies' => (($this->_cookieJar instanceof CookieJar && !$disableCookies) ? $this->_cookieJar : false),
            'verify'  => $this->_verifySSL,
            'proxy'   => ($this->_proxy !== null ? $this->_proxy : null),
            'curl'    => $curlOptions,
        ];

        // Critical options always overwrite identical keys in regular opts.
        // This ensures that we can't screw up the proxy/verify/cookies.
        $finalOptions = array_merge_recursive($guzzleOptions, $criticalOptions);

        // Now merge any specific Guzzle cURL-backend overrides. We must do this
        // separately since it's in an associative array and we can't just
        // overwrite that whole array in case the caller had curl options.
        if (!array_key_exists('curl', $finalOptions)) {
            $finalOptions['curl'] = [];
        }

        // Add their network interface override if they want it.
        // This option MUST be non-empty if set, otherwise it breaks cURL.
        if (is_string($this->_outputInterface) && $this->_outputInterface !== '') {
            $finalOptions['curl'][CURLOPT_INTERFACE] = $this->_outputInterface;
        }
        if ($this->_resetConnection) {
            $finalOptions['curl'][CURLOPT_FRESH_CONNECT] = true;
            $this->_resetConnection = false;
        }

        return $finalOptions;
    }

    /**
     * Wraps Guzzle's request and adds special error handling and options.
     *
     * Automatically throws exceptions on certain very serious HTTP errors. And
     * re-wraps all Guzzle errors to our own internal exceptions instead. You
     * must ALWAYS use this (or _apiRequest()) instead of the raw Guzzle Client!
     * However, you can never assume the server response contains what you
     * wanted. Be sure to validate the API reply too, since Instagram's API
     * calls themselves may fail with a JSON message explaining what went wrong.
     *
     * WARNING: This is a semi-lowlevel handler which only applies critical
     * options and HTTP connection handling! Most functions will want to call
     * _apiRequest() instead. An even higher-level handler which takes care of
     * debugging, server response checking and response decoding!
     *
     * @param HttpRequestInterface $request       HTTP request to send.
     * @param array                $guzzleOptions Extra Guzzle options for this request.
     *
     * @throws \InstagramAPI\Exception\NetworkException                For any network/socket related errors.
     * @throws \InstagramAPI\Exception\ThrottledException              When we're throttled by server.
     * @throws \InstagramAPI\Exception\RequestHeadersTooLargeException When request is too large.
     *
     * @return HttpResponseInterface
     */
    protected function _guzzleRequest(
        HttpRequestInterface $request,
        array $guzzleOptions = [])
    {
        $disableCookies = ($request->getUri()->getPath() === '/logging_client_events' || $request->getUri()->getPath() === '/challenge/') ? true : false;

        // Add critically important options for authenticating the request.
        $guzzleOptions = $this->_buildGuzzleOptions($guzzleOptions, $disableCookies);

        // Attempt the request. Will throw in case of socket errors!
        $retry = 0;
        do {
            $exp = false;

            try {
                $response = $this->_guzzleClient->send($request, $guzzleOptions);
            } catch (\Exception $e) {
                $exp = true;
                // Re-wrap Guzzle's exception using our own NetworkException.
                if ($retry === ($this->_parent->retriesOnNetworkFailure - 1) || !\InstagramAPI\Instagram::$retryOnNetworkException) {
                    throw new \InstagramAPI\Exception\NetworkException($e);
                }
            }
            if ($exp === false) {
                break;
            }
            $retry++;
            sleep(5);
        } while (\InstagramAPI\Instagram::$retryOnNetworkException && $retry < $this->_parent->retriesOnNetworkFailure);

        // Detect very serious HTTP status codes in the response.
        $httpCode = $response->getStatusCode();
        switch ($httpCode) {
        case 429: // "429 Too Many Requests"
            throw new \InstagramAPI\Exception\ThrottledException('Throttled by Instagram because of too many API requests.');
            break;
        case 431: // "431 Request Header Fields Too Large"
            throw new \InstagramAPI\Exception\RequestHeadersTooLargeException('The request start-line and/or headers are too large to process.');
            break;
        // WARNING: Do NOT detect 404 and other higher-level HTTP errors here,
        // since we catch those later during steps like mapServerResponse()
        // and autoThrow. This is a warning to future contributors!
        }

        // We'll periodically auto-save our cookies at certain intervals. This
        // complements the "onCloseUser" and "login()/logout()" force-saving.
        if ((time() - $this->_cookieJarLastSaved) > self::COOKIE_AUTOSAVE_INTERVAL) {
            $this->saveCookieJar();
        }

        // The response may still have serious but "valid response" errors, such
        // as "400 Bad Request". But it's up to the CALLER to handle those!
        return $response;
    }

    /**
     * Internal wrapper around _guzzleRequest().
     *
     * This takes care of many common additional tasks needed by our library,
     * so you should try to always use this instead of the raw _guzzleRequest()!
     *
     * Available library options are:
     * - 'noDebug': Can be set to TRUE to forcibly hide debugging output for
     *   this request. The user controls debugging globally, but this is an
     *   override that prevents them from seeing certain requests that you may
     *   not want to trigger debugging (such as perhaps individual steps of a
     *   file upload process). However, debugging SHOULD be allowed in MOST cases!
     *   So only use this feature if you have a very good reason.
     * - 'debugUploadedBody': Set to TRUE to make debugging display the data that
     *   was uploaded in the body of the request. DO NOT use this if your function
     *   uploaded binary data, since printing those bytes would kill the terminal!
     * - 'debugUploadedBytes': Set to TRUE to make debugging display the size of
     *   the uploaded body data. Should ALWAYS be TRUE when uploading binary data.
     *
     * @param HttpRequestInterface $request        HTTP request to send.
     * @param array                $guzzleOptions  Extra Guzzle options for this request.
     * @param array                $libraryOptions Additional options for controlling Library features
     *                                             such as the debugging output.
     *
     * @throws \InstagramAPI\Exception\NetworkException   For any network/socket related errors.
     * @throws \InstagramAPI\Exception\ThrottledException When we're throttled by server.
     *
     * @return HttpResponseInterface
     */
    protected function _apiRequest(
        HttpRequestInterface $request,
        array $guzzleOptions = [],
        array $libraryOptions = [])
    {
        $requestId = $this->_getRequestId();

        if ($this->_parent->logger !== null) {
            $this->_parent->logger->info('request',
                [
                    'uri'        => $this->_zeroRating->rewrite((string) $request->getUri()),
                    'request'    => (string) $request->getBody(),
                    'request_id' => $requestId,
                ]);
        }

        // Perform the API request and retrieve the raw HTTP response body.
        $guzzleResponse = $this->_guzzleRequest($request, $guzzleOptions);

        if ($this->_parent->logger !== null) {
            $this->_parent->logger->info('response',
                [
                    'uri'        => $this->_zeroRating->rewrite((string) $request->getUri()),
                    'response'   => (string) $guzzleResponse->getBody(),
                    'request_id' => $requestId,
                ]);
        }

        // Debugging (must be shown before possible decoding error).
        if ($this->_parent->debug && (!isset($libraryOptions['noDebug']) || !$libraryOptions['noDebug']) || \InstagramAPI\Debug::$debugLog) {
            // Determine whether we should display the contents of the UPLOADED body.
            if (isset($libraryOptions['debugUploadedBody']) && $libraryOptions['debugUploadedBody']) {
                $uploadedBody = (string) $request->getBody();
                if (!strlen($uploadedBody)) {
                    $uploadedBody = null;
                }
            } else {
                $uploadedBody = null; // Don't display.
            }

            // Determine whether we should display the size of the UPLOADED body.
            if (isset($libraryOptions['debugUploadedBytes']) && $libraryOptions['debugUploadedBytes']) {
                // Calculate the uploaded bytes by looking at request's body size, if it exists.
                $uploadedBytes = $request->getBody()->getSize();
            } else {
                $uploadedBytes = null; // Don't display.
            }

            if (in_array($request->getUri()->getPath(), Constants::ZR_EXCLUSION)) {
                $uri = $request->getUri();
            } else {
                $uri = $this->_zeroRating->rewrite((string) $request->getUri());
            }

            $this->_printDebug(
                $request->getMethod(),
                $uri,
                $uploadedBody,
                $uploadedBytes,
                $guzzleResponse,
                (string) $guzzleResponse->getBody(),
                $this->_parent->debug);
        }

        return $guzzleResponse;
    }

    /**
     * Perform an Instagram API call.
     *
     * @param HttpRequestInterface $request       HTTP request to send.
     * @param array                $guzzleOptions Extra Guzzle options for this request.
     *
     * @throws InstagramException
     *
     * @return HttpResponseInterface
     */
    public function api(
        HttpRequestInterface $request,
        array $guzzleOptions = [])
    {
        $headers = [
                        'set_headers' => [
                            // Keep the API's HTTPS connection alive in Guzzle for future
                            // re-use, to greatly speed up all further queries after this.
                            //'Connection'       => 'close',
                            'Accept-Encoding'  => Constants::ACCEPT_ENCODING,
                            'Accept-Language'  => $this->_parent->getAcceptLanguage(),
                        ],
                    ];

        if ($request->getUri()->getHost() !== parse_url(Constants::GRAPH_API_URL, PHP_URL_HOST)) {
            if ($this->_parent->account_id !== null || strpos($request->getUri(), 'bloks/apps/com.bloks.www.bloks.caa.login.async.send_login_request') !== false) {
                $headers['set_headers']['X-Pigeon-Session-Id'] = $this->getPigeonSession();
            }
            $headers['set_headers']['X-Pigeon-Rawclienttime'] = $this->_getPigeonRawClientTime();

            if ($this->_parent->settings->get('rur') !== null) {
                $this->_rur = $this->_parent->settings->get('rur');
            }
            if ($this->_parent->settings->get('mid') !== null) {
                $this->_mid = $this->_parent->settings->get('mid');
            }
            if ($this->_parent->settings->get('shbid') !== null) {
                $this->_shbid = $this->_parent->settings->get('shbid');
            }
            if ($this->_parent->settings->get('shbts') !== null) {
                $this->_shbts = $this->_parent->settings->get('shbts');
            }

            $this->_directRegionHint = ($this->_parent->settings->get('direct_region') !== null) ? $this->_parent->settings->get('direct_region') : '';
            $this->wwwClaim = ($this->_parent->settings->get('www_claim') !== null) ? $this->_parent->settings->get('www_claim') : '0';

            if ($this->totalTime !== 0 && !empty($this->bandwidthB)) {
                $headers['set_headers']['X-IG-Bandwidth-Speed-KBPS'] = ($this->totalBytes / $this->totalTime + $this->bandwidthB / $this->bandwidthM) / 2;
            } else {
                $headers['set_headers']['X-IG-Bandwidth-Speed-KBPS'] = '-1.000';
            }

            if (($this->_parent->settings->get('authorization_header') !== null) && ($this->_parent->settings->get('authorization_header') !== 'Bearer IGT:2:') && ($this->_parent->isSessionless !== true)) {
                $headers['set_headers']['Authorization'] = $this->_parent->settings->get('authorization_header');
            }

            if ($this->wwwClaim !== '') {
                $headers['set_headers']['X-IG-WWW-Claim'] = $this->wwwClaim;
            } else {
                $headers['set_headers']['X-IG-WWW-Claim'] = 0;
            }

            if ($this->_shbid !== '') {
                $headers['set_headers']['IG-U-SHBID'] = $this->_shbid;
            }

            if ($this->_shbts !== '') {
                $headers['set_headers']['IG-U-SHBTS'] = $this->_shbts;
            }

            if ($this->_parent->account_id !== null && ($this->_parent->isSessionless !== true)) {
                $headers['set_headers']['IG-U-DS-USER-ID'] = $this->_parent->account_id;
            }

            if ($this->_rur !== '' && ($this->_parent->isSessionless !== true)) {
                $headers['set_headers']['IG-U-RUR'] = $this->_rur;
            }

            if ($this->_directRegionHint !== '') {
                $headers['set_headers']['IG-U-IG-DIRECT-REGION-HINT'] = $this->_directRegionHint;
            }

            if ($this->_mid !== '') {
                $headers['set_headers']['X-MID'] = $this->_mid;
            }

            $headers['set_headers']['X-IG-Bandwidth-TotalBytes-B'] = strval($this->totalBytes);
            $headers['set_headers']['X-IG-Bandwidth-TotalTime-MS'] = strval($this->totalTime);
            //$headers['set_headers']['X-MID'] = $this->getMid();

            if (strpos($request->getUri(), 'notifications/badge') !== false) {
                $this->_parent->settings->set('salt_ids', $this->generateNewFlowId(1061163349));
            }

            if (strpos($request->getUri(), 'media/configure') !== false) {
                $this->_parent->settings->set('salt_ids', $this->generateFlowId(1061163349, 1));
            }

            if ($this->_parent->settings->get('salt_ids') !== null) {
                $headers['set_headers']['X-IG-SALT-IDS'] = $this->_parent->settings->get('salt_ids');
            }
        }

        $userAgent = $request->getHeader('User-Agent');

        if (!empty($userAgent)) {
            $headers['set_headers']['User-Agent'] = $userAgent;
        } else {
            $headers['set_headers']['User-Agent'] = $this->_userAgent;
        }

        $headers['set_headers'] = $this->_orderHeaders($headers['set_headers'] + $request->getHeaders());

        // Set up headers that are required for every request.
        $request = GuzzleUtils::modifyRequest($request, $headers);

        // Check the Content-Type header for debugging.
        $contentType = $request->getHeader('Content-Type');
        $isFormData = count($contentType) && reset($contentType) === Constants::CONTENT_TYPE;

        $start = microtime(true);

        // Perform the API request.
        $response = $this->_apiRequest($request, $guzzleOptions, [
            'debugUploadedBody'  => $isFormData,
            'debugUploadedBytes' => !$isFormData,
        ]);

        $this->wwwClaim = $response->getHeaderLine('x-ig-set-www-claim');
        $this->_shbid = $response->getHeaderLine('ig-set-ig-u-shbid');
        $this->_shbts = $response->getHeaderLine('ig-set-ig-u-shbts');
        $this->_rur = $response->getHeaderLine('ig-set-ig-u-rur');
        $this->_directRegionHint = $response->getHeaderLine('ig-set-ig-u-ig-direct-region-hint');
        $this->_mid = $response->getHeaderLine('ig-set-x-mid');

        if ($this->_directRegionHint !== '') {
            $this->_parent->settings->set('direct_region', $this->_directRegionHint);
        }
        if ($this->wwwClaim !== '') {
            $this->_parent->settings->set('www_claim', $this->wwwClaim);
        }
        if ($this->_mid !== '') {
            $this->_parent->settings->set('mid', $this->_mid);
        }
        if ($this->_pigeonBatch !== true) {
            if ($this->_rur !== '') {
                $this->_parent->settings->set('rur', $this->_rur);
            }
            if ($this->_shbid !== '') {
                $this->_parent->settings->set('shbid', $this->_shbid);
            }
            if ($this->_shbts !== '') {
                $this->_parent->settings->set('shbts', $this->_shbts);
            }
        }

        $authorizationHeader = $response->getHeaderLine('ig-set-authorization');

        if ($authorizationHeader !== '') {
            $this->_parent->settings->set('authorization_header', $authorizationHeader);
        }
        if ($this->_rur === '' && $authorizationHeader !== '') {
            $this->_rur = strtoupper($response->getHeaderLine('x-ig-origin-region'));
            $this->_parent->settings->set('rur', $this->_rur);
        }

        $this->bandwidthM = ceil(1000 * (microtime(true) - $start));
        $this->bandwidthB = $response->getHeaderLine('Content-Length');

        if ($this->bandwidthB >= 50000 && $this->bandwidthM >= 50) {
            $this->totalTime += $this->bandwidthM;
            $this->totalBytes += $this->bandwidthB;
        }

        return $response;
    }

    /**
     * Decode a JSON reply from Instagram's API.
     *
     * WARNING: EXTREMELY IMPORTANT! NEVER, *EVER* USE THE BASIC "json_decode"
     * ON API REPLIES! ALWAYS USE THIS METHOD INSTEAD, TO ENSURE PROPER DECODING
     * OF BIG NUMBERS! OTHERWISE YOU'LL TRUNCATE VARIOUS INSTAGRAM API FIELDS!
     *
     * @param string $json  The body (JSON string) of the API response.
     * @param bool   $assoc When FALSE, decode to object instead of associative array.
     *
     * @return object|array|null Object if assoc false, Array if assoc true,
     *                           or NULL if unable to decode JSON.
     */
    public static function api_body_decode(
        $json,
        $assoc = true)
    {
        return @json_decode($json, $assoc, 512, JSON_BIGINT_AS_STRING);
    }

    /**
     * Get the cookies middleware instance.
     *
     * @return FakeCookies
     */
    public function fakeCookies()
    {
        return $this->_fakeCookies;
    }

    /**
     * Get the zero rating rewrite middleware instance.
     *
     * @return ZeroRating
     */
    public function zeroRating()
    {
        return $this->_zeroRating;
    }

    /**
     * Start Pigeon batch requests.
     */
    public function startEmulatingBatch()
    {
        $this->_pigeonBatch = true;
        $this->_pigeonTimestamp = microtime(true);
    }

    /**
     * Stop Pigeon batch requests.
     */
    public function stopEmulatingBatch()
    {
        $this->_pigeonBatch = false;
        $this->_pigeonTimestamp = null;
    }

    /**
     * Get Pigeon Client time.
     *
     * @return string
     */
    private function _getPigeonRawClientTime()
    {
        if ($this->_pigeonBatch === true) {
            $result = $this->_pigeonTimestamp;
            $this->_pigeonTimestamp += mt_rand(0, 100) / 1000;
        } else {
            $result = microtime(true);
        }

        return sprintf('%.3F', $result);
    }

    /**
     * Increment user flow counter.
     *
     * @return int
     */
    public function incrementAndGetUserFlowCounter()
    {
        return $this->_flowUserCounter++;
    }

    /**
     * Generate flow ID.
     *
     * @param int $val1 First value.
     * @param int $val2 Second value.
     *
     * @return int
     */
    public function generateFlowId(
        $val1,
        $val2)
    {
        return $val1 | $val2 << 0x20;
    }

    /**
     * Generate new flow ID.
     *
     * @param int $val Init value.
     *
     * @return int
     */
    public function generateNewFlowId(
        $val)
    {
        return $val | $this->incrementAndGetUserFlowCounter() << 0x20;
    }

    /**
     * Get request ID.
     *
     * @return string
     */
    private function _getRequestId()
    {
        $requestId = sprintf('%s-%d', $this->_requestUuid, $this->_requestId);
        $this->_requestId++;

        return $requestId;
    }

    /**
     * Order headers.
     *
     * @param array $headers Request headers.
     *
     * @return array
     */
    private function _orderHeaders(
        $headers)
    {
        $headersOrder = [
            'Host',
            'X-Fb-Rmd',
            'X-Ads-Opt-Out',
            'X-Google-AD-ID',
            'X-DEVICE-ID',
            'X-CM-Bandwidth-KBPS',
            'X-CM-Latency',
            'X_FB_PHOTO_WATERFALL_ID',
            'X-Instagram-Rupload-Params',
            'X-Entity-Type',
            'X-Entity-Name',
            'X-Entity-Length',
            'Offset',
            'X-IG-App-Locale',
            'X-IG-Device-Locale',
            'X-IG-Mapped-Locale',
            'X-Pigeon-Session-Id',
            'X-Pigeon-Rawclienttime',
            'X-IG-Bandwidth-Speed-KBPS',
            'X-IG-Bandwidth-TotalBytes-B',
            'X-IG-Bandwidth-TotalTime-MS',
            'X-IG-Prefetch-Request',
            'X-IG-App-Startup-Country',
            'X-Bloks-Version-Id',
            'X-IG-WWW-Claim',
            'X-IG-Transfer-Encoding',
            'X-Bloks-Is-Layout-RTL',
            'X-IG-Device-ID',
            'X-IG-Family-Device-ID',
            'X-IG-Android-ID',
            'X-IG-Timezone-Offset',
            'X-IG-Nav-Chain',
            'X-IG-Client-Endpoint',
            'X-IG-SALT-IDS',
            'X-FB-Connection-Type',
            'X-IG-Connection-Type',
            'X-IG-App-ID',
            'X-IG-Capabilities',
            'X-Fb-Friendly-Name',
            'X-Root-Field-Name',
            'Priority',
            'User-Agent',
            'Accept-Language',
            'Authorization',
            'X-MID',
            'IG-U-SHBID',
            'IG-U-SHBTS',
            'IG-U-DS-USER-ID',
            'IG-U-RUR',
            'IG-INTENDED-USER-ID',
            'Content-Type',
            'X-Graphql-Client-Library',
            'Content-Length',
            'X-Tigon-Is-Retry',
            'Accept-Encoding',
            'X-FB-HTTP-Engine',
            'X-FB-Client-IP',
            'X-FB-Server-Cluster',
            'Connection',
        ];

        $orderedHeaders = [];
        foreach ($headersOrder as $key) {
            if (isset($headers[$key])) {
                if (is_array($headers[$key])) {
                    $orderedHeaders[$key] = is_int($headers[$key][0]) ? strval($headers[$key][0]) : $headers[$key][0];
                } else {
                    $orderedHeaders[$key] = $headers[$key];
                }
            }
        }

        return $orderedHeaders;
    }

    /**
     * Sets the last processed request.
     *
     * @param Request $endpoint The last processed request
     */
    public function setLastRequest(
        $endpoint)
    {
        $this->_lastRequest = $endpoint;
    }

    /**
     * Gets the last processed point.
     *
     * @return Request
     */
    public function getLastRequest()
    {
        return $this->_lastRequest;
    }
}
