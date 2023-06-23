<?php

namespace InstagramAPI;

/**
 * Instagram's Private API.
 *
 * TERMS OF USE:
 * - This code is in no way affiliated with, authorized, maintained, sponsored
 *   or endorsed by Instagram or any of its affiliates or subsidiaries. This is
 *   an independent and unofficial API. Use at your own risk.
 * - We do NOT support or tolerate anyone who wants to use this API to send spam
 *   or commit other online crimes.
 * - You will NOT use this API for marketing or other abusive purposes (spam,
 *   botting, harassment, massive bulk messaging...).
 *
 * @author mgp25: Founder, Reversing, Project Leader (https://github.com/mgp25)
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class Instagram implements ExperimentsInterface
{
    /**
     * Experiments refresh interval in sec.
     *
     * @var int
     */
    const EXPERIMENTS_REFRESH = 7200;

    /**
     * Currently active Instagram username.
     *
     * @var string
     */
    public $username;

    /**
     * Currently active Instagram password.
     *
     * @var string
     */
    public $password;

    /**
     * Currently active Facebook access token.
     *
     * @var string
     */
    public $fb_access_token;

    /**
     * The Android device for the currently active user.
     *
     * @var \InstagramAPI\Devices\DeviceInterface
     */
    public $device;

    /**
     * Toggles API query/response debug output.
     *
     * @var bool
     */
    public $debug;

    /**
     * Monolog logger.
     *
     * @var LoggerInterface
     */
    public $logger;

    /**
     * Toggles truncating long responses when debugging.
     *
     * @var bool
     */
    public $truncatedDebug;

    /**
     * For internal use by Instagram-API developers!
     *
     * Toggles the throwing of exceptions whenever Instagram-API's "Response"
     * classes lack fields that were provided by the server. Useful for
     * discovering that our library classes need updating.
     *
     * This is only settable via this public property and is NOT meant for
     * end-users of this library. It is for contributing developers!
     *
     * @var bool
     */
    public $apiDeveloperDebug = false;

    /**
     * Global flag for users who want to run the library incorrectly online.
     *
     * YOU ENABLE THIS AT YOUR OWN RISK! WE GIVE _ZERO_ SUPPORT FOR THIS MODE!
     * EMBEDDING THE LIBRARY DIRECTLY IN A WEBPAGE (WITHOUT ANY INTERMEDIARY
     * PROTECTIVE LAYER) CAN CAUSE ALL KINDS OF DAMAGE AND DATA CORRUPTION!
     *
     * YOU HAVE BEEN WARNED. ANY DATA CORRUPTION YOU CAUSE IS YOUR OWN PROBLEM!
     *
     * The right way to run the library online is described in `webwarning.htm`.
     *
     * @var bool
     *
     * @see Instagram::__construct()
     */
    public static $allowDangerousWebUsageAtMyOwnRisk = false;

    /**
     * Global flag for users who want to run the library incorrectly.
     *
     * YOU ENABLE THIS AT YOUR OWN RISK! WE GIVE _ZERO_ SUPPORT FOR THIS MODE!
     * THIS WILL SKIP ANY PRE AND POST LOGIN FLOW!
     *
     * THIS SHOULD BE ONLY USED FOR RESEARCHING AND EXPERIMENTAL PURPOSES.
     *
     * YOU HAVE BEEN WARNED. ANY DATA CORRUPTION YOU CAUSE IS YOUR OWN PROBLEM!
     *
     * @var bool
     */
    public static $skipLoginFlowAtMyOwnRisk = false;

    /**
     * Global flag for users who want to run the library incorrectly.
     *
     * YOU ENABLE THIS AT YOUR OWN RISK! WE GIVE _ZERO_ SUPPORT FOR THIS MODE!
     * THIS WILL SKIP ANY PRE AND POST LOGIN FLOW!
     *
     * THIS SHOULD BE ONLY USED FOR RESEARCHING AND EXPERIMENTAL PURPOSES.
     *
     * YOU HAVE BEEN WARNED. ANY DATA CORRUPTION YOU CAUSE IS YOUR OWN PROBLEM!
     *
     * @var bool
     */
    public static $skipAccountValidation = false;

    /**
     * Global flag for users who want to manage login exceptions on their own.
     *
     * YOU ENABLE THIS AT YOUR OWN RISK! WE GIVE _ZERO_ SUPPORT FOR THIS MODE!
     *
     * @var bool
     *
     * @see Instagram::__construct()
     */
    public static $manuallyManageLoginException = false;

    /**
     * Global flag for users who want to run deprecated functions.
     *
     * YOU ENABLE THIS AT YOUR OWN RISK! WE GIVE _ZERO_ SUPPORT FOR THIS MODE!
     *
     * @var bool
     *
     * @see Instagram::__construct()
     */
    public static $overrideDeprecatedThrower = false;

    /**
     * Global flag for users who want to run deprecated functions.
     *
     * YOU ENABLE THIS AT YOUR OWN RISK! WE GIVE _ZERO_ SUPPORT FOR THIS MODE!
     *
     * @var bool
     *
     * @see Instagram::__construct()
     */
    public static $disableHttp2 = false;

    /**
     * Global flag for users who want to enable cURL debug.
     *
     * @var bool
     *
     * @see Instagram::__construct()
     */
    public static $curlDebug = false;

    /**
     * Retry on NetworkExpcetion.
     *
     * @var bool
     */
    public static $retryOnNetworkException = false;

    /**
     * Disable login bloks.
     *
     * @var bool
     */
    public static $disableLoginBloks = false;

    /**
     * Override GoodDevices check.
     *
     * @var bool
     */
    public static $overrideGoodDevicesCheck = false;

    /**
     * Use bloks login.
     *
     * @var bool
     */
    public static $useBloksLogin = true;

    /**
     * UUID.
     *
     * @var string
     */
    public $uuid;

    /**
     * Google Play Advertising ID.
     *
     * The advertising ID is a unique ID for advertising, provided by Google
     * Play services for use in Google Play apps. Used by Instagram.
     *
     * @var string
     *
     * @see https://support.google.com/googleplay/android-developer/answer/6048248?hl=en
     */
    public $advertising_id;

    /**
     * Device ID.
     *
     * @var string
     */
    public $device_id;

    /**
     * Phone ID.
     *
     * @var string
     */
    public $phone_id;

    /**
     * Numerical UserPK ID of the active user account.
     *
     * @var string
     */
    public $account_id;

    /**
     * Our current guess about the session status.
     *
     * This contains our current GUESS about whether the current user is still
     * logged in. There is NO GUARANTEE that we're still logged in. For example,
     * the server may have invalidated our current session due to the account
     * password being changed AFTER our last login happened (which kills all
     * existing sessions for that account), or the session may have expired
     * naturally due to not being used for a long time, and so on...
     *
     * NOTE TO USERS: The only way to know for sure if you're logged in is to
     * try a request. If it throws a `LoginRequiredException`, then you're not
     * logged in anymore. The `login()` function will always ensure that your
     * current session is valid. But AFTER that, anything can happen... It's up
     * to Instagram, and we have NO control over what they do with your session!
     *
     * @var bool
     */
    public $isMaybeLoggedIn = false;

    /**
     * Raw API communication/networking class.
     *
     * @var Client
     */
    public $client;

    /**
     * Bloks class.
     *
     * @var Bloks
     */
    public $bloks;

    /**
     * The account settings storage.
     *
     * @var \InstagramAPI\Settings\StorageHandler|null
     */
    public $settings;

    /**
     * The current application session ID.
     *
     * This is a temporary ID which changes in the official app every time the
     * user closes and re-opens the Instagram application or switches account.
     *
     * @var string
     */
    public $session_id;

    /**
     * A list of experiments enabled on per-account basis.
     *
     * @var array
     */
    public $experiments;

    /**
     * Custom Device string.
     *
     * @var string|null
     */
    public $customDeviceString = null;

    /**
     * Custom Device string.
     *
     * @var string|null
     */
    public $customDeviceId = null;

    /**
     * Version Code.
     *
     * @var string
     */
    public $versionCode = Constants::VERSION_CODE[0];

    /**
     * Login attempt counter.
     *
     * @var int
     */
    public $loginAttemptCount = 0;

    /**
     * The radio type used for requests.
     *
     * @var array
     */
    public $radio_type = 'wifi-none';

    /**
     * Timezone offset.
     *
     * @var int
     */
    public $timezoneOffset = null;

    /**
     * The platform used for requests.
     *
     * @var string
     */
    public $platform;

    /**
     * Connection speed.
     *
     * @var string
     */
    public $connectionSpeed = '-1kbps';

    /**
     * EU user.
     *
     * @var bool
     */
    public $isEUUser = true;

    /**
     * Battery level.
     *
     * @var int
     */
    public $batteryLevel = 100;

    /**
     * Sound enabled.
     *
     * @var bool
     */
    public $soundEnabled = false;

    /**
     * Device charging.
     *
     * @var bool
     */
    public $isDeviceCharging = true;

    /**
     * Locale.
     *
     * @var string
     */
    public $locale = '';

    /**
     * Accept language.
     *
     * @var string
     */
    public $acceptLanguage = '';

    /**
     * Accept language.
     *
     * @var string|null
     */
    public $appStartupCountry = null;

    /**
     * Event batch collection.
     *
     * @var array
     */
    public $eventBatch = [
        [], // less common
        [], // android strings and other events will fit here
        [], // Most of the events will go here
    ];

    /**
     * Batch index.
     *
     * @var int
     */
    public $batchIndex = 0;

    /**
     * Navigation sequence.
     *
     * @var int
     */
    public $navigationSequence = 0;

    /**
     * Web user agent.
     *
     * @var string|null
     */
    public $webUserAgent = null;

    /**
     * Logging events compression mode.
     *
     * 0 - Compressed. Event as file
     * 1 - Uncompressed. Multi batch
     * 2 - Compressed. Multi batch/single batch
     *
     * @var int
     */
    public $eventsCompressedMode = 2;

    /**
     * iOS Model.
     *
     * @var string|null
     */
    public $iosModel = null;

    /**
     * Dark mode enabled.
     *
     * @var bool
     */
    public $darkModeEnabled = false;

    /**
     * Low data enabled.
     *
     * @var bool
     */
    public $lowDataModeEnabled = false;

    /**
     * iOS DPI.
     *
     * @var string|null
     */
    public $iosDpi = null;

    /**
     * Navigation chain.
     *
     * @var string
     */
    public $navChain = '';

    /**
     * Navigation chain step.
     *
     * @var int
     */
    public $navChainStep = 0;

    /**
     * Previous navigation chain class.
     *
     * @var string
     */
    public $prevNavChainClass = '';

    /**
     * Disable auto retries in media upload.
     *
     * USE IT UNDER YOUR OWN RISK.
     *
     * @var bool
     */
    public $disableAutoRetriesMediaUpload = false;

    /**
     * Login Waterfall ID.
     *
     * @var string
     */
    public $loginWaterfallId = '';

    /**
     * Carrier.
     *
     * @var string
     */
    public $carrier = 'Android';

    /**
     * Enable resolution check.
     *
     * @var bool
     */
    public $enableResolutionCheck = false;

    /**
     * Custom resolver.
     *
     * @var callable
     */
    public $customResolver = null;

    /**
     * Number of retries to be made when retry
     * on network failure is enabled.
     *
     * @var int
     */
    public $retriesOnNetworkFailure = 3;

    /**
     * Gyroscope enabled.
     *
     * @var bool
     */
    public $gyroscopeEnabled = true;

    /**
     * Background enabled.
     *
     * @var bool
     */
    public $background = false;

    /**
     * Given consent.
     *
     * @var bool
     */
    public $givenConsent = true;

    /**
     * Device init state enabled.
     *
     * @var bool
     */
    public $devicecInitState = false;

    /**
     * CDN RMD.
     *
     * @var bool
     */
    public $cdn_rmd = false;

    /**
     * Bloks info.
     *
     * @var array
     */
    public $bloksInfo = [];

    /** @var Request\Account Collection of Account related functions. */
    public $account;
    /** @var Request\Business Collection of Business related functions. */
    public $business;
    /** @var Request\Checkpoint Collection of Checkpoint related functions. */
    public $checkpoint;
    /** @var Request\Collection Collection of Collections related functions. */
    public $collection;
    /** @var Request\Creative Collection of Creative related functions. */
    public $creative;
    /** @var Request\Direct Collection of Direct related functions. */
    public $direct;
    /** @var Request\Discover Collection of Discover related functions. */
    public $discover;
    /** @var Request\Event Collection of Event related functions. */
    public $event;
    /** @var Request\Hashtag Collection of Hashtag related functions. */
    public $hashtag;
    /** @var Request\Highlight Collection of Highlight related functions. */
    public $highlight;
    /** @var Request\TV Collection of Instagram TV functions. */
    public $tv;
    /** @var Request\Internal Collection of Internal (non-public) functions. */
    public $internal;
    /** @var Request\Live Collection of Live related functions. */
    public $live;
    /** @var Request\Location Collection of Location related functions. */
    public $location;
    /** @var Request\Media Collection of Media related functions. */
    public $media;
    /** @var Request\Music Collection of Music related functions. */
    public $music;
    /** @var Request\People Collection of People related functions. */
    public $people;
    /** @var Request\Push Collection of Push related functions. */
    public $push;
    /** @var Request\Reel Collection of Reel related functions. */
    public $reel;
    /** @var Request\Shopping Collection of Shopping related functions. */
    public $shopping;
    /** @var Request\Story Collection of Story related functions. */
    public $story;
    /** @var Request\Timeline Collection of Timeline related functions. */
    public $timeline;
    /** @var Request\Usertag Collection of Usertag related functions. */
    public $usertag;
    /** @var Request\Web Collection of Web related functions. */
    public $web;

    /**
     * Constructor.
     *
     * @param bool            $debug          Show API queries and responses.
     * @param bool            $truncatedDebug Truncate long responses in debug.
     * @param array           $storageConfig  Configuration for the desired
     *                                        user settings storage backend.
     * @param bool            $platform       The platform to be emulated. 'android' or 'ios'.
     * @param LoggerInterface $logger         Custom logger interface.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function __construct(
        $debug = false,
        $truncatedDebug = false,
        array $storageConfig = [],
        $platform = 'android',
        $logger = null)
    {
        if ($platform !== 'android' && $platform !== 'ios') {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid platform.', $platform));
        } else {
            $this->platform = $platform;
        }

        // Disable incorrect web usage by default. People should never embed
        // this application emulator library directly in a webpage, or they
        // might cause all kinds of damage and data corruption. They should
        // use an intermediary layer such as a database or a permanent process!
        // NOTE: People can disable this safety via the flag at their own risk.
        if (!self::$allowDangerousWebUsageAtMyOwnRisk && (!defined('PHP_SAPI') || PHP_SAPI !== 'cli')) {
            // IMPORTANT: We do NOT throw any exception here for users who are
            // running the library via a webpage. Many webservers are configured
            // to hide all PHP errors, and would just give the user a totally
            // blank webpage with "Error 500" if we throw here, which would just
            // confuse the newbies even more. Instead, we output a HTML warning
            // message for people who try to run the library on a webpage.
            echo file_get_contents(__DIR__.'/../webwarning.htm');
            echo '<p>If you truly want to enable <em>incorrect</em> website usage by directly embedding this application emulator library in your page, then you can do that <strong>AT YOUR OWN RISK</strong> by setting the following flag <em>before</em> you create the <code>Instagram()</code> object:</p>'.PHP_EOL;
            echo '<p><code>\InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;</code></p>'.PHP_EOL;
            exit(0); // Exit without error to avoid triggering Error 500.
        }

        // Prevent people from running this library on ancient PHP versions, and
        // verify that people have the most critically important PHP extensions.
        // NOTE: All of these are marked as requirements in composer.json, but
        // some people install the library at home and then move it somewhere
        // else without the requirements, and then blame us for their errors.
        if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50600) {
            throw new \InstagramAPI\Exception\InternalException(
                'You must have PHP 5.6 or higher to use the Instagram API library.'
            );
        }
        static $extensions = ['curl', 'mbstring', 'gd', 'exif', 'zlib'];
        foreach ($extensions as $ext) {
            if (!@extension_loaded($ext)) {
                throw new \InstagramAPI\Exception\InternalException(sprintf(
                    'You must have the "%s" PHP extension to use the Instagram API library.',
                    $ext
                ));
            }
        }

        // Debugging options.
        $this->debug = $debug;
        $this->truncatedDebug = $truncatedDebug;
        $this->logger = $logger;

        // Load all function collections.
        $this->account = new Request\Account($this);
        $this->business = new Request\Business($this);
        $this->checkpoint = new Request\Checkpoint($this);
        $this->collection = new Request\Collection($this);
        $this->creative = new Request\Creative($this);
        $this->direct = new Request\Direct($this);
        $this->discover = new Request\Discover($this);
        $this->event = new Request\Event($this);
        $this->hashtag = new Request\Hashtag($this);
        $this->highlight = new Request\Highlight($this);
        $this->tv = new Request\TV($this);
        $this->internal = new Request\Internal($this);
        $this->live = new Request\Live($this);
        $this->location = new Request\Location($this);
        $this->media = new Request\Media($this);
        $this->music = new Request\Music($this);
        $this->people = new Request\People($this);
        $this->push = new Request\Push($this);
        $this->reel = new Request\Reel($this);
        $this->shopping = new Request\Shopping($this);
        $this->story = new Request\Story($this);
        $this->timeline = new Request\Timeline($this);
        $this->usertag = new Request\Usertag($this);
        $this->web = new Request\Web($this);

        // Configure the settings storage and network client.
        $self = $this;
        $this->settings = Settings\Factory::createHandler(
            $storageConfig,
            [
                // This saves all user session cookies "in bulk" at script exit
                // or when switching to a different user, so that it only needs
                // to write cookies to storage a few times per user session:
                'onCloseUser' => function ($storage) use ($self) {
                    if ($self->client instanceof Client) {
                        $self->client->saveCookieJar();
                    }
                },
            ]
        );
        $this->client = new Client($this);
        $this->bloks = new Bloks();
        $this->experiments = [];
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
        $this->client->setVerifySSL($state);
    }

    /**
     * Gets the current SSL verification behavior of the Client.
     *
     * @return bool|string
     */
    public function getVerifySSL()
    {
        return $this->client->getVerifySSL();
    }

    /**
     * Set the proxy to use for requests.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#proxy
     *
     * @param string|array|null $value String or Array specifying a proxy in
     *                                 Guzzle format, or NULL to disable
     *                                 proxying.
     */
    public function setProxy(
        $value)
    {
        $this->client->setProxy($value);
    }

    /**
     * Gets the current proxy used for requests.
     *
     * @return string|array|null
     */
    public function getProxy()
    {
        return $this->client->getProxy();
    }

    /**
     * Set custom resolver.
     *
     * @param callable $value.
     */
    public function setCustomResolver(
        $value)
    {
        $this->customResolver = $value;
    }

    /**
     * Set number of retries to make on
     * network failure.
     *
     * @param int $value.
     */
    public function setRetriesOnNetworkFailure(
        $value)
    {
        $this->retriesOnNetworkFailure = $value;
    }

    /**
     * Set the host to resolve.
     *
     * @see https://curl.haxx.se/libcurl/c/CURLOPT_RESOLVE.html
     *
     * @param string|null $value String specifying the host used for resolving.
     */
    public function setResolveHost(
        $value)
    {
        $this->client->setResolveHost($value);
    }

    /**
     * Gets the current resolving host.
     *
     * @return string|null
     */
    public function getResolveHost()
    {
        return $this->client->getResolveHost();
    }

    /**
     * Set a custom device string.
     *
     * If the provided device string is not valid, a device from
     * the good devices list will be chosen randomly.
     *
     * @param string|null $value Device string.
     */
    public function setDeviceString(
        $value)
    {
        $this->customDeviceString = $value;
    }

    /**
     * Set a custom list if device string.
     *
     * A random deviece string will be picked from the provided list.
     * If the provided device string is not valid, a device from
     * the good devices list will be chosen randomly.
     *
     * @param string[]|null $value Device string.
     */
    public function setCustomDevices(
        $value)
    {
        if (is_array($value)) {
            $deviceString = $value[array_rand($value)];
            $this->customDeviceString = is_string($deviceString) ? $deviceString : null;
        }
    }

    /**
     * Set a custom device ID.
     *
     * @param string|null $value Device string.
     */
    public function setCustomDeviceId(
        $value)
    {
        $this->customDeviceId = $value;
    }

    /**
     * Set version code.
     *
     * If the provided version code is not valid, the default version code
     * will be chosen.
     *
     * @param string $value
     * @param bool   $random A random version code will be chosen if set to true.
     */
    public function setVersionCode(
        $value,
        $random = false)
    {
        if ($random === true) {
            $versionCode = array_rand(Constants::VERSION_CODE);
        } else {
            $versionCode = (!in_array($value, Constants::VERSION_CODE)) ? Constants::VERSION_CODE[0] : $value;
        }
        $this->versionCode = $value;
    }

    /**
     * Get version code.
     *
     * @return string Version Code.
     */
    public function getVersionCode()
    {
        return $this->versionCode;
    }

    /**
     * Sets the network interface override to use.
     *
     * Only works if Guzzle is using the cURL backend. But that's
     * almost always the case, on most PHP installations.
     *
     * @see http://php.net/curl_setopt CURLOPT_INTERFACE
     *
     * @param string|null $value Interface name, IP address or hostname, or NULL
     *                           to disable override and let Guzzle use any
     *                           interface.
     */
    public function setOutputInterface(
        $value)
    {
        $this->client->setOutputInterface($value);
    }

    /**
     * Gets the current network interface override used for requests.
     *
     * @return string|null
     */
    public function getOutputInterface()
    {
        return $this->client->getOutputInterface();
    }

    /**
     * Set the radio type used for requests.
     *
     * @param string $value String specifying the radio type.
     */
    public function setRadioType(
        $value)
    {
        if ($value !== 'wifi-none' && $value !== 'mobile-lte') {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid radio type.', $value));
        }

        $this->radio_type = $value;
    }

    /**
     * Get the radio type used for requests.
     *
     * @return string
     */
    public function getRadioType()
    {
        return $this->radio_type;
    }

    /**
     * Set the timezone offset.
     *
     * @param int $value Timezone offset.
     */
    public function setTimezoneOffset(
        $value)
    {
        $this->timezoneOffset = $value;
    }

    /**
     * Get timezone offset.
     *
     * @return string
     */
    public function getTimezoneOffset()
    {
        return $this->timezoneOffset;
    }

    /**
     * Set locale.
     *
     * @param string|string[] $value
     */
    public function setLocale(
        $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $matches = preg_grep('/^[a-z]{2}_[A-Z]{2}$/', $value);

        if (!empty($matches)) {
            $this->locale = implode(', ', $matches);
        } else {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid locale.', $value));
        }
    }

    /**
     * Get locale.
     *
     * @return string
     */
    public function getLocale()
    {
        if ($this->locale === '') {
            return Constants::USER_AGENT_LOCALE;
        } else {
            return $this->locale;
        }
    }

    /**
     * Set accept Language.
     *
     * @param string|string[] $value
     */
    public function setAcceptLanguage(
        $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $matches = preg_grep('/^[a-z]{2}-[A-Z]{2}$/', $value);

        if (!empty($matches)) {
            $this->acceptLanguage = implode(', ', $matches);
        } else {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid accept language value.', $value));
        }
    }

    /**
     * Get Accept Language.
     *
     * @return string
     */
    public function getAcceptLanguage()
    {
        if ($this->acceptLanguage === '') {
            return Constants::ACCEPT_LANGUAGE;
        } else {
            return $this->acceptLanguage;
        }
    }

    /**
     * Set app startup country.
     *
     * @param string|null
     * @param mixed $value
     */
    public function setAppStartupCountry(
        $value)
    {
        if (preg_match_all('/^[A-Z]{2}$/m', $value, $matches)) {
            $this->appStartupCountry = $matches[0][0];
        } else {
            throw new \InvalidArgumentException('Not a valid app startup country value.');
        }
    }

    /**
     * Get app startup country.
     *
     * @return string
     */
    public function getAppStartupCountry()
    {
        return $this->appStartupCountry;
    }

    /**
     * Get the platform used for requests.
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Check if running on Android platform.
     *
     * @return string
     */
    public function getIsAndroid()
    {
        return $this->platform === 'android';
    }

    /**
     * Check if using an android session.
     *
     * @return bool
     */
    public function getIsAndroidSession()
    {
        if (strpos($this->settings->get('device_id'), 'android') !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the connection speed.
     *
     * @return string
     */
    public function getConnectionSpeed()
    {
        return $this->connectionSpeed;
    }

    /**
     * Set the connection speed.
     *
     * @param string $value Connection Speed. Format: '53kbps'.
     */
    public function setConnectionSpeed(
        $value)
    {
        $this->connectionSpeed = $value;
    }

    /**
     * Get if user is in EU.
     *
     * @return bool
     */
    public function getIsEUUser()
    {
        return $this->isEUUser;
    }

    /**
     * Set if user is from EU.
     *
     * @param bool $value. 'true' or 'false'
     */
    public function setIsEUUser(
        $value)
    {
        $this->isEUUser = $value;
    }

    /**
     * Get battery level.
     *
     * @return int
     */
    public function getBatteryLevel()
    {
        return $this->batteryLevel;
    }

    /**
     * Set battery level.
     *
     * @param int $value.
     */
    public function setBatteryLevel(
        $value)
    {
        if ($value < 1 && $value > 100) {
            throw new \InvalidArgumentException(sprintf('"%d" is not a valid battery level.', $value));
        }

        $this->batteryLevel = $value;
    }

    /**
     * Get sound enabled.
     *
     * @return int
     */
    public function getSoundEnabled()
    {
        return $this->soundEnabled;
    }

    /**
     * Set sound enabled.
     *
     * @param bool $value.
     */
    public function setSoundEnabled(
        $value)
    {
        $this->soundEnabled = $value;
    }

    /**
     * Get if device is charging.
     *
     * @return string
     */
    public function getIsDeviceCharging()
    {
        return strval($this->isDeviceCharging);
    }

    /**
     * Set battery level.
     *
     * @param bool $value.
     */
    public function setIsDeviceCharging(
        $value)
    {
        $this->isDeviceCharging = $value;
    }

    /**
     * Set Web User Agent.
     *
     * @param string $value.
     */
    public function setWebUserAgent(
        $value)
    {
        $this->webUserAgent = $value;
    }

    /**
     * Get Web User Agent.
     *
     * @return string
     */
    public function getWebUserAgent()
    {
        return ($this->webUserAgent === null) ? Constants::WEB_USER_AGENT : $this->webUserAgent;
    }

    /**
     * Get if device is VP9 compatible.
     *
     * @return bool
     */
    public function getIsVP9Compatible()
    {
        return $this->device->getIsVP9Compatible();
    }

    /**
     * Get logging events compressed mode.
     *
     * @return int
     */
    public function getEventsCompressedMode()
    {
        return $this->eventsCompressedMode;
    }

    /**
     * Set iOS Model.
     *
     * @param string $device iOS device model.
     */
    public function setIosModel(
        $device)
    {
        Utils::checkIsValidiDevice($device);
        $this->iosModel = $device;
    }

    /**
     * Get iOS Model.
     *
     * @return string
     */
    public function getIosModel()
    {
        return $this->iosModel;
    }

    /**
     * Set iOS DPI.
     *
     * @param string $value.
     */
    public function setIosDpi(
        $value)
    {
        $this->iosDpi = $value;
    }

    /**
     * Get iOS DPI.
     *
     * @return string
     */
    public function getIosDpi()
    {
        return $this->iosDpi;
    }

    /**
     * Get is dark mode enabled.
     *
     * @return bool
     */
    public function getIsDarkModeEnabled()
    {
        return $this->darkModeEnabled;
    }

    /**
     * Set is dark mode enabled.
     *
     * @param bool $value
     */
    public function setIsDarkModeEnabled(
        $value)
    {
        $this->darkModeEnabled = $value;
    }

    /**
     * Get low data mode enabled.
     *
     * @return bool
     */
    public function getIsLowDataModeEnabled()
    {
        return $this->lowDataModeEnabled;
    }

    /**
     * Set low data mode enabled.
     *
     * @param bool $value
     */
    public function setIsLowDataModeEnabled(
        $value)
    {
        $this->lowDataModeEnabled = $value;
    }

    /**
     * Get navigation chain.
     *
     * @return string
     */
    public function getNavChain()
    {
        return $this->navChain;
    }

    /**
     * Set navigation chain.
     *
     * @param mixed $value
     */
    public function setNavChain(
        $value)
    {
        if ($value === '') {
            $this->navChain = '';
        } else {
            $this->navChain .= $value;
        }
    }

    /**
     * Get navigation chain step.
     *
     * @return int
     */
    public function getNavChainStep()
    {
        return $this->navChainStep;
    }

    /**
     * Set navigation chain step.
     *
     * @param mixed $value
     */
    public function setNavChainStep(
        $value)
    {
        $this->navChainStep = $value;
    }

    /**
     * Increment navigation chain step.
     */
    public function incrementNavChainStep()
    {
        $this->navChainStep++;
    }

    /**
     * Decrement navigation chain step.
     */
    public function decrementNavChainStep()
    {
        $this->navChainStep--;
    }

    /**
     * Get previous navigation chain class.
     *
     * @return string
     */
    public function getPrevNavChainClass()
    {
        return $this->prevNavChainClass;
    }

    /**
     * Set next previous navigation chain class.
     *
     * @param string $value
     */
    public function setPrevNavChainClass(
        $value)
    {
        $this->prevNavChainClass = $value;
    }

    /**
     * Disable auto retries media upload.
     *
     * @param bool $value
     */
    public function disableAutoRetriesMediaUpload(
        $value)
    {
        $this->disableAutoRetriesMediaUpload = $value;
    }

    /**
     * Get auto disable retries media upload.
     *
     * @return bool
     */
    public function getIsDisabledAutoRetriesMediaUpload()
    {
        return $this->disableAutoRetriesMediaUpload;
    }

    /**
     * Getcarrier.
     *
     * @return string
     */
    public function getCarrier()
    {
        return $this->carrier;
    }

    /**
     * Set carrier.
     *
     * @param string $value
     */
    public function setCarrier(
        $value)
    {
        $this->carrier = $value;
    }

    /**
     * Set gyroscope enabled.
     *
     * @param bool $value
     */
    public function setGyroscopeEnabled(
        $value)
    {
        $this->gyroscopeEnabled = boolval($value);
    }

    /**
     * Get gyroscope enabled.
     */
    public function getGyroscopeEnabled()
    {
        return $this->gyroscopeEnabled;
    }

    /**
     * Set background state.
     *
     * @param bool $value
     */
    public function setBackgroundState(
        $value)
    {
        $this->background = boolval($value);
    }

    /**
     * Get background state.
     */
    public function getBackgroundState()
    {
        return $this->background ? 'true' : 'false';
    }

    /**
     * Set device init state.
     *
     * @param bool $value
     */
    public function setDeviceInitState(
        $value)
    {
        $this->devicecInitState = boolval($value);
    }

    /**
     * Get device init state.
     */
    public function getDeviceInitState()
    {
        return $this->devicecInitState;
    }

    /**
     * Set given consent.
     *
     * @param bool $value
     */
    public function setGivenConsent(
        $value)
    {
        $this->givenConsent = boolval($value);
    }

    /**
     * Get given consent.
     */
    public function getGivenConsent()
    {
        return $this->givenConsent;
    }

    /**
     * Enable resolution check.
     *
     * @param string $value
     */
    public function enableResolutionCheck(
        $value)
    {
        $this->enableResolutionCheck = $value;
    }

    /**
     * Set user Guzzle Options.
     *
     * @param array
     * @param mixed $options
     */
    public function setUserGuzzleOptions(
        $options)
    {
        $this->client = new Client($this, $options);
    }

    /**
     * Login to Instagram or automatically resume and refresh previous session.
     *
     * Sets the active account for the class instance. You can call this
     * multiple times to switch between multiple Instagram accounts.
     *
     * WARNING: You MUST run this function EVERY time your script runs! It
     * handles automatic session resume and relogin and app session state
     * refresh and other absolutely *vital* things that are important if you
     * don't want to be banned from Instagram!
     *
     * WARNING: This function MAY return a CHALLENGE telling you that the
     * account needs two-factor login before letting you log in! Read the
     * two-factor login example to see how to handle that.
     *
     * @param string      $username           Your Instagram username.
     *                                        You can also use your email or phone,
     *                                        but take in mind that they won't work
     *                                        when you have two factor auth enabled.
     * @param string      $password           Your Instagram password.
     * @param int         $appRefreshInterval How frequently `login()` should act
     *                                        like an Instagram app that's been
     *                                        closed and reopened and needs to
     *                                        "refresh its state", by asking for
     *                                        extended account state details.
     *                                        Default: After `1800` seconds, meaning
     *                                        `30` minutes after the last
     *                                        state-refreshing `login()` call.
     *                                        This CANNOT be longer than `6` hours.
     *                                        Read `_sendLoginFlow()`! The shorter
     *                                        your delay is the BETTER. You may even
     *                                        want to set it to an even LOWER value
     *                                        than the default 30 minutes!
     * @param string|null $deletionToken      Deletion token. Stop account deletion.
     * @param bool        $loggedOut          If account was forced to log out.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse|null A login response if a
     *                                                   full (re-)login
     *                                                   happens, otherwise
     *                                                   `NULL` if an existing
     *                                                   session is resumed.
     */
    public function login(
        $username,
        $password,
        $appRefreshInterval = 1800,
        $deletionToken = null,
        $loggedOut = false)
    {
        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('You must provide a username and password to login().');
        }

        return $this->_login($username, $password, false, $appRefreshInterval, $deletionToken, $loggedOut);
    }

    /**
     * Login to Instagram with Facebook or automatically resume and refresh previous session.
     *
     * Sets the active account for the class instance. You can call this
     * multiple times to switch between multiple Instagram accounts.
     *
     * WARNING: You MUST run this function EVERY time your script runs! It
     * handles automatic session resume and relogin and app session state
     * refresh and other absolutely *vital* things that are important if you
     * don't want to be banned from Instagram!
     *
     * WARNING: This function MAY return a CHALLENGE telling you that the
     * account needs two-factor login before letting you log in! Read the
     * two-factor login example to see how to handle that.
     *
     * @param string $username           Your Instagram username.
     * @param string $fbAccessToken      Your Facebook access token.
     * @param int    $appRefreshInterval How frequently `loginWithFacebook()` should act
     *                                   like an Instagram app that's been
     *                                   closed and reopened and needs to
     *                                   "refresh its state", by asking for
     *                                   extended account state details.
     *                                   Default: After `1800` seconds, meaning
     *                                   `30` minutes after the last
     *                                   state-refreshing `login()` call.
     *                                   This CANNOT be longer than `6` hours.
     *                                   Read `_sendLoginFlow()`! The shorter
     *                                   your delay is the BETTER. You may even
     *                                   want to set it to an even LOWER value
     *                                   than the default 30 minutes!
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse|null A login response if a
     *                                                   full (re-)login
     *                                                   happens, otherwise
     *                                                   `NULL` if an existing
     *                                                   session is resumed.
     */
    public function loginWithFacebook(
         $username,
         $fbAccessToken,
         $appRefreshInterval = 1800
     ) {
        if (empty($username) || empty($fbAccessToken)) {
            throw new \InvalidArgumentException('You must provide a Facebook access token to loginWithFacebook().');
        }

        return $this->_loginWithFacebook($username, $fbAccessToken, false, $appRefreshInterval);
    }

    /**
     * Login to Instagram with email link.
     *
     * Sets the active account for the class instance. You can call this
     * multiple times to switch between multiple Instagram accounts.
     *
     * @param string $username           Your Instagram username.
     * @param string $link               Login link.
     * @param int    $appRefreshInterval How frequently `loginWithFacebook()` should act
     *                                   like an Instagram app that's been
     *                                   closed and reopened and needs to
     *                                   "refresh its state", by asking for
     *                                   extended account state details.
     *                                   Default: After `1800` seconds, meaning
     *                                   `30` minutes after the last
     *                                   state-refreshing `login()` call.
     *                                   This CANNOT be longer than `6` hours.
     *                                   Read `_sendLoginFlow()`! The shorter
     *                                   your delay is the BETTER. You may even
     *                                   want to set it to an even LOWER value
     *                                   than the default 30 minutes!
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse|null A login response if a
     *                                                   full (re-)login
     *                                                   happens, otherwise
     *                                                   `NULL` if an existing
     *                                                   session is resumed.
     */
    public function loginWithEmailLink(
        $username,
        $link,
        $appRefreshInterval = 1800
    ) {
        if (empty($username) || empty($link)) {
            throw new \InvalidArgumentException('You must provide a link to loginWithEmailLink().');
        }

        return $this->_loginWithEmailLink($username, $link, false, $appRefreshInterval);
    }

    /**
     * Internal login handler.
     *
     * @param string      $username
     * @param string      $password
     * @param bool        $forceLogin         Force login to Instagram instead of
     *                                        resuming previous session. Used
     *                                        internally to do a new, full relogin
     *                                        when we detect an expired/invalid
     *                                        previous session.
     * @param int         $appRefreshInterval
     * @param string|null $deletionToken      Deletion token. Stop account deletion.
     * @param bool        $loggedOut          If account was forced to log out.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse|null
     *
     * @see Instagram::login() The public login handler with a full description.
     */
    protected function _login(
        $username,
        $password,
        $forceLogin = false,
        $appRefreshInterval = 1800,
        $deletionToken = null,
        $loggedOut = false)
    {
        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('You must provide a username and password to _login().');
        }

        // Switch the currently active user/pass if the details are different.
        if ($this->username !== $username || $this->password !== $password) {
            $this->_setUser('regular', $username, $password);

            if ($this->settings->get('pending_events') !== null) {
                $this->eventBatch = json_decode($this->settings->get('pending_events'));
                $this->settings->set('pending_events', '');
            }
        }

        $waterfallId = \InstagramAPI\Signatures::generateUUID();
        $this->loginWaterfallId = $waterfallId;
        $startTime = round(microtime(true) * 1000);

        if ($loggedOut === false) {
            $this->event->sendInstagramInstallWithReferrer($this->loginWaterfallId, 0);
            $this->event->sendInstagramInstallWithReferrer($this->loginWaterfallId, 1);
            $this->event->sendFlowSteps('landing', 'step_view_loaded', $waterfallId, $startTime);
            $this->event->sendFlowSteps('landing', 'landing_created', $waterfallId, $startTime);
            $this->event->sendPhoneId($waterfallId, $startTime, 'request');
        }

        // Perform a full relogin if necessary.
        if (!$this->isMaybeLoggedIn || $forceLogin) {
            if ($this->loginAttemptCount === 0 && !self::$skipLoginFlowAtMyOwnRisk && !$loggedOut) {
                $this->_sendPreLoginFlow();
            }

            if ($loggedOut === false) {
                // THIS IS NOT USED ANYMORE IN BLOKS LOGIN
                if (self::$useBloksLogin === false) {
                    $mobileConfigResponse = $this->internal->getMobileConfig(true)->getHttpResponse();
                    $this->settings->set('public_key', $mobileConfigResponse->getHeaderLine('ig-set-password-encryption-pub-key'));
                    $this->settings->set('public_key_id', $mobileConfigResponse->getHeaderLine('ig-set-password-encryption-key-id'));
                }

                $this->event->sendStringImpressions(['2131231876' => 1, '2131231882' => 1, '2131886885' => 2, '2131887195' => 1, '2131887196' => 1, '2131888193' => 4, '2131888472' => 1, '2131890367' => 1, '2131891325' => 1, '2131892179' => 1, '2131892669' => 1, '2131892673' => 1, '2131893765' => 1, '2131893766' => 1, '2131893767' => 1, '2131893768' => 1, '2131893769' => 1, '2131893770' => 1, '2131893771' => 1, '2131893772' => 1, '2131893773' => 1, '2131893774' => 1, '2131893775' => 1, '2131893776' => 1, '2131893777' => 1, '2131893778' => 1, '2131893779' => 1, '2131893780' => 1, '2131893781' => 1, '2131893782' => 1, '2131893783' => 1, '2131893784' => 1, '2131893785' => 1, '2131893788' => 1, '2131893789' => 1, '2131893790' => 1, '2131893791' => 2, '2131893792' => 1, '2131893793' => 1, '2131893806' => 1, '2131893898' => 1, '2131894010' => 1, '2131894018' => 1, '2131896911' => 1, '2131898165' => 1]);
                $this->event->sendFlowSteps('login', 'log_in_username_focus', $waterfallId, $startTime);
                $this->event->sendFlowSteps('login', 'log_in_password_focus', $waterfallId, $startTime);
                $this->event->sendFlowSteps('login', 'log_in_attempt', $waterfallId, $startTime);
                $this->event->sendFlowSteps('login', 'sim_card_state', $waterfallId, $startTime);
                $this->event->sendStringImpressions(['17039371' => 2, '17039886' => 2, '17040255' => 1, '17040256' => 1, '17040257' => 1, '17040645' => 1, '2131232017' => 1, '2131232109' => 1, '2131232164' => 1, '2131232213' => 1, '2131232214' => 1, '2131232227' => 1, '2131232228' => 1, '2131232373' => 1, '2131232374' => 1, '2131232482' => 1, '2131232484' => 1, '2131232609' => 1, '2131232621' => 1, '2131886419' => 1, '2131886885' => 4, '2131887050' => 1, '2131887411' => 1, '2131888159' => 1, '2131890407' => 1, '2131890652' => 1, '2131891238' => 1, '2131891283' => 1, '2131892179' => 1, '2131892675' => 3, '2131892749' => 1, '2131892925' => 3, '2131893604' => 1, '2131894671' => 1, '2131894713' => 2, '2131895369' => 1, '2131895744' => 1, '2131896364' => 1, '2131898082' => 1, '2131898155' => 1]);
                $this->event->sendStringImpressions(['2131231967' => 1, '2131232008' => 1, '2131232152' => 1, '2131232466' => 1, '2131232682' => 1, '2131886420' => 1, '2131886709' => 1, '2131887421' => 1, '2131888159' => 1, '2131889830' => 1, '2131890107' => 5, '2131890302' => 1, '2131890652' => 1, '2131890810' => 4, '2131890813' => 4, '2131892646' => 1, '2131892913' => 1, '2131893117' => 3, '2131893560' => 1, '2131893562' => 1, '2131893668' => 1, '2131893810' => 1, '2131893811' => 1, '2131894468' => 1, '2131896103' => 1, '2131896230' => 1, '2131896432' => 2, '2131896577' => 1, '2131897080' => 3, '2131897172' => 3, '2131897229' => 1, '2131897678' => 2]);

                $this->event->sendAttributionSdkDebug([
                    'event_name'    => 'report_events',
                    'event_types'   => '[LOGIN]',
                ]);
                $this->event->sendAttributionSdkDebug([
                    'event_name'    => 'report_events_compliant',
                    'event_types'   => '[LOGIN]',
                ]);

                $this->event->sendFxSsoLibrary('auth_token_write_start', null, 'log_in');
                $this->event->sendFxSsoLibrary('auth_token_write_failure', 'provider_not_found', 'log_in');
                $this->event->sendFxSsoLibrary('auth_token_write_start', null, 'log_in');
                $this->event->sendFxSsoLibrary('auth_token_write_failure', 'provider_not_found', 'log_in');
            }

            if (self::$useBloksLogin) {
                $this->loginAttemptCount = 1;
                $response = $this->processLoginClientDataAndRedirect();
                $responseArr = $response->asArray();
                $mainBloks = $this->bloks->parseResponse($responseArr, '(bk.action.core.TakeLast');
                $firstDataBlok = null;
                $secondDataBlok = null;
                $thirdDataBlok = null;
                foreach ($mainBloks as $mainBlok) {
                    if (str_contains($mainBlok, 'INTERNAL__latency_qpl_instance_id') && str_contains($mainBlok, 'INTERNAL__latency_qpl_marker_id') && str_contains($mainBlok, 'ar_event_source') && str_contains($mainBlok, 'event_step')) {
                        $firstDataBlok = $mainBlok;
                    }
                    if (str_contains($mainBlok, 'typeahead_id') && str_contains($mainBlok, 'text_input_id') && str_contains($mainBlok, 'text_component_id') && str_contains($mainBlok, 'INTERNAL_INFRA_THEME')) {
                        $secondDataBlok = $mainBlok;
                    }
                    if (str_contains($mainBlok, 'INTERNAL_INFRA_screen_id')) {
                        $thirdDataBlok = $mainBlok;
                    }
                    if ($firstDataBlok !== null && $secondDataBlok !== null && $loggedOut === false) {
                        break;
                    } elseif ($firstDataBlok !== null && $secondDataBlok !== null && $thirdDataBlok !== null) {
                        break;
                    }
                }
                if ($firstDataBlok === null) {
                    $this->isMaybeLoggedIn = false;
                    $this->settings->set('mid', '');
                    $this->settings->set('rur', '');
                    $this->settings->set('www_claim', '');
                    $this->settings->set('account_id', '');
                    $this->settings->set('authorization_header', 'Bearer IGT:2:'); // Header won't be added into request until a new authorization is obtained.
                    $this->account_id = null;

                    throw new \InstagramAPI\Exception\AccountStateException('Try login again.');
                }

                $parsed = $this->bloks->parseBlok($firstDataBlok, 'bk.action.map.Make');
                $offsets = array_slice($this->bloks->findOffsets($parsed, 'offline_experiment_group'), 0, -2);

                foreach ($offsets as $offset) {
                    if (isset($parsed[$offset])) {
                        $parsed = $parsed[$offset];
                    } else {
                        break;
                    }
                }

                $firstMap = $this->bloks->map_arrays($parsed[0], $parsed[1]);
                $this->bloksInfo = array_merge($firstMap, $this->bloksInfo);

                $parsed = $this->bloks->parseBlok($secondDataBlok, 'bk.action.map.Make');
                $offsets = array_slice($this->bloks->findOffsets($parsed, 'INTERNAL_INFRA_THEME'), 0, -2);

                foreach ($offsets as $offset) {
                    if (isset($parsed[$offset])) {
                        $parsed = $parsed[$offset];
                    } else {
                        break;
                    }
                }

                $secondMap = $this->bloks->map_arrays($parsed[0], $parsed[1]);
                $this->bloksInfo = array_merge($secondMap, $this->bloksInfo);

                if ($thirdDataBlok !== null) {
                    $parsed = $this->bloks->parseBlok($thirdDataBlok, 'bk.action.map.Make');
                    $offsets = array_slice($this->bloks->findOffsets($parsed, 'INTERNAL_INFRA_screen_id'), 0, -2);

                    foreach ($offsets as $offset) {
                        if (isset($parsed[$offset])) {
                            $parsed = $parsed[$offset];
                        } else {
                            break;
                        }
                    }

                    $thirdMap = $this->bloks->map_arrays($parsed[0], $parsed[1]);
                    $this->bloksInfo = array_merge($thirdMap, $this->bloksInfo);
                }

                if ($loggedOut === false) {
                    $response = $this->sendLoginTextInputTypeAhead($username);
                } else {
                    $response = $this->getLoginPasswordEntry();
                }

                if ($loggedOut === false) {
                    $accountList = [];
                } else {
                    $accountList = [
                        [
                            'uid'               => $this->account_id,
                            'credential_type'   => 'none',
                            'token'             => '',
                        ],
                    ];
                }

                $response = $this->request('bloks/apps/com.bloks.www.bloks.caa.login.async.send_login_request/')
                    ->setNeedsAuth(false)
                    ->setSignedPost(false)
                    ->addPost('params', json_encode([
                        'client_input_params'           => [
                            'device_id'                     => $this->device_id,
                            'login_attempt_count'           => $this->loginAttemptCount,
                            'secure_family_device_id'       => '',
                            'machine_id'                    => $this->settings->get('mid'),
                            'accounts_list'                 => $accountList,
                            'auth_secure_device_id'         => '',
                            'password'                      => Utils::encryptPassword($password, '', '', true), // Encrypt password with default key and type 1.
                            'family_device_id'              => $this->phone_id,
                            'fb_ig_device_id'               => [],
                            'device_emails'                 => [],
                            'try_num'                       => $this->loginAttemptCount,
                            'event_flow'                    => ($loggedOut === false) ? 'login_manual' : 'aymh',
                            'event_step'                    => 'home_page',
                            'openid_tokens'                 => (object) [],
                            'client_known_key_hash'         => '',
                            'contact_point'                 => $username,
                            'encrypted_msisdn'              => '',
                        ],
                        'server_params'         => [
                            'username_text_input_id'                        => $firstMap['username_text_input_id'],
                            'device_id'                                     => $this->device_id,
                            'should_trigger_override_login_success_action'  => 0,
                            'server_login_source'                           => $firstMap['server_login_source'],
                            'waterfall_id'                                  => $firstMap['waterfall_id'],
                            'login_source'                                  => $firstMap['login_source'],
                            'INTERNAL__latency_qpl_instance_id'             => $firstMap['INTERNAL__latency_qpl_instance_id'][1],
                            'is_platform_login'                             => intval($firstMap['is_platform_login'][1]),
                            'credential_type'                               => $firstMap['credential_type'],
                            'family_device_id'                              => $this->phone_id,
                            'INTERNAL__latency_qpl_marker_id'               => $firstMap['INTERNAL__latency_qpl_marker_id'][1],
                            'offline_experiment_group'                      => $firstMap['offline_experiment_group'],
                            'INTERNAL_INFRA_THEME'                          => $this->bloksInfo['INTERNAL_INFRA_THEME'],
                            'password_text_input_id'                        => $firstMap['password_text_input_id'],
                            'qe_device_id'                                  => $this->uuid,
                            'ar_event_source'                               => $firstMap['ar_event_source'],
                        ],
                    ]))
                    ->addPost('bk_client_context', json_encode([
                        'bloks_version' => Constants::BLOCK_VERSIONING_ID,
                        'styles_id'     => 'instagram',
                    ]))
                    ->addPost('bloks_versioning_id', Constants::BLOCK_VERSIONING_ID)
                    ->getResponse(new Response\LoginResponse());

                $loginResponseWithHeaders = $this->bloks->parseBlok(json_encode($response->asArray()['layout']['bloks_payload']['tree']), 'bk.action.caa.HandleLoginResponse');

                if (is_array($loginResponseWithHeaders)) {
                    $offsets = array_slice($this->bloks->findOffsets($loginResponseWithHeaders, '\exception_message\\'), 0, -2);

                    foreach ($offsets as $offset) {
                        if (isset($loginResponseWithHeaders[$offset])) {
                            $loginResponseWithHeaders = $loginResponseWithHeaders[$offset];
                        } else {
                            break;
                        }
                    }

                    $errorMap = $this->bloks->map_arrays($loginResponseWithHeaders[0], $loginResponseWithHeaders[1]);
                    foreach ($errorMap as $key => $value) {
                        if (!is_array($errorMap[$key])) {
                            $errorMap[stripslashes($key)] = stripslashes($value);
                        }
                        unset($errorMap[$key]);
                    }

                    if (isset($errorMap['exception_message'])) {
                        switch ($errorMap['exception_message']) {
                            case 'Login Error: An unexpected error occurred. Please try logging in again.':
                                throw new \InstagramAPI\Exception\UnexpectedLoginErrorException($errorMap['exception_message']);
                                break;
                            case 'Incorrect Password: The password you entered is incorrect. Please try again.':
                                throw new \InstagramAPI\Exception\IncorrectPasswordException($errorMap['exception_message']);
                                break;
                            default:
                                if (isset($errorMap['event_category'])) {
                                    if ($errorMap['event_category'] === 'checkpoint') {
                                        $loginResponse = $this->bloks->parseBlok(json_encode($response->asArray()['layout']['bloks_payload']['tree']), 'bk.action.caa.PresentCheckpointsFlow');
                                        $loginResponse = json_decode(stripslashes($loginResponse), true);
                                        if (isset($loginResponse['error'])) {
                                            $loginResponse = $loginResponse['error']['error_data'];
                                        }
                                        $loginResponse = new Response\CheckpointResponse($loginResponse);

                                        $e = new \InstagramAPI\Exception\Checkpoint\ChallengeRequiredException();
                                        $e->setResponse($loginResponse);

                                        throw $e;
                                    /*
                                    $offsets = array_slice($this->bloks->findOffsets($loginResponseWithHeaders, '\error_user_msg\\'), 0, -2);

                                    foreach ($offsets as $offset) {
                                        if (isset($loginResponseWithHeaders[$offset])) {
                                            $loginResponseWithHeaders = $loginResponseWithHeaders[$offset];
                                        } else {
                                            break;
                                        }
                                    }

                                    $errorMap = $this->bloks->map_arrays($loginResponseWithHeaders[0], $loginResponseWithHeaders[1]);
                                    foreach ($errorMap as $key => $value) {
                                        if (!is_array($errorMap[$key])) {
                                            $errorMap[stripslashes($key)] = stripslashes($value);
                                        }
                                        unset($errorMap[$key]);
                                    }
                                    */
                                    } elseif ($errorMap['event_category'] === 'two_fac') {
                                        $loginResponse = $this->bloks->parseBlok(json_encode($response->asArray()['layout']['bloks_payload']['tree']), 'bk.action.caa.PresentTwoFactorAuthFlow');
                                        $loginResponse = json_decode(stripslashes($loginResponse), true);
                                        $loginResponse = new Response\LoginResponse($loginResponse);

                                        return $loginResponse;
                                    } elseif ($errorMap['event_category'] === 'login_home_page_interaction') {
                                        $msg = "You can't use Instagram because your account didn't follow our Community Guidelines. This decision can't be reversed either because we've already reviewed it, or because 180 days have passed since your account was disabled";
                                        if (str_contains(json_encode($response->asArray()['layout']['bloks_payload']['tree']), $msg)) {
                                            $loginResponse = new Response\LoginResponse([
                                                'error_type'    => 'inactive_user',
                                                'status'        => 'fail',
                                                'message'       => $msg,
                                            ]);
                                            $e = new \InstagramAPI\Exception\AccountDisabledException($msg);
                                            $e->setResponse($loginResponse);

                                            throw $e;
                                        }
                                        $msg = 'Please wait a few minutes before you try again';
                                        if (str_contains(json_encode($response->asArray()['layout']['bloks_payload']['tree']), $msg)) {
                                            $loginResponse = new Response\LoginResponse([
                                                'error_type'    => 'too_many_attempts',
                                                'status'        => 'fail',
                                                'message'       => $msg,
                                            ]);
                                            $e = new \InstagramAPI\Exception\TooManyAttemptsException($msg);
                                            $e->setResponse($loginResponse);

                                            throw $e;
                                        }
                                        $msg = "We can't find an account with ";
                                        if (str_contains(json_encode($response->asArray()['layout']['bloks_payload']['tree']), $msg)) {
                                            $loginResponse = new Response\LoginResponse([
                                                'error_type'    => 'invalid_username',
                                                'status'        => 'fail',
                                                'message'       => sprintf('%s%s', $msg, $username),
                                            ]);
                                            $e = new \InstagramAPI\Exception\InvalidUsernameException(sprintf('%s%s', $msg, $username));
                                            $e->setResponse($loginResponse);

                                            throw $e;
                                        }
                                        $msg = 'An unexpected error occurred. Please try logging in again.';
                                        if (str_contains(json_encode($response->asArray()['layout']['bloks_payload']['tree']), $msg)) {
                                            $loginResponse = new Response\LoginResponse([
                                                'error_type'    => 'unexpected_login_error',
                                                'status'        => 'fail',
                                                'message'       => $msg,
                                            ]);
                                            $e = new \InstagramAPI\Exception\UnexpectedLoginErrorException($msg);
                                            $e->setResponse($loginResponse);

                                            throw $e;
                                        }
                                        $msg = 'You requested to delete';
                                        if (str_contains(json_encode($response->asArray()['layout']['bloks_payload']['tree']), $msg)) {
                                            $loginResponse = new Response\LoginResponse([
                                                'error_type'    => 'account_deletion_requested',
                                                'status'        => 'fail',
                                                'message'       => sprintf('You requested to delete your account: %s', $username),
                                            ]);
                                            $e = new \InstagramAPI\Exception\AccountDeletionException(sprintf('You requested to delete your account: %s', $username));
                                            $e->setResponse($loginResponse);

                                            throw $e;
                                        }
                                        $msg = 'You entered the wrong code too many times. Wait a few minutes and try again.';
                                        if (str_contains(json_encode($response->asArray()['layout']['bloks_payload']['tree']), $msg)) {
                                            $loginResponse = new Response\LoginResponse([
                                                'error_type'    => 'too_many_attempts_wrong_code',
                                                'status'        => 'fail',
                                                'message'       => $msg,
                                            ]);
                                            $e = new \InstagramAPI\Exception\TooManyAttemptsException('You entered the wrong code too many times. Wait a few minutes and try again.');
                                            $e->setResponse($loginResponse);

                                            throw $e;
                                        }
                                    } else {
                                        throw new \InstagramAPI\Exception\InstagramException($errorMap['event_category']);
                                    }
                                } else {
                                    throw new \InstagramAPI\Exception\InstagramException($errorMap['exception_message']);
                                }
                        }
                    }
                }

                $loginResponseWithHeaders = json_decode($loginResponseWithHeaders, true);
                $loginResponse = new Response\LoginResponse(json_decode($loginResponseWithHeaders['login_response'], true));
                $headers = json_decode($loginResponseWithHeaders['headers'], true);

                $this->settings->set('public_key', $headers['IG-Set-Password-Encryption-Pub-Key']);
                $this->settings->set('public_key_id', $headers['IG-Set-Password-Encryption-Key-Id']);
                $this->settings->set('authorization_header', $headers['IG-Set-Authorization']);

                if (isset($headers['ig-set-ig-u-rur']) && $headers['ig-set-ig-u-rur'] !== '') {
                    $this->settings->set('rur', $headers['ig-set-ig-u-rur']);
                }

                if ($loginResponse->getLoggedInUser()->getUsername() === 'Instagram User') {
                    throw new \InstagramAPI\Exception\AccountDisabledException('Account has been suspended.');
                }
                if ($loginResponse->getLoggedInUser()->getIsBusiness() !== null) {
                    $this->settings->set('business_account', $loginResponse->getLoggedInUser()->getIsBusiness());
                }
            } else {
                try {
                    $request = $this->request('accounts/login/')
                        ->setNeedsAuth(false)
                        ->addPost('jazoest', Utils::generateJazoest($this->phone_id))
                        ->addPost('device_id', $this->device_id)
                        ->addPost('username', $this->username)
                        ->addPost('enc_password', Utils::encryptPassword($password, $this->settings->get('public_key_id'), $this->settings->get('public_key')))
                        //->addPost('_csrftoken', $this->client->getToken())
                        ->addPost('phone_id', $this->phone_id)
                        ->addPost('adid', $this->advertising_id)
                        ->addPost('login_attempt_count', $this->loginAttemptCount);

                    if ($deletionToken !== null) {
                        $request->addPost('stop_deletion_token', $deletionToken);
                    }

                    if ($this->getPlatform() === 'android') {
                        $request->addPost('country_codes', json_encode(
                            [
                                [
                                    'country_code' => Utils::getCountryCode(explode('_', $this->getLocale())[1]),
                                    'source'       => [
                                        'default',
                                    ],
                                ],
                            ]
                        ))
                            ->addPost('guid', $this->uuid)
                            ->addPost('google_tokens', '[]');
                    } elseif ($this->getPlatform() === 'ios') {
                        $request->addPost('reg_login', '0');
                    }
                    $loginResponse = $request->getResponse(new Response\LoginResponse());
                    if ($loginResponse->getLoggedInUser()->getIsBusiness() !== null) {
                        $this->settings->set('business_account', $loginResponse->getLoggedInUser()->getIsBusiness());
                    }
                } catch (\InstagramAPI\Exception\Checkpoint\ChallengeRequiredException $e) {
                    // Login failed because checkpoint is required.
                    // Return server response to tell user they to bypass checkpoint.
                    throw $e;
                } catch (\InstagramAPI\Exception\InstagramException $e) {
                    if ($e->hasResponse() && $e->getResponse()->isTwoFactorRequired()) {
                        // Login failed because two-factor login is required.
                        // Return server response to tell user they need 2-factor.
                        return $e->getResponse();
                    } elseif ($e->hasResponse() && ($e->getResponse()->getInvalidCredentials() === true)) {
                        $this->loginAttemptCount++;

                        throw $e;
                    } else {
                        if ($e->getResponse() === null) {
                            throw new \InstagramAPI\Exception\NetworkException($e);
                        }
                        // Login failed for some other reason... Re-throw error.
                        throw $e;
                    }
                }

                if ($loginResponse->getLoggedInUser()->getUsername() === 'Instagram User') {
                    throw new \InstagramAPI\Exception\AccountDisabledException('Account has been suspended.');
                }
            }

            /*
            try {
                $this->account->getAccountsMultiLogin($response->getMacLoginNonce());
            } catch (\InstagramAPI\Exception\InstagramException $e) {
                //pass
            }
            */

            $this->event->sendFlowSteps('login', 'log_in', $waterfallId, $startTime);
            $this->event->pushNotificationSettings();
            $this->event->enableNotificationSettings([
                'ig_product_announcements', 'ig_friends_on_instagram', 'ig_likes', 'ig_other', 'ig_photos_of_you', 'ig_private_user_follow_request', 'ig_igtv_video_updates', 'ig_mentions_in_bio', 'ig_direct', 'uploads', 'ig_reminders', 'ig_direct_requests', 'ig_igtv_recommended_videos', 'ig_new_followers', 'ig_likes_and_comments_on_photos_of_you', 'ig_first_posts_and_stories', 'ig_comment_likes', 'ig_live_videos', 'ig_comments', 'ig_shopping_drops', 'ig_view_counts', 'ig_direct_video_chat', 'ig_posting_status',
            ]);
            $this->event->sendAttributionSdkDebug([
                'event_name'    => 'get_compliance_action_success',
                'message'       => 'REPORT',
                'event_types'   => '[LOGIN]',
            ]);

            $this->event->sendNavigation('cold_start', 'login', 'feed_timeline');

            $this->event->sendNavigationTabImpression(1);
            $this->event->sendScreenshotDetector();
            $this->event->sendNavigationTabImpression(0);
            $this->loginAttemptCount = 0;
            $this->_updateLoginState($loginResponse);

            $this->_sendLoginFlow(true, $appRefreshInterval);

            // Full (re-)login successfully completed. Return server response.
            return $loginResponse;
        }

        // Attempt to resume an existing session, or full re-login if necessary.
        // NOTE: The "return" here gives a LoginResponse in case of re-login.
        return $this->_sendLoginFlow(false, $appRefreshInterval);
    }

    /**
     * Process login client data and redirect.
     *
     * @param bool $isLoggedOut If states comes from logged_out.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function processLoginClientDataAndRedirect(
        $isLoggedOut = false)
    {
        if ($isLoggedOut) {
            $accountList = [
                [
                    'uid'               => $this->account_id,
                    'credential_type'   => 'none',
                    'token'             => '',
                ],
            ];
        } else {
            $accountList = [];
        }

        return $this->request('bloks/apps/com.bloks.www.bloks.caa.login.process_client_data_and_redirect/')
        ->setNeedsAuth(false)
        ->setSignedPost(false)
        ->addPost('params', json_encode([
            'logged_out_user'           => '',
            'qpl_join_id'               => Signatures::generateUUID(),
            'family_device_id'          => $this->phone_id,
            'device_id'                 => $this->device_id,
            'offline_experiment_group'  => $this->settings->get('offline_experiment'),
            'waterfall_id'              => $this->loginWaterfallId,
            'show_internal_settings'    => false,
            'qe_device_id'              => $this->uuid,
            'account_list'              => $accountList,
            'blocked_uid'               => [],
            'INTERNAL_INFRA_THEME'      => 'harm_f',
        ]))
        ->addPost('bk_client_context', json_encode([
            'bloks_version' => Constants::BLOCK_VERSIONING_ID,
            'styles_id'     => 'instagram',
        ]))
        ->addPost('bloks_versioning_id', Constants::BLOCK_VERSIONING_ID)
        ->getResponse(new Response\GenericResponse());
    }

    /**
     * Send login text input typy ahead.
     *
     * @param bool $username Username.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function sendLoginTextInputTypeAhead(
        $username)
    {
        return $this->request('bloks/apps/com.bloks.www.caa.login.cp_text_input_type_ahead/')
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->addPost('params', json_encode([
                'client_input_params'           => [
                    'account_centers'   => [],
                    'query'             => $username,
                ],
                'server_params'         => [
                    'text_input_id'                     => $this->bloksInfo['text_input_id'][1],
                    'typeahead_id'                      => $this->bloksInfo['typeahead_id'][1],
                    'text_component_id'                 => $this->bloksInfo['text_component_id'][1],
                    'INTERNAL__latency_qpl_marker_id'   => $this->bloksInfo['INTERNAL__latency_qpl_marker_id'][1],
                    'INTERNAL_INFRA_THEME'              => $this->bloksInfo['INTERNAL_INFRA_THEME'],
                    'fdid'                              => $this->bloksInfo['fdid'],
                    'waterfall_id'                      => $this->loginWaterfallId,
                    'screen_id'                         => $this->bloksInfo['screen_id'][1],
                    'INTERNAL__latency_qpl_instance_id' => $this->bloksInfo['INTERNAL__latency_qpl_instance_id'][1],
                ],
            ]))
            ->addPost('bk_client_context', json_encode([
                'bloks_version' => Constants::BLOCK_VERSIONING_ID,
                'styles_id'     => 'instagram',
            ]))
            ->addPost('bloks_versioning_id', Constants::BLOCK_VERSIONING_ID)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get login password entry.
     *
     * @param bool $username Username.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getLoginPasswordEntry()
    {
        return $this->request('bloks/apps/com.bloks.www.caa.login.aymh_password_entry/')
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->addPost('params', json_encode([
                'client_input_params'           => [
                    'user_id'   => $this->account_id,
                ],
                'server_params'         => [
                    'offline_experiment_group'          => $this->settings->get('offline_experiment'),
                    'INTERNAL_INFRA_THEME'              => $this->bloksInfo['INTERNAL_INFRA_THEME'],
                    'device_id'                         => $this->device_id,
                    'is_platform_login'                 => 0,
                    'qe_device_id'                      => $this->uuid,
                    'family_device_id'                  => $this->phone_id,
                    'INTERNAL_INFRA_screen_id'          => isset($this->bloksInfo['INTERNAL_INFRA_screen_id']) ? $this->bloksInfo['INTERNAL_INFRA_screen_id'][1] : '',
                ],
            ]))
            ->addPost('bk_client_context', json_encode([
                'bloks_version' => Constants::BLOCK_VERSIONING_ID,
                'styles_id'     => 'instagram',
            ]))
            ->addPost('bloks_versioning_id', Constants::BLOCK_VERSIONING_ID)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Internal Facebook login handler.
     *
     * @param string $username           Your Instagram username.
     * @param string $fbAccessToken      Facebook access token.
     * @param bool   $forceLogin         Force login to Instagram instead of
     *                                   resuming previous session. Used
     *                                   internally to do a new, full relogin
     *                                   when we detect an expired/invalid
     *                                   previous session.
     * @param int    $appRefreshInterval
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse|null
     *
     * @see Instagram::loginWithFacebook() The public Facebook login handler with a full description.
     */
    protected function _loginWithFacebook(
         $username,
         $fbAccessToken,
         $forceLogin = false,
         $appRefreshInterval = 1800
     ) {
        if (empty($fbAccessToken)) {
            throw new \InvalidArgumentException('You must provide an fb_access_token to _loginWithFacebook().');
        }
        // Switch the currently active access token if it is different.
        if ($this->fb_access_token !== $fbAccessToken) {
            $this->_setUser('facebook', $username, $fbAccessToken);
        }
        if (!$this->isMaybeLoggedIn || $forceLogin) {
            if ($this->loginAttemptCount === 0 && !self::$skipLoginFlowAtMyOwnRisk) {
                $this->_sendPreLoginFlow();
            }

            try {
                $response = $this->request('fb/facebook_signup/')
                     ->setNeedsAuth(false)
                     ->addPost('dryrun', 'false')
                     ->addPost('phone_id', $this->phone_id)
                     ->addPost('adid', $this->advertising_id)
                     ->addPost('device_id', $this->device_id)
                     ->addPost('waterfall_id', Signatures::generateUUID())
                     ->addPost('fb_access_token', $this->fb_access_token)
                     ->getResponse(new Response\LoginResponse());
            } catch (\InstagramAPI\Exception\InstagramException $e) {
                if ($e->hasResponse() && $e->getResponse()->isTwoFactorRequired()) {
                    // Login failed because two-factor login is required.
                    // Return server response to tell user they need 2-factor.
                    return $e->getResponse();
                } elseif ($e->hasResponse() && ($e->getResponse()->getInvalidCredentials() === true)) {
                    $this->loginAttemptCount++;
                } else {
                    if ($e->getResponse() === null) {
                        throw new \InstagramAPI\Exception\NetworkException($e);
                    }
                    // Login failed for some other reason... Re-throw error.
                    throw $e;
                }
            }
            $this->loginAttemptCount = 0;
            $this->_updateLoginState($response);

            $this->_sendLoginFlow(true, $appRefreshInterval);

            // Full (re-)login successfully completed. Return server response.
            return $response;
        }
        // Attempt to resume an existing session, or full re-login if necessary.
        // NOTE: The "return" here gives a LoginResponse in case of re-login.
        return $this->_sendLoginFlow(false, $appRefreshInterval);
    }

    /**
     * Internal Email link login handler.
     *
     * @param string $username           Your Instagram username.
     * @param string $link               Email login link.
     * @param bool   $forceLogin         Force login to Instagram instead of
     *                                   resuming previous session. Used
     *                                   internally to do a new, full relogin
     *                                   when we detect an expired/invalid
     *                                   previous session.
     * @param int    $appRefreshInterval
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse|null
     *
     * @see Instagram::loginWithEmailLink() The public email with link login handler with a full description.
     */
    protected function _loginWithEmailLink(
        $username,
        $link,
        $forceLogin = false,
        $appRefreshInterval = 1800)
    {
        // Switch the currently active user/pass if the details are different.
        if ($this->username !== $username) {
            $this->_setUser('regular', $username, 'NOPASSWORD');

            if ($this->settings->get('pending_events') !== null) {
                $this->eventBatch = json_decode($this->settings->get('pending_events'));
                $this->settings->set('pending_events', '');
            }
        }

        if (!$this->isMaybeLoggedIn || $forceLogin) {
            if ($this->loginAttemptCount === 0 && !self::$skipLoginFlowAtMyOwnRisk) {
                $this->_sendPreLoginFlow();
            }

            try {
                $str = explode('?', $link);
                parse_str($str[1], $params);

                $request = $this->request('accounts/one_click_login/')
                    ->setNeedsAuth(false)
                    ->addPost('source', 'email')
                    //->addPost('_csrftoken', $this->client->getToken())
                    ->addPost('uid', $params['uid'])
                    ->addPost('adid', $this->advertising_id)
                    ->addPost('guid', $this->uuid)
                    ->addPost('device_id', $this->device_id)
                    ->addPost('token', $params['token'])
                    ->addPost('auto_send', '0');

                $response = $request->getResponse(new Response\LoginResponse());
                $this->settings->set('business_account', $response->getLoggedInUser()->getIsBusiness());
            } catch (\InstagramAPI\Exception\InstagramException $e) {
                if ($e->hasResponse() && $e->getResponse()->isTwoFactorRequired()) {
                    // Login failed because two-factor login is required.
                    // Return server response to tell user they need 2-factor.
                    return $e->getResponse();
                } elseif ($e->hasResponse() && ($e->getResponse()->getInvalidCredentials() === true)) {
                    $this->loginAttemptCount++;
                } else {
                    if ($e->getResponse() === null) {
                        throw new \InstagramAPI\Exception\NetworkException($e);
                    }
                    // Login failed for some other reason... Re-throw error.
                    throw $e;
                }
            }

            $this->loginAttemptCount = 0;
            $this->_updateLoginState($response);

            $this->_sendLoginFlow(true, $appRefreshInterval);

            // Full (re-)login successfully completed. Return server response.
            return $response;
        }
        // Attempt to resume an existing session, or full re-login if necessary.
        // NOTE: The "return" here gives a LoginResponse in case of re-login.
        return $this->_sendLoginFlow(false, $appRefreshInterval);
    }

    /**
     * Finish a two-factor authenticated login.
     *
     * This function finishes a two-factor challenge that was provided by the
     * regular `login()` function. If you successfully answer their challenge,
     * you will be logged in after this function call.
     *
     * @param string      $username            Your Instagram username used for login.
     *                                         Email and phone aren't allowed here.
     * @param string      $password            Your Instagram password.
     * @param string      $twoFactorIdentifier Two factor identifier, obtained in
     *                                         login() response. Format: `123456`.
     * @param string      $verificationCode    Verification code you have received
     *                                         via SMS.
     * @param string      $verificationMethod  The verification method for 2FA. 1 is SMS,
     *                                         2 is backup codes, 3 is TOTP, 4 is notification,
     *                                         6 is whatsapp.
     * @param int         $appRefreshInterval  See `login()` for description of this
     *                                         parameter.
     * @param string|null $usernameHandler     Instagram username sent in the login response.
     *                                         Email and phone aren't allowed here.
     * @param bool        $trustDevice         If you want to trust the used Device ID.
     * @param string      $pollingNonce        Trusted polling nonce.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse
     */
    public function finishTwoFactorLogin(
        $username,
        $password,
        $twoFactorIdentifier,
        $verificationCode,
        $verificationMethod = 1,
        $appRefreshInterval = 1800,
        $usernameHandler = null,
        $trustDevice = true,
        $pollingNonce = null)
    {
        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('You must provide a username and password to finishTwoFactorLogin().');
        }
        if ((empty($verificationCode) && ($verificationMethod !== 4)) || empty($twoFactorIdentifier)) {
            throw new \InvalidArgumentException('You must provide a verification code and two-factor identifier to finishTwoFactorLogin().');
        }
        if (!in_array($verificationMethod, [1, 2, 3, 4, 6], true)) {
            throw new \InvalidArgumentException('You must provide a valid verification method value.');
        }

        // Switch the currently active user/pass if the details are different.
        // NOTE: The username and password AREN'T actually necessary for THIS
        // endpoint, but this extra step helps people who statelessly embed the
        // library directly into a webpage, so they can `finishTwoFactorLogin()`
        // on their second page load without having to begin any new `login()`
        // call (since they did that in their previous webpage's library calls).
        if ($this->username !== $username || $this->password !== $password) {
            $this->_setUser('regular', $username, $password);
        }

        $username = ($usernameHandler !== null) ? $usernameHandler : $username;

        // Remove all whitespace from the verification code.
        $verificationCode = preg_replace('/\s+/', '', $verificationCode);

        $request = $this->request('accounts/two_factor_login/')
            ->setNeedsAuth(false)
            ->addPost('verification_code', $verificationCode)
            ->addPost('phone_id', $this->phone_id)
            //->addPost('_csrftoken', $this->client->getToken())
            ->addPost('two_factor_identifier', $twoFactorIdentifier)
            ->addPost('username', $username)
            ->addPost('trust_this_device', ($trustDevice) ? '1' : '0')
            ->addPost('guid', $this->uuid)
            ->addPost('device_id', $this->device_id)
            ->addPost('waterfall_id', $this->loginWaterfallId)
            // 1 - SMS, 2 - Backup codes, 3 - TOTP, 4 - Notification approval, 6 - whatsapp
            ->addPost('verification_method', $verificationMethod);

        if ($pollingNonce !== null) {
            $request->addPost('trusted_notification_polling_nonces', json_encode([$pollingNonce]));
        }

        $response = $request->getResponse(new Response\LoginResponse());

        $this->_updateLoginState($response);

        $this->_sendLoginFlow(true, $appRefreshInterval);

        return $response;
    }

    /**
     * Request a new security code SMS for a Two Factor login account.
     *
     * NOTE: You should first attempt to `login()` which will automatically send
     * you a two factor SMS. This function is just for asking for a new SMS if
     * the old code has expired.
     *
     * NOTE: Instagram can only send you a new code every 60 seconds.
     *
     * @param string      $username            Your Instagram username.
     * @param string      $password            Your Instagram password.
     * @param string      $twoFactorIdentifier Two factor identifier, obtained in
     *                                         `login()` response.
     * @param string|null $usernameHandler     Instagram username sent in the login response.
     *                                         Email and phone aren't allowed here.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\TwoFactorLoginSMSResponse
     */
    public function sendTwoFactorLoginSMS(
        $username,
        $password,
        $twoFactorIdentifier,
        $usernameHandler = null)
    {
        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('You must provide a username and password to sendTwoFactorLoginSMS().');
        }
        if (empty($twoFactorIdentifier)) {
            throw new \InvalidArgumentException('You must provide a two-factor identifier to sendTwoFactorLoginSMS().');
        }

        // Switch the currently active user/pass if the details are different.
        // NOTE: The password IS NOT actually necessary for THIS
        // endpoint, but this extra step helps people who statelessly embed the
        // library directly into a webpage, so they can `sendTwoFactorLoginSMS()`
        // on their second page load without having to begin any new `login()`
        // call (since they did that in their previous webpage's library calls).
        if ($this->username !== $username || $this->password !== $password) {
            $this->_setUser('regular', $username, $password);
        }

        $username = ($usernameHandler !== null) ? $usernameHandler : $username;

        return $this->request('accounts/send_two_factor_login_sms/')
            ->setNeedsAuth(false)
            ->addPost('two_factor_identifier', $twoFactorIdentifier)
            ->addPost('username', $username)
            ->addPost('device_id', $this->device_id)
            ->addPost('guid', $this->uuid)
            //->addPost('_csrftoken', $this->client->getToken())
            ->getResponse(new Response\TwoFactorLoginSMSResponse());
    }

    /**
     * Request a new security code via WhatsApp for a Two Factor login account.
     *
     * @param string      $username            Your Instagram username.
     * @param string      $password            Your Instagram password.
     * @param string      $twoFactorIdentifier Two factor identifier, obtained in
     *                                         `login()` response.
     * @param string|null $usernameHandler     Instagram username sent in the login response.
     *                                         Email and phone aren't allowed here.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\TwoFactorLoginSMSResponse
     */
    public function sendTwoFactorLoginWhatsapp(
        $username,
        $password,
        $twoFactorIdentifier,
        $usernameHandler = null)
    {
        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('You must provide a username and password to sendTwoFactorLoginSMS().');
        }
        if (empty($twoFactorIdentifier)) {
            throw new \InvalidArgumentException('You must provide a two-factor identifier to sendTwoFactorLoginSMS().');
        }

        // Switch the currently active user/pass if the details are different.
        // NOTE: The password IS NOT actually necessary for THIS
        // endpoint, but this extra step helps people who statelessly embed the
        // library directly into a webpage, so they can `sendTwoFactorLoginSMS()`
        // on their second page load without having to begin any new `login()`
        // call (since they did that in their previous webpage's library calls).
        if ($this->username !== $username || $this->password !== $password) {
            $this->_setUser('regular', $username, $password);
        }

        $username = ($usernameHandler !== null) ? $usernameHandler : $username;

        return $this->request('two_factor/send_two_factor_login_whatsapp/')
            ->setNeedsAuth(false)
            ->addPost('two_factor_identifier', $twoFactorIdentifier)
            ->addPost('username', $username)
            ->addPost('device_id', $this->device_id)
            ->addPost('guid', $this->uuid)
            //->addPost('_csrftoken', $this->client->getToken())
            ->getResponse(new Response\TwoFactorLoginSMSResponse());
    }

    /**
     * Check trusted notification status for 2FA login.
     *
     * This checks wether a device has approved the login via
     * notification.
     *
     * @param string $username            Your Instagram username.
     * @param string $twoFactorIdentifier Two factor identifier, obtained in
     *                                    `login()` response.
     * @param string $pollingNonce        Trusted polling nonce.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\TwoFactorNotificationStatusResponse
     */
    public function checkTrustedNotificationStatus(
        $username,
        $twoFactorIdentifier,
        $pollingNonce)
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('You must provide a username.');
        }
        if (empty($twoFactorIdentifier)) {
            throw new \InvalidArgumentException('You must provide a two-factor identifier.');
        }

        return $this->request('two_factor/check_trusted_notification_status/')
        ->setNeedsAuth(false)
        ->addPost('two_factor_identifier', $twoFactorIdentifier)
        ->addPost('username', $username)
        ->addPost('device_id', $this->device_id)
        ->addPost('trusted_notification_polling_nonces', json_encode([$pollingNonce]))
        //->addPost('_csrftoken', $this->client->getToken())
        ->getResponse(new Response\TwoFactorNotificationStatusResponse());
    }

    /**
     * Finish checkpoint.
     *
     * If code verification went successful, we proceed to update state and
     * send login flow.
     *
     * @param Response\LoginResponse $verifyCodeResponse
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function finishCheckpoint(
        $verifyCodeResponse)
    {
        $this->_updateLoginState($verifyCodeResponse);
        $this->_sendLoginFlow(true, 1800);
    }

    /**
     * Request information about available password recovery methods for an account.
     *
     * This will tell you things such as whether SMS or EMAIL-based recovery is
     * available for the given account name.
     *
     * `WARNING:` You can call this function without having called `login()`,
     * but be aware that a user database entry will be created for every
     * username you try to look up. This is ONLY meant for recovering your OWN
     * accounts.
     *
     * @param string $username Your Instagram username.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UsersLookupResponse
     */
    public function userLookup(
        $username)
    {
        // Set active user (without pwd), and create database entry if new user.
        $this->setUserWithoutPassword($username);
        $waterfallId = \InstagramAPI\Signatures::generateUUID();

        return $this->request('users/lookup/')
            ->setNeedsAuth(false)
            ->addPost('country_codes', json_encode(
                [
                    [
                        'country_code' => Utils::getCountryCode(explode('_', $this->getLocale())[1]),
                        'source'       => [
                            'default',
                        ],
                    ],
                ]
            ))
            ->addPost('q', $username)
            ->addPost('directly_sign_in', 'true')
            ->addPost('username', $username)
            ->addPost('device_id', $this->device_id)
            ->addPost('android_build_type', 'release')
            ->addPost('guid', $this->uuid)
            ->addPost('waterfall_id', $waterfallId)
            ->addPost('directly_sign_in', 'true')
            //->addPost('_csrftoken', $this->client->getToken())
            ->getResponse(new Response\UsersLookupResponse());
    }

    /**
     * Request a recovery EMAIL to get back into your account.
     *
     * `WARNING:` You can call this function without having called `login()`,
     * but be aware that a user database entry will be created for every
     * username you try to look up. This is ONLY meant for recovering your OWN
     * accounts.
     *
     * @param string $username Your Instagram username.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\RecoveryResponse
     */
    public function sendRecoveryEmail(
        $username)
    {
        // Verify that they can use the recovery email option.
        $userLookup = $this->userLookup($username);
        if (!$userLookup->getCanEmailReset()) {
            throw new \InstagramAPI\Exception\InternalException('Email recovery is not available, since your account lacks a verified email address.');
        }

        return $this->request('accounts/send_recovery_flow_email/')
            ->setNeedsAuth(false)
            ->addPost('query', $username)
            ->addPost('adid', $this->advertising_id)
            ->addPost('device_id', $this->device_id)
            ->addPost('guid', $this->uuid)
            //->addPost('_csrftoken', $this->client->getToken())
            ->getResponse(new Response\RecoveryResponse());
    }

    /**
     * Request a recovery SMS to get back into your account.
     *
     * `WARNING:` You can call this function without having called `login()`,
     * but be aware that a user database entry will be created for every
     * username you try to look up. This is ONLY meant for recovering your OWN
     * accounts.
     *
     * @param string $username Your Instagram username.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\RecoveryResponse
     */
    public function sendRecoverySMS(
        $username)
    {
        // Verify that they can use the recovery SMS option.
        $userLookup = $this->userLookup($username);
        if (!$userLookup->getHasValidPhone() || !$userLookup->getCanSmsReset()) {
            throw new \InstagramAPI\Exception\InternalException('SMS recovery is not available, since your account lacks a verified phone number.');
        }

        return $this->request('users/lookup_phone/')
            ->setNeedsAuth(false)
            ->addPost('query', $username)
            //->addPost('_csrftoken', $this->client->getToken())
            ->getResponse(new Response\RecoveryResponse());
    }

    /**
     * Set the active account for the class instance.
     *
     * We can call this multiple times to switch between multiple accounts.
     *
     * @param string $loginType 'regular' or 'facebook'.
     * @param string $username  Your Instagram username.
     * @param string $password  Your Instagram password.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     */
    protected function _setUser(
        $loginType,
        $username,
        $password)
    {
        if ((empty($username) || empty($password)) && $loginType === 'regular') {
            throw new \InvalidArgumentException('You must provide a username and password to _setUser().');
        }

        // Load all settings from the storage and mark as current user.
        $this->settings->setActiveUser($username);

        // Generate the user's device instance, which will be created from the
        // user's last-used device IF they've got a valid, good one stored.
        // But if they've got a BAD/none, this will create a brand-new device.

        $autoFallback = self::$overrideGoodDevicesCheck ? false : true;
        if ($this->settings->get('devicestring') !== null) {
            $savedDeviceString = $this->settings->get('devicestring');
        } elseif ($this->customDeviceString !== null) {
            $savedDeviceString = $this->customDeviceString;
        } else {
            $savedDeviceString = null;
            $autoFallback = true;
        }

        $this->device = new Devices\Device(
            Constants::IG_VERSION,
            $this->getVersionCode(),
            $this->getLocale(),
            $this->getAcceptLanguage(),
            $savedDeviceString,
            $autoFallback,
            $this->getPlatform(),
            $this->getIosModel(),
            $this->getIosDpi(),
            $this->enableResolutionCheck
        );

        // Get active device string so that we can compare it to any saved one.
        $deviceString = $this->device->getDeviceString();

        // Generate a brand-new device fingerprint if the device wasn't reused
        // from settings, OR if any of the stored fingerprints are missing.
        // NOTE: The regeneration when our device model changes is to avoid
        // dangerously reusing the "previous phone's" unique hardware IDs.
        // WARNING TO CONTRIBUTORS: Only add new parameter-checks here if they
        // are CRITICALLY important to the particular device. We don't want to
        // frivolously force the users to generate new device IDs constantly.
        $resetCookieJar = false;
        if ($deviceString !== $savedDeviceString // Brand new device, or missing
            || empty($this->settings->get('uuid')) // one of the critically...
            || empty($this->settings->get('phone_id')) // ...important device...
            || empty($this->settings->get('device_id'))) { // ...parameters.
            // Erase all previously stored device-specific settings and cookies.
            $this->settings->eraseDeviceSettings();

            // Save the chosen device string to settings.
            if ($this->getPlatform() === 'ios') {
                $deviceString = 'ios';
            }

            $this->settings->set('devicestring', $deviceString);

            // Generate hardware fingerprints for the new device.
            if ($this->customDeviceId !== null) {
                $this->settings->set('device_id', $this->customDeviceId);
            } else {
                $this->settings->set('device_id', Signatures::generateDeviceId($this->getPlatform()));
            }

            if ($this->getIsAndroid()) {
                $result = Signatures::generateSpecialUUID();
                $phoneId = $result['phone_id'];
                $this->settings->set('offline_experiment', $result['offline_experiment']);
                $this->settings->set('nav_started', 'false');
                $this->settings->set('phone_id', $phoneId);
            } else {
                $this->settings->set('phone_id', $this->settings->get('device_id'));
            }
            $this->settings->set('uuid', Signatures::generateUUID(true, true));

            if ($loginType === 'facebook') {
                $this->settings->set('fb_access_token', $password);
            }

            // Erase any stored account ID, to ensure that we detect ourselves
            // as logged-out. This will force a new relogin from the new device.
            $this->settings->set('account_id', '');

            // We'll also need to throw out all previous cookies.
            $resetCookieJar = true;
        }

        // Generate other missing values. These are for less critical parameters
        // that don't need to trigger a complete device reset like above. For
        // example, this is good for new parameters that Instagram introduces
        // over time, since those can be added one-by-one over time here without
        // needing to wipe/reset the whole device.
        if (empty($this->settings->get('advertising_id'))) {
            $this->settings->set('advertising_id', Signatures::generateUUID());
        }
        if (empty($this->settings->get('session_id'))) {
            $this->settings->set('session_id', Signatures::generateUUID());
        }
        if (empty($this->settings->get('offline_experiment'))) {
            $result = Signatures::generateSpecialUUID($this->settings->get('phone_id'));
            $this->settings->set('offline_experiment', $result['offline_experiment']);
        }

        // Store various important parameters for easy access.
        $this->username = $username;
        $this->password = $password;
        $this->uuid = $this->settings->get('uuid');
        $this->advertising_id = $this->settings->get('advertising_id');
        $this->device_id = $this->settings->get('device_id');
        $this->phone_id = $this->settings->get('phone_id');
        $this->session_id = $this->settings->get('session_id');
        if ($loginType === 'facebook') {
            $this->fb_access_token = $this->settings->get('fb_access_token');
        }
        $this->experiments = $this->settings->getExperiments();

        // Load the previous session details if we're possibly logged in.
        if ($this->settings->get('authorization_header') !== null) {
            $authorizationData = json_decode(base64_decode(explode(':', $this->settings->get('authorization_header'))[2]), true);
        }
        if (!isset($authorizationData['sessionid'])) {
            if (!$resetCookieJar && $this->settings->isMaybeLoggedIn()) {
                $this->isMaybeLoggedIn = true;
                $this->account_id = $this->settings->get('account_id');
            } else {
                $this->isMaybeLoggedIn = false;
                $this->account_id = null;
            }
        } else {
            $this->isMaybeLoggedIn = true;
            if (!isset($authorizationData['ds_user_id'])) {
                $this->account_id = $this->settings->get('account_id');
            } else {
                if ($this->settings->get('account_id') === null) {
                    $this->settings->set('account_id', $authorizationData['ds_user_id']);
                }
                $this->account_id = $authorizationData['ds_user_id'];
            }
        }

        // Configures Client for current user AND updates isMaybeLoggedIn state
        // if it fails to load the expected cookies from the user's jar.
        // Must be done last here, so that isMaybeLoggedIn is properly updated!
        // NOTE: If we generated a new device we start a new cookie jar.
        $this->client->updateFromCurrentSettings($resetCookieJar);
    }

    /**
     * Set the active account for the class instance, without knowing password.
     *
     * This internal function is used by all unauthenticated pre-login functions
     * whenever they need to perform unauthenticated requests, such as looking
     * up a user's account recovery options.
     *
     * `WARNING:` A user database entry will be created for every username you
     * set as the active user, exactly like the normal `_setUser()` function.
     * This is necessary so that we generate a user-device and data storage for
     * each given username, which gives us necessary data such as a "device ID"
     * for the new user's virtual device, to use in various API-call parameters.
     *
     * `WARNING:` This function CANNOT be used for performing logins, since
     * Instagram will validate the password and will reject the missing
     * password. It is ONLY meant to be used for *RECOVERY* PRE-LOGIN calls that
     * need device parameters when the user DOESN'T KNOW their password yet.
     *
     * @param string $username Your Instagram username.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function setUserWithoutPassword(
        $username)
    {
        if (empty($username) || !is_string($username)) {
            throw new \InvalidArgumentException('You must provide a username.');
        }

        // Switch the currently active user/pass if the username is different.
        // NOTE: Creates a user database (device) for the user if they're new!
        // NOTE: Because we don't know their password, we'll mark the user as
        // having "NOPASSWORD" as pwd. The user will fix that when/if they call
        // `login()` with the ACTUAL password, which will tell us what it is.
        // We CANNOT use an empty string since `_setUser()` will not allow that!
        // NOTE: If the user tries to look up themselves WHILE they are logged
        // in, we'll correctly NOT call `_setUser()` since they're already set.
        if ($this->username !== $username) {
            $this->_setUser('regular', $username, 'NOPASSWORD');
        }
    }

    /**
     * Updates the internal state after a successful login.
     *
     * @param Response\LoginResponse $response The login response.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     */
    protected function _updateLoginState(
        Response\LoginResponse $response)
    {
        if (self::$skipAccountValidation === false) {
            // This check is just protection against accidental bugs. It makes sure
            // that we always call this function with a *successful* login response!
            if (!$response instanceof Response\LoginResponse
                || !$response->isOk() || empty($response->getLoggedInUser()->getPk())) {
                throw new \InvalidArgumentException('Invalid login response provided to _updateLoginState().');
            }

            $this->isMaybeLoggedIn = true;
            $this->account_id = $response->getLoggedInUser()->getPk();
            $this->settings->set('account_id', $this->account_id);
            $this->settings->set('last_login', time());
        }
    }

    /**
     * Sends pre-login flow. This is required to emulate real device behavior.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    protected function _sendPreLoginFlow()
    {
        // Reset zero rating rewrite rules.
        $this->client->zeroRating()->reset();
        // Calling this non-token API will put a csrftoken in our cookie
        // jar. We must do this before any functions that require a token.

        if ($this->getIsAndroid()) {
            // Start emulating batch requests with Pidgeon Raw Client Time.
            $this->client->startEmulatingBatch();

            $this->event->sendInstagramDeviceIds($this->loginWaterfallId);
            $this->event->sendApkTestingExposure();
            $this->event->sendApkSignatureV2();
            $this->event->sendEmergencyPushInitialVersion();

            try {
                try {
                    $this->internal->fetchZeroRatingToken('token_expired', false);
                    //$this->account->setContactPointPrefill('prefill');
                    /*
                    $this->internal->sendGraph('455411352809009551099714876', [
                        'input' => [
                            'app_scoped_id'     => $this->uuid,
                            'appid'             => Constants::FACEBOOK_ANALYTICS_APPLICATION_ID,
                            'family_device_id'  => $this->phone_id,
                        ]
                    ], 'FamilyDeviceIDAppScopedDeviceIDSyncMutation', false); */
                } catch (\InstagramAPI\Exception\InstagramException $e) {
                    // pass
                }

                //$this->event->sendZeroCarrierSignal();
                //$this->internal->bootstrapMsisdnHeader();
                //$this->internal->readMsisdnHeader('default');

                /* QE SYNC DISABLED
                try {
                    $this->internal->syncDeviceFeatures(true);
                } catch (\Exception $e) {
                    // pass
                }
                */

                /*
                //THIS WAS USED IN PRELOGIN FOR OBTAINING DEVICE EXPERIMENTS AND PUBLIC KEY TO ENCRYPT PASSWORDS
                //SEEMS IT IS NOT BEING USED ANYMORE WITH BLOKS LOGIN
                */
                if (self::$useBloksLogin === false) {
                    $mobileConfigResponse = $this->internal->getMobileConfig(true)->getHttpResponse();
                    $this->settings->set('public_key', $mobileConfigResponse->getHeaderLine('ig-set-password-encryption-pub-key'));
                    $this->settings->set('public_key_id', $mobileConfigResponse->getHeaderLine('ig-set-password-encryption-key-id'));
                }

                //$this->internal->bootstrapMsisdnHeader();
                /*
                try {
                    //$this->internal->logAttribution();
                    $this->internal->sendGraph('455411352809009551099714876', [
                        'input' => [
                            'app_scoped_id'     => $this->uuid,
                            'appid'             => Constants::FACEBOOK_ANALYTICS_APPLICATION_ID,
                            'family_device_id'  => $this->phone_id,
                        ]
                    ], 'FamilyDeviceIDAppScopedDeviceIDSyncMutation', false);
                    $this->people->getNonExpiredFriendRequests();
                } catch (\InstagramAPI\Exception\InstagramException $e) {
                    // pass
                }
                */
            } finally {
                // Stops emulating batch requests.
                $this->client->stopEmulatingBatch();
            }

            // Start emulating batch requests with Pidgeon Raw Client Time.
            $this->client->startEmulatingBatch();
        } else {
            // IOS. PROBABLY USING BLOKS LOGIN INSTEAD OF THE FOLLOWING.
            $mobileConfigResponse = $this->internal->getMobileConfig(true)->getHttpResponse();
            $this->settings->set('public_key', $mobileConfigResponse->getHeaderLine('ig-set-password-encryption-pub-key'));
            $this->settings->set('public_key_id', $mobileConfigResponse->getHeaderLine('ig-set-password-encryption-key-id'));
        }

        try {
            //$this->internal->readMsisdnHeader('default', true);
            /*
            try {
                $this->account->setContactPointPrefill('prefill');
            } catch (\Exception $e) {
                //pass
            }
            */

            if ($this->getPlatform() === 'ios') {
                $this->account->getNamePrefill();
            }
            // WAS USED BEFORE BLOKS LOGIN
            if (self::$useBloksLogin === false) {
                $this->internal->getMobileConfig(true);
            }

            /* QE SYNC DISABLED
            try {
                $this->internal->syncDeviceFeatures(true, true);
            } catch (\Exception $e) {
                //pass
            }
            */
        } finally {
            // Stops emulating batch requests.
            $this->client->stopEmulatingBatch();
        }
    }

    /**
     * Registers available Push channels during the login flow.
     */
    protected function _registerPushChannels()
    {
        // Forcibly remove the stored token value if >24 hours old.
        // This prevents us from constantly re-registering the user's
        // "useless" token if they have stopped using the Push features.
        try {
            $lastFbnsToken = (int) $this->settings->get('last_fbns_token');
        } catch (\Exception $e) {
            $lastFbnsToken = null;
        }
        if (!$lastFbnsToken || $lastFbnsToken < strtotime('-24 hours')) {
            try {
                $this->settings->set('fbns_token', '');
            } catch (\Exception $e) {
                // Ignore storage errors.
            }

            return;
        }

        // Read our token from the storage.
        try {
            $fbnsToken = $this->settings->get('fbns_token');
        } catch (\Exception $e) {
            $fbnsToken = null;
        }
        if ($fbnsToken === null) {
            return;
        }

        // Register our last token since we had a fresh (age <24 hours) one,
        // or clear our stored token if we fail to register it again.
        try {
            $this->push->register('mqtt', $fbnsToken);
        } catch (\Exception $e) {
            try {
                $this->settings->set('fbns_token', '');
            } catch (\Exception $e) {
                // Ignore storage errors.
            }
        }
    }

    /**
     * Sends login flow. This is required to emulate real device behavior.
     *
     * @param bool $justLoggedIn       Whether we have just performed a full
     *                                 relogin (rather than doing a resume).
     * @param int  $appRefreshInterval See `login()` for description of this
     *                                 parameter.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse|null A login response if a
     *                                                   full (re-)login is
     *                                                   needed during the login
     *                                                   flow attempt, otherwise
     *                                                   `NULL`.
     */
    protected function _sendLoginFlow(
        $justLoggedIn,
        $appRefreshInterval = 21600)
    {
        if (!is_int($appRefreshInterval) || $appRefreshInterval < 0) {
            throw new \InvalidArgumentException("Instagram's app state refresh interval must be a positive integer.");
        }
        if ($appRefreshInterval > 21600) {
            throw new \InvalidArgumentException("Instagram's app state refresh interval is NOT allowed to be higher than 6 hours, and the lower the better!");
        }

        if (self::$skipLoginFlowAtMyOwnRisk) {
            return null;
        }

        // SUPER IMPORTANT:
        //
        // STOP trying to ask us to remove this code section!
        //
        // EVERY time the user presses their device's home button to leave the
        // app and then comes back to the app, Instagram does ALL of these things
        // to refresh its internal app state. We MUST emulate that perfectly,
        // otherwise Instagram will silently detect you as a "fake" client
        // after a while!
        //
        // You can configure the login's $appRefreshInterval in the function
        // parameter above, but you should keep it VERY frequent (definitely
        // NEVER longer than 6 hours), so that Instagram sees you as a real
        // client that keeps quitting and opening their app like a REAL user!
        //
        // Otherwise they WILL detect you as a bot and silently BLOCK features
        // or even ban you.
        //
        // You have been warned.
        if ($justLoggedIn) {
            // Reset zero rating rewrite rules.
            try {
                $this->client->zeroRating()->reset();
                $this->event->sendCellularDataOpt();
                $this->event->legacyFbTokenOnIgAccessControl('token_access', 'ig_login_util', 'LoginUtil');
                $this->event->legacyFbTokenOnIgAccessControl('token_access', 'ig_login_util', 'LoginUtil');
                $this->event->legacyFbTokenOnIgAccessControl('token_access', 'ig_login_util', 'LoginUtil');
                $this->event->sendDarkModeOpt();
            } catch (\Exception $e) {
                // pass
            }
            // Perform the "user has just done a full login" API flow.

            // Batch request 1
            $this->client->startEmulatingBatch();

            try {
                $this->internal->fetchZeroRatingToken('token_expired', false, false);
            } catch (\InstagramAPI\Exception\Checkpoint\ChallengeRequiredException $e) {
                throw $e;
            } catch (\Exception $e) {
                // pass
            }

            try {
                $this->account->getAccountFamily();
                if (self::$useBloksLogin === true) {
                    $this->internal->getBloksSaveCredentialsScreen();
                    sleep(mt_rand(1, 3));
                }
                //$this->internal->sendGraph('4703444349433374284764063878', ['is_pando' => true], 'AREffectConsentStateQuery', 'viewer', false, 'pando');

                $this->event->sendZeroCarrierSignal();
                $this->internal->getMobileConfig(true);
                $this->internal->getMobileConfig(false);

                $this->_registerPushChannels();
                $this->internal->getAsyncNdxIgSteps('NDX_IG4A_MA_FEATURE');
            } catch (\InstagramAPI\Exception\Checkpoint\ChallengeRequiredException $e) {
                throw $e;
            } catch (\Exception $e) {
                // pass
            } finally {
                // Stops emulating batch requests.
                $this->client->stopEmulatingBatch();
            }

            // Batch request 2
            $this->client->startEmulatingBatch();

            try {
                $requestId = \InstagramAPI\Signatures::generateUUID();
                $this->event->sendInstagramFeedRequestSent($requestId, 'cold_start_fetch');
                $feed = $this->timeline->getTimelineFeed(null, [
                    'reason'        => Constants::REASONS[0],
                    'request_id'    => $requestId,
                ]);
                $this->event->sendInstagramFeedRequestSent($requestId, 'cold_start_fetch', true);
                $items = $feed->getFeedItems();
                $items = array_slice($items, 0, 2);

                foreach ($items as $item) {
                    if ($item->getMediaOrAd() !== null) {
                        switch ($item->getMediaOrAd()->getMediaType()) {
                            case 1:
                                $this->event->sendOrganicMediaImpression($item->getMediaOrAd(), 'feed_timeline');
                                break;
                            case 2:
                                $this->event->sendOrganicViewedImpression($item->getMediaOrAd(), 'feed_timeline');
                                // Not playing the video.
                                break;
                            case 8:
                                $carouselItem = $item->getMediaOrAd()->getCarouselMedia()[0]; // First item of the carousel.
                                if ($carouselItem->getMediaType() === 1) {
                                    $this->event->sendOrganicMediaImpression($item->getMediaOrAd(), 'feed_timeline',
                                        [
                                            'feed_request_id'   => null,
                                        ]
                                    );
                                } else {
                                    $this->event->sendOrganicViewedImpression($item->getMediaOrAd(), 'feed_timeline', null, null, null,
                                        [
                                            'feed_request_id'   => null,
                                        ]
                                    );
                                }
                                break;
                        }
                    }
                    $previewComments = ($item->getMediaOrAd() === null) ? [] : $item->getMediaOrAd()->getPreviewComments();

                    if ($previewComments !== null) {
                        foreach ($previewComments as $comment) {
                            $this->event->sendCommentImpression($item->getMediaOrAd(), $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount(), 'feed_timeline');
                        }
                    }
                }
            } catch (\InstagramAPI\Exception\Checkpoint\ChallengeRequiredException $e) {
                throw $e;
            } catch (\Exception $e) {
                // pass
            } finally {
                // Stops emulating batch requests
                $this->client->stopEmulatingBatch();
            }

            // Batch request 3
            $this->client->startEmulatingBatch();

            try {
                //$this->internal->sendGraph('47034443410017494685272535358', [], 'AREffectConsentStateQuery', true);

                $requestId = \InstagramAPI\Signatures::generateUUID();
                $traySessionId = \InstagramAPI\Signatures::generateUUID();
                $this->event->sendStoriesRequest($traySessionId, $requestId, 'cold_start');

                $this->story->getReelsTrayFeed('cold_start', $requestId, $traySessionId);

                $this->internal->sendGraph('33052919472135518510885263591', ['is_pando' => true], 'BasicAdsOptInQuery', 'xfb_user_basic_ads_preferences', false, 'pando');

                $this->internal->getAsyncNdxIgSteps('NDX_IG_IMMERSIVE');

                try {
                    $this->account->getBadgeNotifications();
                    $this->settings->set('nav_started', 'true');
                    $this->internal->getLoomFetchConfig();
                } catch (\Exception $e) {
                    // pass
                }

                $this->internal->cdnRmd();

                $this->people->getSharePrefill();
                $this->internal->sendGraph('20527889286411119358419418429', [
                    'languages'     => ['nolang'],
                    'service_ids'   => ['MUTED_WORDS'],
                ], 'IGContentFilterDictionaryLookupQuery', 'ig_content_filter_dictionary_lookup_query', false, 'pando');
            } catch (\InstagramAPI\Exception\Checkpoint\ChallengeRequiredException $e) {
                throw $e;
            } catch (\Exception $e) {
                // pass
            } finally {
                // Stops emulating batch requests
                $this->client->stopEmulatingBatch();
            }

            // Batch request 4
            $this->client->startEmulatingBatch();

            try {
                $this->timeline->getUserFeed($this->account_id);
                $this->people->getInfoById($this->account_id, null, null, true); // Prefetch
                $this->highlight->getUserFeed($this->account_id);
                //$this->internal->logResurrectAttribution();
                //$this->internal->getDeviceCapabilitiesDecisions();
                $this->people->getCreatorInfo($this->account_id);
                $this->people->getBootstrapUsers();
                $this->media->getBlockedMedia();
                $this->internal->sendGraph('279018452917733073575656047369', ['is_pando' => true], 'FetchAttributionEventComplianceAction', 'fetch_attribution_event_compliance_action', true, 'pando');
                $this->people->getInfoById($this->account_id);
                $this->creative->sendSupportedCapabilities();
                $this->account->getProcessContactPointSignals();
            } catch (\InstagramAPI\Exception\Checkpoint\ChallengeRequiredException $e) {
                throw $e;
            } catch (\Exception $e) {
                // pass
            } finally {
                // Stops emulating batch requests
                $this->client->stopEmulatingBatch();
            }

            $this->client->startEmulatingBatch();

            try {
                $this->reel->discover();
                //$this->timeline->getTimelineFeed(); TODO
                $this->internal->sendGraph('18293997046226642457734318433', [
                    'is_pando' => true,
                    'input'    => [
                        'actor_id'              => $this->account_id,
                        'client_mutation_id'    => \InstagramAPI\Signatures::generateUUID(),
                        'events'                => [
                            'adid'                  => null,
                            'event_name'            => 'RESURRECTION',
                            'no_advertisement_id'   => false,
                        ],
                        'log_only'              => true,
                    ],
                ], 'ReportAttributionEventsMutation', 'report_attribution_events', false, 'pando');

                $this->internal->sendGraph('176575339118291536801493724773', ['is_pando' => true], 'IGFxLinkedAccountsQuery', 'fx_linked_accounts', false, 'pando');
                $this->internal->sendGraph('171864746410373358862136873197', ['is_pando' => true, 'data' => (object) []], 'ListCallsQuery', 'list_ig_calls_paginated_query', false, 'pando');
                /*$this->internal->sendGraph('13513772661704761708109730075', [
                    'is_pando' => true,
                    'input'    => [
                        'caller_context'    => [
                            'caller'                => 'StartupManager',
                            'function_credential'   => 'function_credential'
                        ],
                        'key'   => '1L1D'
                    ],
                ], 'IGOneLinkMiddlewareWhatsAppBusinessQuery', 'xfb_one_link_monoschema', false, 'pando');*/
                $this->internal->sendGraph('14088097634272511800572157181', [
                    'is_pando'         => true,
                    'client_states'    => [
                        'impression_count'      => 1,
                        'last_impression_time'  => 0,
                        'sequence_number'       => 0,
                        'variant'               => 'BOTTOMSHEET_XAR_REELS',
                    ],
                ], 'SyncCXPNoticeStateMutation', 'xcxp_sync_notice_state', false, 'pando');
                //$this->internal->sendGraph('176575339118291536801493724773', ['is_pando' => true], 'HasAvatarQuery', 'viewer', false, 'pando');
            } catch (\Exception $e) {
                // pass
            } finally {
                // Stops emulating batch requests
                $this->client->stopEmulatingBatch();
            }

            $this->client->startEmulatingBatch();

            try {
                //$this->account->getLinkageStatus();
                $this->internal->storeClientPushPermissions();
                //$this->business->getMonetizationProductsEligibilityData();
            } catch (\Exception $e) {
                // pass
            } finally {
                // Stops emulating batch requests
                $this->client->stopEmulatingBatch();
            }

            $this->client->startEmulatingBatch();

            try {
                try {
                    $this->internal->getViewableStatuses(true);
                    $this->account->getPresenceStatus();
                    $this->direct->getPresences();
                    $this->direct->getHasInteropUpgraded();
                    //$this->internal->getNotificationsSettings();
                } catch (\InstagramAPI\Exception\Checkpoint\ChallengeRequiredException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    // pass
                }
            } finally {
                // Stops emulating batch requests
                $this->client->stopEmulatingBatch();
            }

            $this->client->startEmulatingBatch();

            try {
                //$this->story->getReelsMediaFeed($this->account_id);
                $this->discover->getExploreFeed(null, \InstagramAPI\Signatures::generateUUID(), null, true);
                /*
                try {
                    $this->internal->sendGraph('2360595178779351530479091981', ['is_pando' => true, 'fb_profile_image_size' => 200], 'FxIGMasterAccountQuery', 'fxcal_accounts', false, 'pando');
                }  catch (\Exception $e) {
                    // pass
                }
                */
                /*
                $this->internal->sendGraph('21564406653994218282552117012', [
                    'is_pando' => true,
                    'configs_request' => [
                        'crosspost_app_surface_list' => [
                            [
                                'cross_app_share_type'  => 'CROSSPOST',
                                'destination_app'       => 'FB',
                                'destination_surface'   => 'REELS',
                                'source_surface'        => 'REELS'
                            ]
                        ],
                        'source_app' => 'IG'
                    ]
                ], 'CrossPostingContentCompatibilityConfig', 'xcxp_unified_crossposting_configs_root', false, 'pando');
                */
                $this->internal->getNotes();
                $this->reel->getShareToFbConfig();
                $this->internal->sendGraph('215817804115327440933115577895',
                    [
                        'is_pando'      => true,
                        'user_id'       => $this->account_id,
                        'query_params'  => [
                            'instruction_key_ids'   => ['4546360412114313'], // mobile config 57985
                            'refresh_only'          => true,
                        ],
                    ], 'IGAvatarStickersForKeysQuery', 'fetch__IGUser', false, 'pando');

                try {
                    $this->direct->getInbox(null, null, 20, 10, false, 'all', 'initial_snapshot');
                    $this->direct->getInbox(null, null, 0, null);

                    //$this->internal->sendGraph('243882031010379133527862780970', [], 'FBToIGDefaultAudienceBottomSheetQuery', false, 'graphservice');
                    //$this->internal->sendGraph('338246149711919572858330660779', ['is_pando' => true], 'FBToIGDefaultAudienceSettingQuery', true, 'pando');
                } catch (\InstagramAPI\Exception\Checkpoint\ChallengeRequiredException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    // pass
                }
                $this->internal->getQPFetch();
                $this->people->getSharePrefill();
                $this->account->getBadgeNotifications();

                /*
                if ($this->getPlatform() === 'android') {
                    $this->internal->getArlinkDownloadInfo();
                }
                */
            } catch (\InstagramAPI\Exception\Checkpoint\ChallengeRequiredException $e) {
                throw $e;
            } catch (\Exception $e) {
                // pass
            } finally {
                // Stops emulating batch requests
                $this->client->stopEmulatingBatch();
            }

            /*
            // Batch request 5
            $this->client->startEmulatingBatch();

            try {
                $this->internal->getQPCooldowns();
            } catch (\Exception $e) {
                // pass
            } finally {
                // Stops emulating batch requests
                $this->client->stopEmulatingBatch();
            }
            */

            /*
            try {
                $this->internal->getFacebookOTA();
            } catch (\Exception $e) {
            }
            */
        } else {
            $lastLoginTime = $this->settings->get('last_login');
            $isSessionExpired = $lastLoginTime === null || (time() - $lastLoginTime) > $appRefreshInterval;

            // Perform the "user has returned to their already-logged in app,
            // so refresh all feeds to check for news" API flow.
            if ($isSessionExpired) {
                // Batch Request 1
                $this->client->startEmulatingBatch();

                try {
                    // Act like a real logged in app client refreshing its news timeline.
                    // This also lets us detect if we're still logged in with a valid session.
                    try {
                        $this->story->getReelsTrayFeed('cold_start');
                    } catch (\InstagramAPI\Exception\LoginRequiredException $e) {
                        if (!self::$manuallyManageLoginException) {
                            if (isset($e->getResponse()->asArray()['logout_reason'])) {
                                $this->performPostForceLogoutActions($e->getResponse()->asArray()['logout_reason'], 'feed/reels_tray/');

                                return $this->_login($this->username, $this->password, true, $appRefreshInterval, true);
                            } else {
                                // If our session cookies are expired, we were now told to login,
                                // so handle that by running a forced relogin in that case!
                                return $this->_login($this->username, $this->password, true, $appRefreshInterval);
                            }
                        } else {
                            throw $e;
                        }
                    } catch (\InstagramAPI\Exception\EmptyResponseException | \InstagramAPI\Exception\ThrottledException $e) {
                        // This can have EmptyResponse, and that's ok.
                    }
                    $feed = $this->timeline->getTimelineFeed(null, [
                        'is_pull_to_refresh' => $isSessionExpired ? null : mt_rand(1, 3) < 3,
                    ]);

                    $items = $feed->getFeedItems();
                    $items = array_slice($items, 0, 2);

                    foreach ($items as $item) {
                        if ($item->getMediaOrAd() !== null) {
                            switch ($item->getMediaOrAd()->getMediaType()) {
                                case 1:
                                    $this->event->sendOrganicMediaImpression($item->getMediaOrAd(), 'feed_timeline');
                                    break;
                                case 2:
                                    $this->event->sendOrganicViewedImpression($item->getMediaOrAd(), 'feed_timeline');
                                    // Not playing the video.
                                    break;
                                case 8:
                                    $carouselItem = $item->getMediaOrAd()->getCarouselMedia()[0]; // First item of the carousel.
                                    if ($carouselItem->getMediaType() === 1) {
                                        $this->event->sendOrganicMediaImpression($item->getMediaOrAd(), 'feed_timeline',
                                            [
                                                'feed_request_id'   => null,
                                            ]
                                        );
                                    } else {
                                        $this->event->sendOrganicViewedImpression($item->getMediaOrAd(), 'feed_timeline', null, null, null,
                                            [
                                                'feed_request_id'   => null,
                                            ]
                                        );
                                    }
                                    break;
                            }
                        }
                        $previewComments = ($item->getMediaOrAd() === null) ? [] : $item->getMediaOrAd()->getPreviewComments();

                        if ($previewComments !== null) {
                            foreach ($previewComments as $comment) {
                                $this->event->sendCommentImpression($item->getMediaOrAd(), $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount(), 'feed_timeline');
                            }
                        }
                    }

                    try {
                        $this->people->getSharePrefill();
                        $this->people->getRecentActivityInbox();
                    } catch (\Exception $e) {
                        //pass
                    }
                } catch (\InstagramAPI\Exception\Checkpoint\ChallengeRequiredException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    // pass
                } finally {
                    // Stops emulating batch requests.
                    $this->client->stopEmulatingBatch();
                }

                // Batch Request 2
                $this->client->startEmulatingBatch();

                try {
                    //$this->people->getSharePrefill();
                    $this->people->getRecentActivityInbox();
                    $this->internal->writeSupportedCapabilities();
                    $this->people->getInfoById($this->account_id);
                    //$this->internal->getDeviceCapabilitiesDecisions();
                } catch (\InstagramAPI\Exception\Checkpoint\ChallengeRequiredException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    // pass
                } finally {
                    // Stops emulating batch requests.
                    $this->client->stopEmulatingBatch();
                }

                // Batch Request 3
                $this->client->startEmulatingBatch();

                try {
                    $this->direct->getPresences();
                    $this->discover->getExploreFeed('', \InstagramAPI\Signatures::generateUUID(), null, true, true);
                    $this->direct->getInbox();
                } catch (\InstagramAPI\Exception\EmptyResponseException | \InstagramAPI\Exception\ThrottledException $e) {
                    // This can have EmptyResponse, and that's ok.
                } finally {
                    // Stops emulating batch requests.
                    $this->client->stopEmulatingBatch();
                }

                $this->settings->set('last_login', time());

                // Generate and save a new application session ID.
                $this->session_id = Signatures::generateUUID();
                $this->settings->set('session_id', $this->session_id);

                // Do the rest of the "user is re-opening the app" API flow...
                //$this->people->getBootstrapUsers();

                // Start emulating batch requests with Pidgeon Raw Client Time.
                $this->client->startEmulatingBatch();

                try {
                    $this->internal->getQPFetch();
                    //$this->direct->getRankedRecipients('reshare', true);
                    //$this->direct->getRankedRecipients('raven', true);
                } catch (\InstagramAPI\Exception\Checkpoint\ChallengeRequiredException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    // pass
                } finally {
                    $this->_registerPushChannels();
                    // Stops emulating batch requests.
                    $this->client->stopEmulatingBatch();
                }
            } else {
                try {
                    $this->story->getReelsTrayFeed('cold_start');
                } catch (\InstagramAPI\Exception\LoginRequiredException $e) {
                    if (!self::$manuallyManageLoginException) {
                        if (isset($e->getResponse()->asArray()['logout_reason'])) {
                            $this->performPostForceLogoutActions($e->getResponse()->asArray()['logout_reason'], 'feed/reels_tray/');

                            return $this->_login($this->username, $this->password, true, $appRefreshInterval, true);
                        } else {
                            // If our session cookies are expired, we were now told to login,
                            // so handle that by running a forced relogin in that case!
                            return $this->_login($this->username, $this->password, true, $appRefreshInterval);
                        }
                    } else {
                        throw $e;
                    }
                } catch (\InstagramAPI\Exception\EmptyResponseException | \InstagramAPI\Exception\ThrottledException $e) {
                    // This can have EmptyResponse, and that's ok.
                }
            }

            // Users normally resume their sessions, meaning that their
            // experiments never get synced and updated. So sync periodically.
            $lastExperimentsTime = $this->settings->get('last_experiments');
            if ($lastExperimentsTime === null || (time() - intval($lastExperimentsTime)) > self::EXPERIMENTS_REFRESH) {
                // Start emulating batch requests with Pidgeon Raw Client Time.
                //$this->client->startEmulatingBatch();
                try {
                    $this->internal->getMobileConfig(true);
                    $this->internal->getMobileConfig(false);
                } catch (\Exception $e) {
                    // Ignore exception if 500 is received.
                }
            }

            // Update zero rating token when it has been expired.
            $expired = time() - (int) $this->settings->get('zr_expires');

            try {
                if ($expired > 0) {
                    $this->client->zeroRating()->reset();
                    $this->internal->fetchZeroRatingToken($expired > 7200 ? 'token_stale' : 'token_expired', false, false);
                    $this->event->sendZeroCarrierSignal();
                }
            } catch (\InstagramAPI\Exception\InstagramException $e) {
                // pass
            }
        }

        $this->event->forceSendBatch();
        // We've now performed a login or resumed a session. Forcibly write our
        // cookies to the storage, to ensure that the storage doesn't miss them
        // in case something bad happens to PHP after this moment.
        $this->client->saveCookieJar();

        return null;
    }

    /**
     * Perform post force logout actions.
     *
     * @param int    $logoutReason Logout reason.
     * @param string $path         Path.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     *
     * @see Instagram::login()
     */
    public function performPostForceLogoutActions(
        $logoutReason,
        $path)
    {
        return $this->request('accounts/perform_post_force_logout_actions/')
            ->setNeedsAuth(false)
            ->addPost('user_id', $this->account_id)
            ->addPost('_uid', $this->account_id)
            //->addPost('_csrftoken', $this->client->getToken())
            ->addPost('guid', $this->uuid)
            ->addPost('device_id', $this->device_id)
            ->addPost('path', $path)
            ->addPost('_uuid', $this->uuid)
            ->addPost('logout_reason', $logoutReason)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Log out of Instagram.
     *
     * WARNING: Most people should NEVER call `logout()`! Our library emulates
     * the Instagram app for Android, where you are supposed to stay logged in
     * forever. By calling this function, you will tell Instagram that you are
     * logging out of the APP. But you SHOULDN'T do that! In almost 100% of all
     * cases you want to *stay logged in* so that `login()` resumes your session!
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LogoutResponse
     *
     * @see Instagram::login()
     */
    public function logout()
    {
        $response = $this->request('accounts/logout/')
            ->setSignedPost(false)
            ->addPost('phone_id', $this->phone_id)
            //->addPost('_csrftoken', $this->client->getToken())
            ->addPost('guid', $this->uuid)
            ->addPost('device_id', $this->device_id)
            ->addPost('_uuid', $this->uuid)
            ->getResponse(new Response\LogoutResponse());

        // We've now logged out. Forcibly write our cookies to the storage, to
        // ensure that the storage doesn't miss them in case something bad
        // happens to PHP after this moment.
        $this->client->saveCookieJar();

        return $response;
    }

    /**
     * Checks if a parameter is enabled in the given experiment.
     *
     * @param string $experiment
     * @param string $param
     * @param bool   $default
     *
     * @return bool
     */
    public function isExperimentEnabled(
        $experiment,
        $param,
        $default = false)
    {
        return isset($this->experiments[$experiment][$param])
            ? in_array($this->experiments[$experiment][$param], ['enabled', 'true', '1'])
            : $default;
    }

    /**
     * Get a parameter value for the given experiment.
     *
     * @param string $experiment
     * @param string $param
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getExperimentParam(
        $experiment,
        $param,
        $default = null)
    {
        return isset($this->experiments[$experiment][$param])
            ? $this->experiments[$experiment][$param]
            : $default;
    }

    /**
     * Create a custom API request.
     *
     * Used internally, but can also be used by end-users if they want
     * to create completely custom API queries without modifying this library.
     *
     * @param string $url
     *
     * @return \InstagramAPI\Request
     */
    public function request(
        $url)
    {
        return new Request($this, $url, $this->customResolver);
    }
}
