<?php

namespace InstagramAPI;

use InstagramAPI\Media\Video\FFmpeg;
use InstagramAPI\Response\Model\Item;
use InstagramAPI\Response\Model\Location;

class Utils
{
    /**
     * Override for the default temp path used by various class functions.
     *
     * If this value is non-null, we'll use it. Otherwise we'll use the default
     * system tmp folder.
     *
     * TIP: If your default system temp folder isn't writable, it's NECESSARY
     * for you to set this value to another, writable path, like this:
     *
     * \InstagramAPI\Utils::$defaultTmpPath = '/home/example/foo/';
     */
    public static $defaultTmpPath = null;

    /**
     * Used for multipart boundary generation.
     *
     * @var string
     */
    const BOUNDARY_CHARS = '-_1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Length of generated multipart boundary.
     *
     * @var int
     */
    const BOUNDARY_LENGTH = 30;

    /**
     * Name of the detected ffmpeg executable.
     *
     * @var string|null
     *
     * @deprecated
     * @see FFmpeg::$defaultBinary
     */
    public static $ffmpegBin = null;

    /**
     * Name of the detected ffprobe executable, or FALSE if none found.
     *
     * @var string|bool|null
     */
    public static $ffprobeBin = null;

    /**
     * Last uploadId generated with microtime().
     *
     * @var string|null
     */
    protected static $_lastUploadId = null;

    /**
     * @param bool $useNano Whether to return result in usec instead of msec.
     *
     * @return string
     */
    public static function generateUploadId(
        $useNano = false)
    {
        $result = null;
        if (!$useNano) {
            while (true) {
                $result = number_format(round(microtime(true) * 1000), 0, '', '');
                if (self::$_lastUploadId !== null && $result === self::$_lastUploadId) {
                    // NOTE: Fast machines can process files too quick (< 0.001
                    // sec), which leads to identical upload IDs, which leads to
                    // "500 Oops, an error occurred" errors. So we sleep 0.001
                    // sec to guarantee different upload IDs per each call.
                    usleep(1000);
                } else { // OK!
                    self::$_lastUploadId = $result;
                    break;
                }
            }
        } else {
            // Emulate System.nanoTime().
            $result = number_format(microtime(true) - strtotime('Last Monday'), 6, '', '');
            // Append nanoseconds.
            $result .= str_pad((string) mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        }

        return $result;
    }

    /**
     * Calculates Java hashCode() for a given string.
     *
     * WARNING: This method is not Unicode-aware, so use it only on ANSI strings.
     *
     * @param string $string
     *
     * @return int
     *
     * @see https://en.wikipedia.org/wiki/Java_hashCode()#The_java.lang.String_hash_function
     */
    public static function hashCode(
        $string)
    {
        $result = 0;
        for ($i = 0, $len = strlen($string); $i < $len; $i++) {
            $result = (-$result + ($result << 5) + ord($string[$i])) & 0xFFFFFFFF;
        }
        if (PHP_INT_SIZE > 4) {
            if ($result > 0x7FFFFFFF) {
                $result -= 0x100000000;
            } elseif ($result < -0x80000000) {
                $result += 0x100000000;
            }
        }

        return $result;
    }

    /**
     * Reorders array by hashCode() of its keys.
     *
     * @param array $data
     *
     * @return array
     */
    public static function reorderByHashCode(
        array $data)
    {
        $hashCodes = [];
        foreach ($data as $key => $value) {
            $hashCodes[$key] = self::hashCode($key);
        }

        uksort($data, function ($a, $b) use ($hashCodes) {
            $a = $hashCodes[$a];
            $b = $hashCodes[$b];
            if ($a < $b) {
                return -1;
            } elseif ($a > $b) {
                return 1;
            } else {
                return 0;
            }
        });

        return $data;
    }

    /**
     * Generates random multipart boundary string.
     *
     * @return string
     */
    public static function generateMultipartBoundary()
    {
        $result = '';
        $max = strlen(self::BOUNDARY_CHARS) - 1;
        for ($i = 0; $i < self::BOUNDARY_LENGTH; $i++) {
            $result .= self::BOUNDARY_CHARS[mt_rand(0, $max)];
        }

        return $result;
    }

    /**
     * Generates user breadcrumb for use when posting a comment.
     *
     * @param int $size
     *
     * @return string
     */
    public static function generateUserBreadcrumb(
        $size)
    {
        $key = 'iN4$aGr0m';
        $date = (int) (microtime(true) * 1000);

        // typing time
        $term = rand(2, 3) * 1000 + $size * rand(15, 20) * 100;

        // android EditText change event occur count
        $text_change_event_count = round($size / rand(2, 3));
        if ($text_change_event_count == 0) {
            $text_change_event_count = 1;
        }

        // generate typing data
        $data = $size.' '.$term.' '.$text_change_event_count.' '.$date;

        return base64_encode(hash_hmac('sha256', $data, $key, true))."\n".base64_encode($data)."\n";
    }

    /**
     * Generates jazoest value for login.
     *
     * @param string $phoneId
     *
     * @return string
     */
    public static function generateJazoest(
        $phoneId)
    {
        $jazoestPrefix = '2';
        $array = str_split($phoneId);

        $i = 0;
        foreach ($array as $char) {
            $i += ord($char);
        }

        return $jazoestPrefix.strval($i);
    }

    /**
     * Generates Client Context value for Direct.
     *
     * @return int
     */
    public static function generateClientContext()
    {
        return (round(microtime(true) * 1000) << 22 | random_int(PHP_INT_MIN, PHP_INT_MAX) & 4194303) & PHP_INT_MAX;
    }

    /**
     * Encrypt password for authentication.
     *
     * @param string $password    Password.
     * @param string $publicKeyId Public Key ID.
     * @param string $publicKey   Public Key.
     * @param mixed  $isBloks
     *
     * @return string
     */
    public static function encryptPassword(
        $password,
        $publicKeyId,
        $publicKey,
        $isBloks = false)
    {
        $key = openssl_random_pseudo_bytes(32);
        $iv = openssl_random_pseudo_bytes(12);
        $time = time();

        // Fallback mode.
        if (empty($publicKey) || empty($publicKeyId)) {
            $publicKey = openssl_pkey_get_public(Constants::IG_LOGIN_DEFAULT_PUBLIC_KEY);
            $publicKeyId = Constants::IG_LOGIN_DEFAULT_PUBLIC_KEY_ID;
            openssl_public_encrypt($key, $encryptedAesKey, $publicKey);
        } else {
            openssl_public_encrypt($key, $encryptedAesKey, base64_decode($publicKey));
        }

        $encrypted = openssl_encrypt($password, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, strval($time));
        $payload = base64_encode("\x01" | pack('n', intval($publicKeyId)).$iv.pack('s', strlen($encryptedAesKey)).$encryptedAesKey.$tag.$encrypted);

        $version = ($isBloks) ? '1' : '4';

        return sprintf('#PWD_INSTAGRAM:%s:%s:%s', $version, $time, $payload);
    }

    /**
     * Encrypt password for Web authentication.
     *
     * WARNING: IN ORDER TO BE ABLE TO USE THIS FUNCTION
     *          YOU NEED TO INSTALL LIBSODIUM EXTENSION.
     *          LIBDOSIUM HAS SUPPORT FOR ELLIPTIC CURVES
     *          CRYPTOGRAPHY.
     *
     * @param string $password    Password.
     * @param string $publicKey   Public Key.
     * @param string $publicKeyId Public Key ID.
     *
     * @return string
     */
    public static function encryptPasswordForBrowser(
        $password,
        $publicKey = '8dd9aad29d9a614c338cff479f850d3ec57c525c33b3f702ab65e9e057fc087e',
        $publicKeyId = 87)
    {
        $key = openssl_random_pseudo_bytes(32);
        $iv = "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
        $time = time();

        $sealedBox = sodium_crypto_box_seal($key, hex2bin($publicKey));
        $encrypted = openssl_encrypt($password, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, strval($time));
        $payload = base64_encode("\x01" | pack('n', intval($publicKeyId)).pack('s', strlen($sealedBox)).$sealedBox.$tag.$encrypted);

        return sprintf('#PWD_INSTAGRAM_BROWSER:9:%s:%s', $time, $payload);
    }

    /**
     * Converts a hours/minutes/seconds timestamp to seconds.
     *
     * @param string $timeStr Either `HH:MM:SS[.###]` (24h-clock) or
     *                        `MM:SS[.###]` or `SS[.###]`. The `[.###]` is for
     *                        optional millisecond precision if wanted, such as
     *                        `00:01:01.149`.
     *
     * @throws \InvalidArgumentException If any part of the input is invalid.
     *
     * @return float The number of seconds, with decimals (milliseconds).
     */
    public static function hmsTimeToSeconds(
        $timeStr)
    {
        if (!is_string($timeStr)) {
            throw new \InvalidArgumentException('Invalid non-string timestamp.');
        }

        $sec = 0.0;
        foreach (array_reverse(explode(':', $timeStr)) as $offsetKey => $v) {
            if ($offsetKey > 2) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid input "%s" with too many components (max 3 is allowed "HH:MM:SS").',
                    $timeStr
                ));
            }

            // Parse component (supports "01" or "01.123" (milli-precision)).
            if ($v === '' || !preg_match('/^\d+(?:\.\d+)?$/', $v)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid non-digit or empty component "%s" in time string "%s".',
                    $v, $timeStr
                ));
            }
            if ($offsetKey !== 0 && strpos($v, '.') !== false) {
                throw new \InvalidArgumentException(sprintf(
                    'Unexpected period in time component "%s" in time string "%s". Only the seconds-component supports milliseconds.',
                    $v, $timeStr
                ));
            }

            // Convert the value to float and cap minutes/seconds to 60 (but
            // allow any number of hours).
            $v = (float) $v;
            $maxValue = $offsetKey < 2 ? 60 : -1;
            if ($maxValue >= 0 && $v > $maxValue) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid time component "%d" (its allowed range is 0-%d) in time string "%s".',
                    $v, $maxValue, $timeStr
                ));
            }

            // Multiply the current component of the "01:02:03" string with the
            // power of its offset. Hour-offset will be 2, Minutes 1 and Secs 0;
            // and "pow(60, 0)" will return 1 which is why seconds work too.
            $sec += pow(60, $offsetKey) * $v;
        }

        return $sec;
    }

    /**
     * Converts seconds to a hours/minutes/seconds timestamp.
     *
     * @param int|float $sec The number of seconds. Can have fractions (millis).
     *
     * @throws \InvalidArgumentException If any part of the input is invalid.
     *
     * @return string The time formatted as `HH:MM:SS.###` (`###` is millis).
     */
    public static function hmsTimeFromSeconds(
        $sec)
    {
        if (!is_int($sec) && !is_float($sec)) {
            throw new \InvalidArgumentException('Seconds must be a number.');
        }

        $wasNegative = false;
        if ($sec < 0) {
            $wasNegative = true;
            $sec = abs($sec);
        }

        $result = sprintf(
            '%02d:%02d:%06.3f', // "%06f" is because it counts the whole string.
            floor($sec / 3600),
            floor(fmod($sec / 60, 60)),
            fmod($sec, 60)
        );

        if ($wasNegative) {
            $result = '-'.$result;
        }

        return $result;
    }

    /**
     * Builds an Instagram media location JSON object in the correct format.
     *
     * This function is used whenever we need to send a location to Instagram's
     * API. All endpoints (so far) expect location data in this exact format.
     *
     * @param Location $location A model object representing the location.
     *
     * @throws \InvalidArgumentException If the location is invalid.
     *
     * @return string The final JSON string ready to submit as an API parameter.
     */
    public static function buildMediaLocationJSON(
        $location)
    {
        if (!$location instanceof Location) {
            throw new \InvalidArgumentException('The location must be an instance of \InstagramAPI\Response\Model\Location.');
        }

        // Forbid locations that came from Location::searchFacebook() and
        // Location::searchFacebookByPoint()! They have slightly different
        // properties, and they don't always contain all data we need. The
        // real application NEVER uses the "Facebook" endpoints for attaching
        // locations to media, and NEITHER SHOULD WE.
        if ($location->getFacebookPlacesId() !== null) {
            throw new \InvalidArgumentException('You are not allowed to use Location model objects from the Facebook-based location search functions. They are not valid media locations!');
        }

        // Core location keys that always exist.
        $obj = [
            'name'            => $location->getName(),
            'lat'             => $location->getLat(),
            'lng'             => $location->getLng(),
            'address'         => $location->getAddress(),
            'external_source' => $location->getExternalIdSource(),
        ];

        // Attach the location ID via a dynamically generated key.
        // NOTE: This automatically generates a key such as "facebook_places_id".
        $key = $location->getExternalIdSource().'_id';
        $obj[$key] = $location->getExternalId();

        // Ensure that all keys are listed in the correct hash order.
        $obj = self::reorderByHashCode($obj);

        return json_encode($obj);
    }

    /**
     * Check for ffprobe dependency.
     *
     * TIP: If your binary isn't findable via the PATH environment locations,
     * you can manually set the correct path to it. Before calling any functions
     * that need FFprobe, you must simply assign a manual value (ONCE) to tell
     * us where to find your FFprobe, like this:
     *
     * \InstagramAPI\Utils::$ffprobeBin = '/home/exampleuser/ffmpeg/bin/ffprobe';
     *
     * @return string|bool Name of the library if present, otherwise FALSE.
     */
    public static function checkFFPROBE()
    {
        // We only resolve this once per session and then cache the result.
        if (self::$ffprobeBin === null) {
            @exec('ffprobe -version 2>&1', $output, $statusCode);
            if ($statusCode === 0) {
                self::$ffprobeBin = 'ffprobe';
            } else {
                self::$ffprobeBin = false; // Nothing found!
            }
        }

        return self::$ffprobeBin;
    }

    /**
     * Verifies a user tag.
     *
     * Ensures that the input strictly contains the exact keys necessary for
     * user tag, and with proper values for them. We cannot validate that the
     * user-id actually exists, but that's the job of the library user!
     *
     * @param mixed $userTag An array containing the user ID and the tag position.
     *                       Example: ['position'=>[0.5,0.5],'user_id'=>'123'].
     *
     * @throws \InvalidArgumentException If the tag is invalid.
     */
    public static function throwIfInvalidUserTag(
        $userTag)
    {
        // NOTE: We can use "array" typehint, but it doesn't give us enough freedom.
        if (!is_array($userTag)) {
            throw new \InvalidArgumentException('User tag must be an array.');
        }

        // Check for required keys.
        $requiredKeys = ['position', 'user_id'];
        $missingKeys = array_diff($requiredKeys, array_keys($userTag));
        if (!empty($missingKeys)) {
            throw new \InvalidArgumentException(sprintf('Missing keys "%s" for user tag array.', implode('", "', $missingKeys)));
        }

        // Verify this product tag entry, ensuring that the entry is format
        // ['position'=>[0.0,1.0],'user_id'=>'123'] and nothing else.
        foreach ($userTag as $key => $value) {
            switch ($key) {
                case 'user_id':
                    if (!is_int($value) && !ctype_digit($value)) {
                        throw new \InvalidArgumentException('User ID must be an integer.');
                    }
                    if ($value < 0) {
                        throw new \InvalidArgumentException('User ID must be a positive integer.');
                    }
                    break;
                case 'position':
                    try {
                        self::throwIfInvalidPosition($value);
                    } catch (\InvalidArgumentException $e) {
                        throw new \InvalidArgumentException(sprintf('Invalid user tag position: %s', $e->getMessage()), $e->getCode(), $e);
                    }
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Invalid key "%s" in user tag array.', $key));
            }
        }
    }

    /**
     * Verifies an array of media usertags.
     *
     * Ensures that the input strictly contains the exact keys necessary for
     * usertags, and with proper values for them. We cannot validate that the
     * user-id's actually exist, but that's the job of the library user!
     *
     * @param mixed $usertags The array of usertags, optionally with the "in" or
     *                        "removed" top-level keys holding the usertags. Example:
     *                        ['in'=>[['position'=>[0.5,0.5],'user_id'=>'123'], ...]].
     *
     * @throws \InvalidArgumentException If any tags are invalid.
     */
    public static function throwIfInvalidUsertags(
        $usertags)
    {
        // NOTE: We can use "array" typehint, but it doesn't give us enough freedom.
        if (!is_array($usertags)) {
            throw new \InvalidArgumentException('Usertags must be an array.');
        }

        if (empty($usertags)) {
            throw new \InvalidArgumentException('Empty usertags array.');
        }

        foreach ($usertags as $k => $v) {
            if (!is_array($v)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid usertags array. The value for key "%s" must be an array.', $k
                ));
            }
            // Skip the section if it's empty.
            if (empty($v)) {
                continue;
            }
            // Handle ['in'=>[...], 'removed'=>[...]] top-level keys since
            // this input contained top-level array keys containing the usertags.
            switch ($k) {
                case 'in':
                    foreach ($v as $idx => $userTag) {
                        try {
                            self::throwIfInvalidUserTag($userTag);
                        } catch (\InvalidArgumentException $e) {
                            throw new \InvalidArgumentException(
                                sprintf('Invalid usertag at index "%d": %s', $idx, $e->getMessage()),
                                $e->getCode(),
                                $e
                            );
                        }
                    }
                    break;
                case 'removed':
                    // Check the array of userids to remove.
                    foreach ($v as $userId) {
                        if (!ctype_digit($userId) && (!is_int($userId) || $userId < 0)) {
                            throw new \InvalidArgumentException('Invalid user ID in usertags "removed" array.');
                        }
                    }
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Invalid key "%s" in user tags array.', $k));
            }
        }
    }

    /**
     * Verifies an array of product tags.
     *
     * Ensures that the input strictly contains the exact keys necessary for
     * product tags, and with proper values for them. We cannot validate that the
     * product's-id actually exists, but that's the job of the library user!
     *
     * @param mixed $productTags The array of usertags, optionally with the "in" or
     *                           "removed" top-level keys holding the usertags. Example:
     *                           ['in'=>[['position'=>[0.5,0.5],'product_id'=>'123'], ...]].
     *
     * @throws \InvalidArgumentException If any tags are invalid.
     */
    public static function throwIfInvalidProductTags(
        $productTags)
    {
        // NOTE: We can use "array" typehint, but it doesn't give us enough freedom.
        if (!is_array($productTags)) {
            throw new \InvalidArgumentException('Products tags must be an array.');
        }

        if (empty($productTags)) {
            throw new \InvalidArgumentException('Empty product tags array.');
        }

        foreach ($productTags as $k => $v) {
            if (!is_array($v)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid product tags array. The value for key "%s" must be an array.', $k
                ));
            }

            // Skip the section if it's empty.
            if (empty($v)) {
                continue;
            }

            // Handle ['in'=>[...], 'removed'=>[...]] top-level keys since
            // this input contained top-level array keys containing the product tags.
            switch ($k) {
                case 'in':
                    // Check the array of product tags to insert.
                    foreach ($v as $idx => $productTag) {
                        try {
                            self::throwIfInvalidProductTag($productTag);
                        } catch (\InvalidArgumentException $e) {
                            throw new \InvalidArgumentException(
                                sprintf('Invalid product tag at index "%d": %s', $idx, $e->getMessage()),
                                $e->getCode(),
                                $e
                            );
                        }
                    }
                    break;
                case 'removed':
                    // Check the array of product_id to remove.
                    foreach ($v as $productId) {
                        if (!ctype_digit($productId) && (!is_int($productId) || $productId < 0)) {
                            throw new \InvalidArgumentException('Invalid product ID in product tags "removed" array.');
                        }
                    }
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Invalid key "%s" in product tags array.', $k));
            }
        }
    }

    /**
     * Verifies a product tag.
     *
     * Ensures that the input strictly contains the exact keys necessary for
     * product tag, and with proper values for them. We cannot validate that the
     * product-id actually exists, but that's the job of the library user!
     *
     * @param mixed $productTag An array containing the product ID and the tag position.
     *                          Example: ['position'=>[0.5,0.5],'product_id'=>'123'].
     *
     * @throws \InvalidArgumentException If any tags are invalid.
     */
    public static function throwIfInvalidProductTag(
        $productTag)
    {
        // NOTE: We can use "array" typehint, but it doesn't give us enough freedom.
        if (!is_array($productTag)) {
            throw new \InvalidArgumentException('Product tag must be an array.');
        }

        // Check for required keys.
        $requiredKeys = ['position', 'product_id'];
        $missingKeys = array_diff($requiredKeys, array_keys($productTag));
        if (!empty($missingKeys)) {
            throw new \InvalidArgumentException(sprintf('Missing keys "%s" for product tag array.', implode('", "', $missingKeys)));
        }

        // Verify this product tag entry, ensuring that the entry is format
        // ['position'=>[0.0,1.0],'product_id'=>'123'] and nothing else.
        foreach ($productTag as $key => $value) {
            switch ($key) {
                case 'product_id':
                    if (!is_int($value) && !ctype_digit($value)) {
                        throw new \InvalidArgumentException('Product ID must be an integer.');
                    }
                    if ($value < 0) {
                        throw new \InvalidArgumentException('Product ID must be a positive integer.');
                    }
                    break;
                case 'position':
                    try {
                        self::throwIfInvalidPosition($value);
                    } catch (\InvalidArgumentException $e) {
                        throw new \InvalidArgumentException(sprintf('Invalid product tag position: %s', $e->getMessage()), $e->getCode(), $e);
                    }
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Invalid key "%s" in product tag array.', $key));
            }
        }
    }

    /**
     * Verifies a position.
     *
     * @param mixed $position An array containing a position coordinates.
     *
     * @throws \InvalidArgumentException
     */
    public static function throwIfInvalidPosition(
        $position)
    {
        if (!is_array($position)) {
            throw new \InvalidArgumentException('Position must be an array.');
        }

        if (!isset($position[0])) {
            throw new \InvalidArgumentException('X coordinate is required.');
        }
        $x = $position[0];
        if (!is_int($x) && !is_float($x)) {
            throw new \InvalidArgumentException('X coordinate must be a number.');
        }
        if ($x < 0.0 || $x > 1.0) {
            throw new \InvalidArgumentException('X coordinate must be a float between 0.0 and 1.0.');
        }

        if (!isset($position[1])) {
            throw new \InvalidArgumentException('Y coordinate is required.');
        }
        $y = $position[1];
        if (!is_int($y) && !is_float($y)) {
            throw new \InvalidArgumentException('Y coordinate must be a number.');
        }
        if ($y < 0.0 || $y > 1.0) {
            throw new \InvalidArgumentException('Y coordinate must be a float between 0.0 and 1.0.');
        }
    }

    /**
     * Verifies that a single hashtag is valid.
     *
     * This function enforces the following requirements: It must be a string,
     * at least 1 character long, and cannot contain the "#" character itself.
     *
     * @param mixed $hashtag The hashtag to check (should be string but we
     *                       accept anything for checking purposes).
     *
     * @throws \InvalidArgumentException
     */
    public static function throwIfInvalidHashtag(
        $hashtag)
    {
        if (!is_string($hashtag) || !strlen($hashtag)) {
            throw new \InvalidArgumentException('Hashtag must be a non-empty string.');
        }
        // Perform an UTF-8 aware search for the illegal "#" symbol (anywhere).
        // NOTE: We must use mb_strpos() to support international tags.
        if (mb_strpos($hashtag, '#') !== false) {
            throw new \InvalidArgumentException(sprintf(
                'Hashtag "%s" is not allowed to contain the "#" character.',
                $hashtag
            ));
        }
    }

    /**
     * Verifies a rank token.
     *
     * @param string $rankToken
     *
     * @throws \InvalidArgumentException
     */
    public static function throwIfInvalidRankToken(
        $rankToken
    ) {
        if (!Signatures::isValidUUID($rankToken)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid rank token.', $rankToken));
        }
    }

    /**
     * Verifies an array of story poll.
     *
     * @param array[] $storyPoll Array with story poll key-value pairs.
     *
     * @throws \InvalidArgumentException If it's missing keys or has invalid values.
     */
    public static function throwIfInvalidStoryPoll(
        array $storyPoll)
    {
        $requiredKeys = ['question', 'viewer_vote', 'viewer_can_vote', 'tallies', 'is_sticker', 'color', 'type', 'poll_id', 'tap_state_str_id', 'is_multi_option_poll'];

        if (count($storyPoll) !== 1) {
            throw new \InvalidArgumentException(sprintf('Only one story poll is permitted. You added %d story polls.', count($storyPoll)));
        }

        // Ensure that all keys exist.
        $missingKeys = array_keys(array_diff_key(['question' => 1, 'viewer_vote' => 1, 'viewer_can_vote' => 1, 'tallies' => 1, 'is_sticker' => 1, 'color' => 1, 'type' => 1, 'poll_id' => 1, 'tap_state_str_id' => 1, 'is_multi_option_poll' => 1], $storyPoll[0]));
        if (count($missingKeys)) {
            throw new \InvalidArgumentException(sprintf('Missing keys "%s" for story poll array.', implode(', ', $missingKeys)));
        }

        foreach ($storyPoll[0] as $k => $v) {
            switch ($k) {
                case 'question':
                    if (!is_string($v)) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story poll array-key "%s".', $v, $k));
                    }
                    break;
                case 'viewer_vote':
                    if ($v !== 0) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story poll array-key "%s".', $v, $k));
                    }
                    break;
                case 'viewer_can_vote':
                case 'is_sticker':
                    if (!is_bool($v) && $v !== true) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story poll array-key "%s".', $v, $k));
                    }
                    break;
                case 'tallies':
                    if (!is_array($v)) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story poll array-key "%s".', $v, $k));
                    }
                    self::_throwIfInvalidStoryPollTallies($v);
                    break;
            }
        }
        self::_throwIfInvalidStoryStickerPlacement(array_diff_key($storyPoll[0], array_flip($requiredKeys)), 'polls');
    }

    /**
     * Verifies an array of story slider.
     *
     * @param array[] $storySlider Array with story slider key-value pairs.
     *
     * @throws \InvalidArgumentException If it's missing keys or has invalid values.
     */
    public static function throwIfInvalidStorySlider(
        array $storySlider)
    {
        $requiredKeys = ['viewer_vote', 'viewer_can_vote', 'slider_vote_average', 'slider_vote_count', 'emoji', 'background_color', 'text_color', 'is_sticker'];

        if (count($storySlider) !== 1) {
            throw new \InvalidArgumentException(sprintf('Only one story slider is permitted. You added %d story sliders.', count($storySlider)));
        }

        // Ensure that all keys exist.
        $missingKeys = array_keys(array_diff_key(['viewer_vote' => 1, 'viewer_can_vote' => 1, 'slider_vote_average' => 1, 'slider_vote_count' => 1, 'emoji' => 1, 'background_color' => 1, 'text_color' => 1, 'is_sticker' => 1], $storySlider[0]));
        if (count($missingKeys)) {
            throw new \InvalidArgumentException(sprintf('Missing keys "%s" for story slider array.', implode(', ', $missingKeys)));
        }

        foreach ($storySlider[0] as $k => $v) {
            switch ($k) {
                case 'question':
                    if (!is_string($v)) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story slider array-key "%s".', $v, $k));
                    }
                    $requiredKeys[] = 'question';
                    break;
                case 'viewer_vote':
                case 'slider_vote_count':
                case 'slider_vote_average':
                    if ($v !== 0) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story slider array-key "%s".', $v, $k));
                    }
                    break;
                case 'background_color':
                case 'text_color':
                    if (!preg_match('/^[0-9a-fA-F]{6}$/', substr($v, 1))) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story slider array-key "%s".', $v, $k));
                    }
                    break;
                case 'emoji':
                    //TODO REQUIRES EMOJI VALIDATION
                    break;
                case 'viewer_can_vote':
                    if (!is_bool($v) && $v !== false) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story poll array-key "%s".', $v, $k));
                    }
                    break;
                case 'is_sticker':
                    if (!is_bool($v) && $v !== true) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story poll array-key "%s".', $v, $k));
                    }
                    break;
            }
        }
        self::_throwIfInvalidStoryStickerPlacement(array_diff_key($storySlider[0], array_flip($requiredKeys)), 'sliders');
    }

    /**
     * Verifies an array of story question.
     *
     * @param array $storyQuestion Array with story question key-value pairs.
     *
     * @throws \InvalidArgumentException If it's missing keys or has invalid values.
     */
    public static function throwIfInvalidStoryQuestion(
        array $storyQuestion)
    {
        $requiredKeys = ['z', 'viewer_can_interact', 'background_color', 'profile_pic_url', 'question_type', 'question', 'text_color', 'is_sticker'];

        if (count($storyQuestion) !== 1) {
            throw new \InvalidArgumentException(sprintf('Only one story question is permitted. You added %d story questions.', count($storyQuestion)));
        }

        // Ensure that all keys exist.
        $missingKeys = array_keys(array_diff_key(['viewer_can_interact' => 1, 'background_color' => 1, 'profile_pic_url' => 1, 'question_type' => 1, 'question' => 1, 'text_color' => 1, 'is_sticker' => 1], $storyQuestion[0]));
        if (count($missingKeys)) {
            throw new \InvalidArgumentException(sprintf('Missing keys "%s" for story question array.', implode(', ', $missingKeys)));
        }

        foreach ($storyQuestion[0] as $k => $v) {
            switch ($k) {
                case 'z': // May be used for AR in the future, for now it's always 0.
                    if ($v !== 0) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story question array-key "%s".', $v, $k));
                    }
                    break;
                case 'viewer_can_interact':
                    if (!is_bool($v) || $v !== false) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story question array-key "%s".', $v, $k));
                    }
                    break;
                case 'background_color':
                case 'text_color':
                    if (!preg_match('/^[0-9a-fA-F]{6}$/', substr($v, 1))) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story question array-key "%s".', $v, $k));
                    }
                    break;
                case 'question_type':
                    // At this time only text questions are supported.
                    if (!is_string($v) || $v !== 'text') {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story question array-key "%s".', $v, $k));
                    }
                    break;
                case 'question':
                    if (!is_string($v)) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story question array-key "%s".', $v, $k));
                    }
                    break;
                case 'profile_pic_url':
                    if (!self::hasValidWebURLSyntax($v)) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story question array-key "%s".', $v, $k));
                    }
                    break;
                case 'is_sticker':
                    if (!is_bool($v) && $v !== true) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story question array-key "%s".', $v, $k));
                    }
                    break;
            }
        }
        self::_throwIfInvalidStoryStickerPlacement(array_diff_key($storyQuestion[0], array_flip($requiredKeys)), 'questions');
    }

    /**
     * Verifies an array of story countdown.
     *
     * @param array $storyCountdown Array with story countdown key-value pairs.
     *
     * @throws \InvalidArgumentException If it's missing keys or has invalid values.
     */
    public static function throwIfInvalidStoryCountdown(
        array $storyCountdown)
    {
        $requiredKeys = ['z', 'text', 'text_color', 'start_background_color', 'end_background_color', 'digit_color', 'digit_card_color', 'end_ts', 'following_enabled', 'is_sticker'];

        if (count($storyCountdown) !== 1) {
            throw new \InvalidArgumentException(sprintf('Only one story countdown is permitted. You added %d story countdowns.', count($storyCountdown)));
        }

        // Ensure that all keys exist.
        $missingKeys = array_keys(array_diff_key(['z' => 1, 'text' => 1, 'text_color' => 1, 'start_background_color' => 1, 'end_background_color' => 1, 'digit_color' => 1, 'digit_card_color' => 1, 'end_ts' => 1, 'following_enabled' => 1, 'is_sticker' => 1], $storyCountdown[0]));
        if (count($missingKeys)) {
            throw new \InvalidArgumentException(sprintf('Missing keys "%s" for story countdown array.', implode(', ', $missingKeys)));
        }

        foreach ($storyCountdown[0] as $k => $v) {
            switch ($k) {
                case 'z': // May be used for AR in the future, for now it's always 0.
                    if ($v !== 0) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story countdown array-key "%s".', $v, $k));
                    }
                    break;
                case 'text':
                    if (!is_string($v)) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story countdown array-key "%s".', $v, $k));
                    }
                    break;
                case 'text_color':
                case 'start_background_color':
                case 'end_background_color':
                case 'digit_color':
                    if (!preg_match('/^[0-9a-fA-F]{6}$/', substr($v, 1))) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story countdown array-key "%s".', $v, $k));
                    }
                    break;
                case 'digit_card_color':
                    if (!preg_match('/^[0-9a-fA-F]{8}$/', substr($v, 1))) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story countdown array-key "%s".', $v, $k));
                    }
                    break;
                case 'end_ts':
                    if (!is_int($v)) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story countdown array-key "%s".', $v, $k));
                    }
                    break;
                case 'following_enabled':
                    if (!is_bool($v)) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story countdown array-key "%s".', $v, $k));
                    }
                    break;
                case 'is_sticker':
                    if (!is_bool($v) && $v !== true) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story countdown array-key "%s".', $v, $k));
                    }
                    break;
            }
        }
        self::_throwIfInvalidStoryStickerPlacement(array_diff_key($storyCountdown[0], array_flip($requiredKeys)), 'countdowns');
    }

    /**
     * Verifies an array of story quiz.
     *
     * @param array $storyQuiz Array with story quiz key-value pairs.
     *
     * @throws \InvalidArgumentException If it's missing keys or has invalid values.
     */
    public static function throwIfInvalidStoryQuiz(
        array $storyQuiz)
    {
        $requiredKeys = ['z', 'question', 'options', 'correct_answer', 'viewer_can_answer', 'viewer_answer', 'text_color', 'start_background_color', 'end_background_color', 'is_sticker'];

        if (count($storyQuiz) !== 1) {
            throw new \InvalidArgumentException(sprintf('Only one story quiz is permitted. You added %d story quizzes.', count($storyQuiz)));
        }

        // Ensure that all keys exist.
        $missingKeys = array_keys(array_diff_key(['z' => 1, 'question' => 1, 'options' => 1, 'correct_answer' => 1, 'viewer_can_answer' => 1, 'viewer_answer' => 1, 'text_color' => 1, 'start_background_color' => 1, 'end_background_color' => 1, 'is_sticker' => 1], $storyQuiz[0]));
        if (count($missingKeys)) {
            throw new \InvalidArgumentException(sprintf('Missing keys "%s" for story quiz array.', implode(', ', $missingKeys)));
        }

        foreach ($storyQuiz[0] as $k => $v) {
            switch ($k) {
                case 'z': // May be used for AR in the future, for now it's always 0.
                    if ($v !== 0) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story quiz array-key "%s".', $v, $k));
                    }
                    break;
                case 'question':
                    if (!is_string($v)) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story quiz array-key "%s".', $v, $k));
                    }
                    break;
                case 'text_color':
                case 'start_background_color':
                case 'end_background_color':
                    if (!preg_match('/^[0-9a-fA-F]{6}$/', substr($v, 1))) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story quiz array-key "%s".', $v, $k));
                    }
                    break;
                case 'viewer_answer':
                    if ($v !== -1) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story quiz array-key "%s".', $v, $k));
                    }
                    break;
                case 'viewer_can_answer':
                    if (!is_bool($v) && $v !== false) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story quiz array-key "%s".', $v, $k));
                    }
                    break;
                case 'is_sticker':
                    if (!is_bool($v) && $v !== true) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story quiz array-key "%s".', $v, $k));
                    }
                    break;
                case 'options':
                    $optionCount = 0;
                    foreach ($v as $curOption) {
                        $optionCount++;
                        if (!is_string($curOption['text'])) {
                            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story quiz array-key "%s".', $v, $k));
                        }
                        if ($curOption['count'] !== 0) {
                            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story quiz array-key "%s".', $v, $k));
                        }
                    }
                    if ($optionCount < 2 || $optionCount > 4) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story quiz array-key "%s".', $v, $k));
                    }
                    break;
            }
        }
        self::_throwIfInvalidStoryStickerPlacement(array_diff_key($storyQuiz[0], array_flip($requiredKeys)), 'quizzes');
    }

    /**
     * Verifies an array of chat sticker.
     *
     * @param array $chatSticker Array with chat story key-value pairs.
     *
     * @throws \InvalidArgumentException If it's missing keys or has invalid values.
     */
    public static function throwIfInvalidChatSticker(
        array $chatSticker)
    {
        $requiredKeys = ['z', 'type', 'text', 'start_background_color', 'end_background_color', 'is_pinned'];

        if (count($chatSticker) !== 1) {
            throw new \InvalidArgumentException(sprintf('Only one chat sticker is permitted. You added %d chat stickers.', count($chatSticker)));
        }

        // Ensure that all keys exist.
        $missingKeys = array_keys(array_diff_key(['z' => 1, 'type' => 1, 'start_background_color' => 1, 'end_background_color' => 1, 'is_pinned' => 1], $chatSticker[0]));
        if (count($missingKeys)) {
            throw new \InvalidArgumentException(sprintf('Missing keys "%s" for chat sticker array.', implode(', ', $missingKeys)));
        }

        foreach ($chatSticker[0] as $k => $v) {
            switch ($k) {
                case 'z': // May be used for AR in the future, for now it's always 0.
                    if ($v !== 0) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for chat sticker array-key "%s".', $v, $k));
                    }
                    break;
                case 'type':
                case 'text':
                    if (!is_string($v)) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for chat sticker array-key "%s".', $v, $k));
                    }
                    break;
                case 'start_background_color':
                case 'end_background_color':
                    if (!preg_match('/^[0-9a-fA-F]{6}$/', substr($v, 1))) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for chat sticker array-key "%s".', $v, $k));
                    }
                    break;
                case 'is_pinned':
                    if (!is_bool($v) && $v !== false) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for chat sticker array-key "%s".', $v, $k));
                    }
                    break;
            }
        }
        self::_throwIfInvalidStoryStickerPlacement(array_diff_key($chatSticker[0], array_flip($requiredKeys)), 'chat_sticker');
    }

    /**
     * Verifies if tallies are valid.
     *
     * @param array[] $tallies Array with story poll key-value pairs.
     *
     * @throws \InvalidArgumentException If it's missing keys or has invalid values.
     */
    protected static function _throwIfInvalidStoryPollTallies(
        array $tallies)
    {
        $requiredKeys = ['text', 'count', 'font_size'];
        if (count($tallies) < 2 || count($tallies) > 4) {
            throw new \InvalidArgumentException(sprintf('Invalid number of tallies.'));
        }

        foreach ($tallies as $tallie) {
            $missingKeys = array_keys(array_diff_key(['text' => 1, 'count' => 1, 'font_size' => 1], $tallie));

            if (count($missingKeys)) {
                throw new \InvalidArgumentException(sprintf('Missing keys "%s" for location array.', implode(', ', $missingKeys)));
            }
            foreach ($tallie as $k => $v) {
                if (!in_array($k, $requiredKeys, true)) {
                    throw new \InvalidArgumentException(sprintf('Invalid key "%s" for story poll tallies.', $k));
                }
                switch ($k) {
                    case 'text':
                        if (!is_string($v)) {
                            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for tallies array-key "%s".', $v, $k));
                        }
                        break;
                    case 'count':
                        if ($v !== 0) {
                            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for tallies array-key "%s".', $v, $k));
                        }
                        break;
                    case 'font_size':
                        $v = floatval($v);
                        if (!is_float($v) || ($v < 17.5 || $v > 64.0)) {
                            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for tallies array-key "%s".', $v, $k));
                        }
                        break;
                }
            }
        }
    }

    /**
     * Verifies an array of story mentions.
     *
     * @param array[] $storyMentions The array of all story mentions.
     *
     * @throws \InvalidArgumentException If it's missing keys or has invalid values.
     */
    public static function throwIfInvalidStoryMentionSticker(
        array $storyMentions)
    {
        $requiredKeys = ['user_id', 'display_type', 'is_sticker', 'tap_state', 'tap_state_str_id', 'type'];

        foreach ($storyMentions as $mention) {
            $missingKeys = array_keys(array_diff_key(['user_id' => 1, 'display_type' => 1, 'is_sticker' => 1, 'tap_state' => 1, 'tap_state_str_id' => 1, 'type' => 1], $mention));
            if (count($missingKeys)) {
                throw new \InvalidArgumentException(sprintf('Missing keys "%s" for story mention.', implode(', ', $missingKeys)));
            }

            foreach ($mention as $k => $v) {
                switch ($k) {
                    case 'user_id':
                        if (!ctype_digit($v) && (!is_int($v) || $v < 0)) {
                            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story mention array-key "%s".', $v, $k));
                        }
                        break;
                    case 'display_type':
                        if ($v !== 'mention_username') {
                            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story mention array-key "%s".', $v, $k));
                        }
                        break;
                    case 'is_sticker':
                        if (!is_bool($v)) {
                            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story mention array-key "%s".', $v, $k));
                        }
                        break;
                    case 'tap_state':
                        if ($v < 0 || $v > 3) {
                            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story mention array-key "%s".', $v, $k));
                        }
                        break;
                    case 'tap_state_str_id':
                        if ($v !== 'mention_sticker_subtle' && $v !== 'mention_sticker_hero' && $v !== 'mention_sticker_rainbow' && $v !== 'mention_sticker_gradient' && $v !== 'mention_text') {
                            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story mention array-key "%s".', $v, $k));
                        }
                        break;
                    case 'type':
                        if ($v !== 'mention') {
                            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for story mention array-key "%s".', $v, $k));
                        }
                        break;
                }
            }
            self::_throwIfInvalidStoryStickerPlacement(array_diff_key($mention, array_flip($requiredKeys)), 'story mentions');
        }
    }

    /**
     * Verifies if a story location sticker is valid.
     *
     * @param array[] $locationSticker Array with location sticker key-value pairs.
     *
     * @throws \InvalidArgumentException If it's missing keys or has invalid values.
     */
    public static function throwIfInvalidStoryLocationSticker(
        array $locationSticker)
    {
        $requiredKeys = ['location_id', 'is_sticker', 'tap_state', 'tap_state_str_id', 'type'];
        $missingKeys = array_keys(array_diff_key(['location_id' => 1, 'is_sticker' => 1, 'tap_state' => 1, 'tap_state_str_id' => 1, 'type' => 1], $locationSticker));

        if (count($missingKeys)) {
            throw new \InvalidArgumentException(sprintf('Missing keys "%s" for location array.', implode(', ', $missingKeys)));
        }

        foreach ($locationSticker as $k => $v) {
            switch ($k) {
                case 'location_id':
                    if (!is_string($v) && !is_numeric($v)) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for location array-key "%s".', $v, $k));
                    }
                    break;
                case 'is_sticker':
                    if (!is_bool($v)) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for location array-key "%s".', $v, $k));
                    }
                    break;
                case 'tap_state':
                    if ($v !== 0) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for location array-key "%s".', $v, $k));
                    }
                    break;
                case 'tap_state_str_id':
                    if ($v !== 'location_sticker_vibrant') {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for location array-key "%s".', $v, $k));
                    }
                    break;
                case 'type':
                    if ($v !== 'location') {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for location array-key "%s".', $v, $k));
                    }
                    break;
            }
        }
        self::_throwIfInvalidStoryStickerPlacement(array_diff_key($locationSticker, array_flip($requiredKeys)), 'location');
    }

    /**
     * Verifies if a story link sticker is valid.
     *
     * @param array[] $linkSticker Array with link sticker key-value pairs.
     *
     * @throws \InvalidArgumentException If it's missing keys or has invalid values.
     */
    public static function throwIfInvalidStoryLinkSticker(
        array $linkSticker)
    {
        $requiredKeys = ['link_type', 'url', 'selected_index', 'is_sticker', 'tap_state', 'tap_state_str_id', 'type'];
        $missingKeys = array_keys(array_diff_key(['link_type' => 1, 'url' => 1, 'selected_index' => 1, 'is_sticker' => 1, 'tap_state' => 1, 'tap_state_str_id' => 1, 'type' => 1], $linkSticker));

        if (count($missingKeys)) {
            throw new \InvalidArgumentException(sprintf('Missing keys "%s" for link array.', implode(', ', $missingKeys)));
        }

        foreach ($linkSticker as $k => $v) {
            switch ($k) {
                case 'link_type':
                    if ($v !== 'web') {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for link array-key "%s".', $v, $k));
                    }
                    break;
                case 'url':
                    if (!is_string($v)) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for link array-key "%s".', $v, $k));
                    }
                    break;
                case 'is_sticker':
                    if (!is_bool($v)) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for link array-key "%s".', $v, $k));
                    }
                    break;
                case 'tap_state':
                case 'selected_index':
                    if ($v !== 0) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for link array-key "%s".', $v, $k));
                    }
                    break;
                case 'tap_state_str_id':
                    if ($v !== 'link_sticker_default') {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for link array-key "%s".', $v, $k));
                    }
                    break;
                case 'type':
                    if ($v !== 'story_link') {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for location array-key "%s".', $v, $k));
                    }
                    break;
            }
        }
        self::_throwIfInvalidStoryStickerPlacement(array_diff_key($linkSticker, array_flip($requiredKeys)), 'link');
    }

    /**
     * Verifies an array of hashtags.
     *
     * @param array[] $hashtags The array of all story hashtags.
     *
     * @throws \InvalidArgumentException If caption doesn't contain any hashtag,
     *                                   or if any tags are invalid.
     */
    public static function throwIfInvalidStoryHashtagSticker(
         array $hashtags)
    {
        $requiredKeys = ['tag_name', 'is_sticker', 'tap_state', 'tap_state_str_id', 'type'];

        /*
        // Extract all hashtags from the caption using a UTF-8 aware regex.
        if (!preg_match_all('/#([^\s#]+)/u', $captionText, $tagsInCaption)) {
            throw new \InvalidArgumentException('Invalid caption for hashtag.');
        }
        */

        // Verify all provided hashtags.
        foreach ($hashtags as $hashtag) {
            $missingKeys = array_keys(array_diff_key(['tag_name' => 1, 'is_sticker' => 1, 'tap_state' => 1, 'tap_state_str_id' => 1, 'type' => 1], $hashtag));
            if (count($missingKeys)) {
                throw new \InvalidArgumentException(sprintf('Missing keys "%s" for hashtag array.', implode(', ', $missingKeys)));
            }

            foreach ($hashtag as $k => $v) {
                switch ($k) {
                    case 'tag_name':
                        // Ensure that the hashtag format is valid.
                        self::throwIfInvalidHashtag($v);
                        break;
                    case 'is_sticker':
                        if (!is_bool($v)) {
                            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for hashtag array-key "%s".', $v, $k));
                        }
                        break;
                    case 'tap_state':
                        if ($v < 0 || $v > 3) {
                            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for hashtag array-key "%s".', $v, $k));
                        }
                        break;
                    case 'tap_state_str_id':
                        if ($v !== 'hashtag_sticker_subtle' && $v !== 'hashtag_sticker_hero' && $v !== 'hashtag_sticker_rainbow' && $v !== 'hashtag_sticker_gradient') {
                            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for hashtag array-key "%s".', $v, $k));
                        }
                        break;
                    case 'type':
                        if ($v !== 'hashtag') {
                            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for hashtag array-key "%s".', $v, $k));
                        }
                        break;
                }
            }
            self::_throwIfInvalidStoryStickerPlacement(array_diff_key($hashtag, array_flip($requiredKeys)), 'hashtag');
        }
    }

    /**
     * Verifies an attached media.
     *
     * @param array[] $attachedMedia Array containing the attached media data.
     *
     * @throws \InvalidArgumentException If it's missing keys or has invalid values.
     */
    public static function throwIfInvalidAttachedMedia(
        array $attachedMedia)
    {
        $attachedMedia = reset($attachedMedia);
        $requiredKeys = ['media_id', 'is_sticker'];

        // Ensure that all keys exist.
        $missingKeys = array_keys(array_diff_key(['media_id' => 1, 'is_sticker' => 1], $attachedMedia));
        if (count($missingKeys)) {
            throw new \InvalidArgumentException(sprintf('Missing keys "%s" for attached media.', implode(', ', $missingKeys)));
        }

        if (!is_string($attachedMedia['media_id']) && !is_numeric($attachedMedia['media_id'])) {
            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for media_id.', $attachedMedia['media_id']));
        }

        if (!is_bool($attachedMedia['is_sticker']) && $attachedMedia['is_sticker'] !== true) {
            throw new \InvalidArgumentException(sprintf('Invalid value "%s" for attached media.', $attachedMedia['is_sticker']));
        }

        self::_throwIfInvalidStoryStickerPlacement(array_diff_key($attachedMedia, array_flip($requiredKeys)), 'attached media');
    }

    /**
     * Verifies a story sticker's placement parameters.
     *
     * There are many kinds of story stickers, such as hashtags, locations,
     * mentions, etc. To place them on the media, the user must provide certain
     * parameters for things like position and size. This function verifies all
     * of those parameters and ensures that the sticker placement is valid.
     *
     * @param array  $storySticker The array describing the story sticker placement.
     * @param string $type         What type of sticker this is.
     *
     * @throws \InvalidArgumentException If storySticker is missing keys or has invalid values.
     */
    protected static function _throwIfInvalidStoryStickerPlacement(
        array $storySticker,
        $type)
    {
        $requiredKeys = ['x', 'y', 'z', 'width', 'height', 'rotation'];

        // Ensure that all required hashtag array keys exist.
        $missingKeys = array_keys(array_diff_key(['x' => 1, 'y' => 1, 'z' => 1, 'width' => 1, 'height' => 1, 'rotation' => 0], $storySticker));
        if (count($missingKeys)) {
            throw new \InvalidArgumentException(sprintf('Missing keys "%s" for "%s".', implode(', ', $missingKeys), $type));
        }

        // Check the individual array values.
        foreach ($storySticker as $k => $v) {
            if (!in_array($k, $requiredKeys, true)) {
                throw new \InvalidArgumentException(sprintf('Invalid key "%s" for "%s".', $k, $type));
            }
            switch ($k) {
                case 'x':
                case 'y':
                case 'z':
                case 'width':
                case 'height':
                case 'rotation':
                    $v = floatval($v);
                    if (!is_float($v) || $v < 0.0 || $v > 1.0) {
                        throw new \InvalidArgumentException(sprintf('Invalid value "%s" for "%s" key "%s".', $v, $type, $k));
                    }
                    break;
            }
        }
    }

    /**
     * Checks and validates a media item's type.
     *
     * @param string|int $mediaType The type of the media item. One of: "PHOTO", "VIDEO"
     *                              "CAROUSEL", or the raw value of the Item's "getMediaType()" function.
     *
     * @throws \InvalidArgumentException If the type is invalid.
     *
     * @return string The verified final type; either "PHOTO", "VIDEO" or "CAROUSEL".
     */
    public static function checkMediaType(
        $mediaType)
    {
        if (ctype_digit($mediaType) || is_int($mediaType)) {
            if ($mediaType == Item::PHOTO) {
                $mediaType = 'PHOTO';
            } elseif ($mediaType == Item::VIDEO) {
                $mediaType = 'VIDEO';
            } elseif ($mediaType == Item::CAROUSEL) {
                $mediaType = 'CAROUSEL';
            }
        }
        if (!in_array($mediaType, ['PHOTO', 'VIDEO', 'CAROUSEL'], true)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid media type.', $mediaType));
        }

        return $mediaType;
    }

    public static function formatBytes(
        $bytes,
        $precision = 2)
    {
        $units = ['B', 'kB', 'mB', 'gB', 'tB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).''.$units[$pow];
    }

    public static function colouredString(
        $string,
        $colour)
    {
        $colours['black'] = '0;30';
        $colours['dark_gray'] = '1;30';
        $colours['blue'] = '0;34';
        $colours['light_blue'] = '1;34';
        $colours['green'] = '0;32';
        $colours['light_green'] = '1;32';
        $colours['cyan'] = '0;36';
        $colours['light_cyan'] = '1;36';
        $colours['red'] = '0;31';
        $colours['light_red'] = '1;31';
        $colours['purple'] = '0;35';
        $colours['light_purple'] = '1;35';
        $colours['brown'] = '0;33';
        $colours['yellow'] = '1;33';
        $colours['light_gray'] = '0;37';
        $colours['white'] = '1;37';

        $colored_string = '';

        if (isset($colours[$colour])) {
            $colored_string .= "\033[".$colours[$colour].'m';
        }

        $colored_string .= $string."\033[0m";

        return $colored_string;
    }

    public static function getFilterCode(
        $filter)
    {
        $filters = [];
        $filters[0] = 'Normal';
        $filters[615] = 'Lark';
        $filters[614] = 'Reyes';
        $filters[613] = 'Juno';
        $filters[612] = 'Aden';
        $filters[608] = 'Perpetua';
        $filters[603] = 'Ludwig';
        $filters[605] = 'Slumber';
        $filters[616] = 'Crema';
        $filters[24] = 'Amaro';
        $filters[17] = 'Mayfair';
        $filters[23] = 'Rise';
        $filters[26] = 'Hudson';
        $filters[25] = 'Valencia';
        $filters[1] = 'X-Pro II';
        $filters[27] = 'Sierra';
        $filters[28] = 'Willow';
        $filters[2] = 'Lo-Fi';
        $filters[3] = 'Earlybird';
        $filters[22] = 'Brannan';
        $filters[10] = 'Inkwell';
        $filters[21] = 'Hefe';
        $filters[15] = 'Nashville';
        $filters[18] = 'Sutro';
        $filters[19] = 'Toaster';
        $filters[20] = 'Walden';
        $filters[14] = '1977';
        $filters[16] = 'Kelvin';
        $filters[-2] = 'OES';
        $filters[-1] = 'YUV';
        $filters[109] = 'Stinson';
        $filters[106] = 'Vesper';
        $filters[112] = 'Clarendon';
        $filters[118] = 'Maven';
        $filters[114] = 'Gingham';
        $filters[107] = 'Ginza';
        $filters[113] = 'Skyline';
        $filters[105] = 'Dogpatch';
        $filters[115] = 'Brooklyn';
        $filters[111] = 'Moon';
        $filters[117] = 'Helena';
        $filters[116] = 'Ashby';
        $filters[108] = 'Charmes';
        $filters[640] = 'BrightContrast';
        $filters[642] = 'CrazyColor';
        $filters[643] = 'SubtleColor';

        return array_search($filter, $filters);
    }

    /**
     * Creates a folder if missing, or ensures that it is writable.
     *
     * @param string $folder The directory path.
     *
     * @return bool TRUE if folder exists and is writable, otherwise FALSE.
     */
    public static function createFolder(
        $folder)
    {
        // Test write-permissions for the folder and create/fix if necessary.
        if ((is_dir($folder) && is_writable($folder))
            || (!is_dir($folder) && mkdir($folder, 0755, true))
            || chmod($folder, 0755)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Recursively deletes a file/directory tree.
     *
     * @param string $folder         The directory path.
     * @param bool   $keepRootFolder Whether to keep the top-level folder.
     *
     * @return bool TRUE on success, otherwise FALSE.
     */
    public static function deleteTree(
        $folder,
        $keepRootFolder = false)
    {
        // Handle bad arguments.
        if (empty($folder) || !file_exists($folder)) {
            return true; // No such file/folder exists.
        } elseif (is_file($folder) || is_link($folder)) {
            return @unlink($folder); // Delete file/link.
        }

        // Delete all children.
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $action = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            if (!@$action($fileinfo->getRealPath())) {
                return false; // Abort due to the failure.
            }
        }

        // Delete the root folder itself?
        return !$keepRootFolder ? @rmdir($folder) : true;
    }

    /**
     * Atomic filewriter.
     *
     * Safely writes new contents to a file using an atomic two-step process.
     * If the script is killed before the write is complete, only the temporary
     * trash file will be corrupted.
     *
     * The algorithm also ensures that 100% of the bytes were written to disk.
     *
     * @param string $filename     Filename to write the data to.
     * @param string $data         Data to write to file.
     * @param string $atomicSuffix Lets you optionally provide a different
     *                             suffix for the temporary file.
     *
     * @return int|bool Number of bytes written on success, otherwise `FALSE`.
     */
    public static function atomicWrite(
        $filename,
        $data,
        $atomicSuffix = 'atomictmp')
    {
        // Perform an exclusive (locked) overwrite to a temporary file.
        $filenameTmp = sprintf('%s.%s', $filename, $atomicSuffix);
        $writeResult = @file_put_contents($filenameTmp, $data, LOCK_EX);

        // Only proceed if we wrote 100% of the data bytes to disk.
        if ($writeResult !== false && $writeResult === strlen($data)) {
            // Now move the file to its real destination (replaces if exists).
            $moveResult = @rename($filenameTmp, $filename);
            if ($moveResult === true) {
                // Successful write and move. Return number of bytes written.
                return $writeResult;
            }
        }

        // We've failed. Remove the temporary file if it exists.
        if (is_file($filenameTmp)) {
            @unlink($filenameTmp);
        }

        return false; // Failed.
    }

    /**
     * Creates an empty temp file with a unique filename.
     *
     * @param string $outputDir  Folder to place the temp file in.
     * @param string $namePrefix (optional) What prefix to use for the temp file.
     *
     * @throws \RuntimeException If the file cannot be created.
     *
     * @return string
     */
    public static function createTempFile(
        $outputDir,
        $namePrefix = 'TEMP')
    {
        // Automatically generates a name like "INSTATEMP_" or "INSTAVID_" etc.
        $finalPrefix = sprintf('INSTA%s_', $namePrefix);

        // Try to create the file (detects errors).
        $tmpFile = @tempnam($outputDir, $finalPrefix);
        if (!is_string($tmpFile)) {
            throw new \RuntimeException(sprintf(
                'Unable to create temporary output file in "%s" (with prefix "%s").',
                $outputDir, $finalPrefix
            ));
        }

        return $tmpFile;
    }

    /**
     * Closes a file pointer if it's open.
     *
     * Always use this function instead of fclose()!
     *
     * Unlike the normal fclose(), this function is safe to call multiple times
     * since it only attempts to close the pointer if it's actually still open.
     * The normal fclose() would give an annoying warning in that scenario.
     *
     * @param resource $handle A file pointer opened by fopen() or fsockopen().
     *
     * @return bool TRUE on success or FALSE on failure.
     */
    public static function safe_fclose(
        $handle)
    {
        if (is_resource($handle)) {
            return fclose($handle);
        }

        return true;
    }

    /**
     * Checks if a URL has valid "web" syntax.
     *
     * This function is Unicode-aware.
     *
     * Be aware that it only performs URL syntax validation! It doesn't check
     * if the domain/URL is fully valid and actually reachable!
     *
     * It verifies that the URL begins with either the "http://" or "https://"
     * protocol, and that it must contain a host with at least one period in it,
     * and at least two characters after the period (in other words, a TLD). The
     * rest of the string can be any sequence of non-whitespace characters.
     *
     * For example, "http://localhost" will not be seen as a valid web URL, and
     * "http://www.google.com foobar" is not a valid web URL since there's a
     * space in it. But "https://bing.com" and "https://a.com/foo" are valid.
     * However, "http://a.abdabdbadbadbsa" is also seen as a valid URL, since
     * the validation is pretty simple and doesn't verify the TLDs (there are
     * too many now to catch them all and new ones appear constantly).
     *
     * @param string $url
     *
     * @return bool TRUE if valid web syntax, otherwise FALSE.
     */
    public static function hasValidWebURLSyntax(
        $url)
    {
        return (bool) preg_match('/^https?:\/\/[^\s.\/]+\.[^\s.\/]{2}\S*$/iu', $url);
    }

    /**
     * Extract all URLs from a text string.
     *
     * This function is Unicode-aware.
     *
     * @param string          $text        The string to scan for URLs.
     * @param string|string[] $excludeText The string to scan for URLs.
     *
     * @return array An array of URLs and their individual components.
     */
    public static function extractURLs(
        $text,
        $excludeText = [])
    {
        if (!is_array($excludeText)) {
            $excludeText = [$excludeText];
        }

        foreach ($excludeText as $key => $val) {
            $excludeText[$key] = '/\b'.$val.'\b/';
        }

        if (!empty($excludeText)) {
            $replace = [];
            foreach ($excludeText as $str) {
                $replace[] = '';
            }
            $text = str_replace($excludeText, $replace, $text);
        }

        $urls = [];
        if (preg_match_all(
            // NOTE: This disgusting regex comes from the Android SDK, slightly
            // modified by Instagram and then encoded by us into PHP format. We
            // are NOT allowed to tweak this regex! It MUST match the official
            // app so that our link-detection acts *exactly* like the real app!
            // NOTE: Here is the "to PHP regex" conversion algorithm we used:
            // https://github.com/mgp25/Instagram-API/issues/1445#issuecomment-318921867
            '/((?:(http|https|Http|Https|rtsp|Rtsp):\/\/(?:(?:[a-zA-Z0-9$\-\_\.\+\!\*\'\(\)\,\;\?\&\=]|(?:\%[a-fA-F0-9]{2})){1,64}(?:\:(?:[a-zA-Z0-9$\-\_\.\+\!\*\'\(\)\,\;\?\&\=]|(?:\%[a-fA-F0-9]{2})){1,25})?\@)?)?((?:(?:[a-zA-Z0-9\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\_][a-zA-Z0-9\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\_\-]{0,64}\.)+(?:(?:aero|arpa|asia|a[cdefgilmnoqrstuwxz])|(?:biz|b[abdefghijmnorstvwyz])|(?:cat|com|coop|c[acdfghiklmnoruvxyz])|d[ejkmoz]|(?:edu|e[cegrstu])|f[ijkmor]|(?:gov|g[abdefghilmnpqrstuwy])|h[kmnrtu]|(?:info|int|i[delmnoqrst])|(?:jobs|j[emop])|(?:social)|k[eghimnprwyz]|l[abcikrstuvy]|(?:mil|mobi|museum|m[acdeghklmnopqrstuvwxyz])|(?:name|net|n[acefgilopruz])|(?:org|om)|(?:run)|(?:pro|p[aefghklmnrstwy])|qa|r[eosuw]|s[abcdeghijklmnortuvyz]|(?:tel|travel|t[cdfghjklmnoprtvwz])|(?:site)|u[agksyz]|v[aceginu]|w[fs]|(?:observer|off|one|ong|onl|online|open|ooo])|(?:\x{03B4}\x{03BF}\x{03BA}\x{03B9}\x{03BC}\x{03AE}|\x{0438}\x{0441}\x{043F}\x{044B}\x{0442}\x{0430}\x{043D}\x{0438}\x{0435}|\x{0440}\x{0444}|\x{0441}\x{0440}\x{0431}|\x{05D8}\x{05E2}\x{05E1}\x{05D8}|\x{0622}\x{0632}\x{0645}\x{0627}\x{06CC}\x{0634}\x{06CC}|\x{0625}\x{062E}\x{062A}\x{0628}\x{0627}\x{0631}|\x{0627}\x{0644}\x{0627}\x{0631}\x{062F}\x{0646}|\x{0627}\x{0644}\x{062C}\x{0632}\x{0627}\x{0626}\x{0631}|\x{0627}\x{0644}\x{0633}\x{0639}\x{0648}\x{062F}\x{064A}\x{0629}|\x{0627}\x{0644}\x{0645}\x{063A}\x{0631}\x{0628}|\x{0627}\x{0645}\x{0627}\x{0631}\x{0627}\x{062A}|\x{0628}\x{06BE}\x{0627}\x{0631}\x{062A}|\x{062A}\x{0648}\x{0646}\x{0633}|\x{0633}\x{0648}\x{0631}\x{064A}\x{0629}|\x{0641}\x{0644}\x{0633}\x{0637}\x{064A}\x{0646}|\x{0642}\x{0637}\x{0631}|\x{0645}\x{0635}\x{0631}|\x{092A}\x{0930}\x{0940}\x{0915}\x{094D}\x{0937}\x{093E}|\x{092D}\x{093E}\x{0930}\x{0924}|\x{09AD}\x{09BE}\x{09B0}\x{09A4}|\x{0A2D}\x{0A3E}\x{0A30}\x{0A24}|\x{0AAD}\x{0ABE}\x{0AB0}\x{0AA4}|\x{0B87}\x{0BA8}\x{0BCD}\x{0BA4}\x{0BBF}\x{0BAF}\x{0BBE}|\x{0B87}\x{0BB2}\x{0B99}\x{0BCD}\x{0B95}\x{0BC8}|\x{0B9A}\x{0BBF}\x{0B99}\x{0BCD}\x{0B95}\x{0BAA}\x{0BCD}\x{0BAA}\x{0BC2}\x{0BB0}\x{0BCD}|\x{0BAA}\x{0BB0}\x{0BBF}\x{0B9F}\x{0BCD}\x{0B9A}\x{0BC8}|\x{0C2D}\x{0C3E}\x{0C30}\x{0C24}\x{0C4D}|\x{0DBD}\x{0D82}\x{0D9A}\x{0DCF}|\x{0E44}\x{0E17}\x{0E22}|\x{30C6}\x{30B9}\x{30C8}|\x{4E2D}\x{56FD}|\x{4E2D}\x{570B}|\x{53F0}\x{6E7E}|\x{53F0}\x{7063}|\x{65B0}\x{52A0}\x{5761}|\x{6D4B}\x{8BD5}|\x{6E2C}\x{8A66}|\x{9999}\x{6E2F}|\x{D14C}\x{C2A4}\x{D2B8}|\x{D55C}\x{AD6D}|xn\-\-0zwm56d|xn\-\-11b5bs3a9aj6g|xn\-\-3e0b707e|xn\-\-45brj9c|xn\-\-80akhbyknj4f|xn\-\-90a3ac|xn\-\-9t4b11yi5a|xn\-\-clchc0ea0b2g2a9gcd|xn\-\-deba0ad|xn\-\-fiqs8s|xn\-\-fiqz9s|xn\-\-fpcrj9c3d|xn\-\-fzc2c9e2c|xn\-\-g6w251d|xn\-\-gecrj9c|xn\-\-h2brj9c|xn\-\-hgbk6aj7f53bba|xn\-\-hlcj6aya9esc7a|xn\-\-j6w193g|xn\-\-jxalpdlp|xn\-\-kgbechtv|xn\-\-kprw13d|xn\-\-kpry57d|xn\-\-lgbbat1ad8j|xn\-\-mgbaam7a8h|xn\-\-mgbayh7gpa|xn\-\-mgbbh1a71e|xn\-\-mgbc0a9azcg|xn\-\-mgberp4a5d4ar|xn\-\-o3cw4h|xn\-\-ogbpf8fl|xn\-\-p1ai|xn\-\-pgbs0dh|xn\-\-s9brj9c|xn\-\-wgbh1c|xn\-\-wgbl6a|xn\-\-xkc2al3hye2a|xn\-\-xkc2dl3a5ee0h|xn\-\-yfro4i67o|xn\-\-ygbi2ammx|xn\-\-zckzah|xxx)|y[et]|z[amw]))|(?:(?:25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[1-9][0-9]|[1-9])\.(?:25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[1-9][0-9]|[1-9]|0)\.(?:25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[1-9][0-9]|[1-9]|0)\.(?:25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[1-9][0-9]|[0-9])))(?:\:\d{1,5})?)(\/(?:(?:[a-zA-Z0-9\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\;\/\?\:\@\&\=\#\~\-\.\+\!\*\'\(\)\,\_])|(?:\%[a-fA-F0-9]{2}))*)?(?:\b|$)/iu',
            $text,
            $matches,
            PREG_SET_ORDER
        ) !== false) {
            foreach ($matches as $match) {
                $urls[] = [
                    'fullUrl'  => $match[0], // "https://foo:bar@www.bing.com/?foo=#test"
                    'baseUrl'  => $match[1], // "https://foo:bar@www.bing.com"
                    'protocol' => $match[2], // "https" (empty if no protocol)
                    'domain'   => $match[3], // "www.bing.com"
                    'path'     => isset($match[4]) ? $match[4] : '', // "/?foo=#test"
                ];
            }
        }

        return $urls;
    }

    /**
     * Get country code from given locale.
     *
     * Country locale in the format: 'ES'.
     *
     * @param string $countryLocale The country locale 2 letter uppercase.
     *
     * @return string               The country code.
     */
    public static function getCountryCode(
        $countryLocale)
    {
        $countries = [];
        $countries[] = ['code' => 'AF', 'name' => 'Afghanistan', 'd_code' => '+93'];
        $countries[] = ['code' => 'AL', 'name' => 'Albania', 'd_code' => '+355'];
        $countries[] = ['code' => 'DZ', 'name' => 'Algeria', 'd_code' => '+213'];
        $countries[] = ['code' => 'AS', 'name' => 'American Samoa', 'd_code' => '+1'];
        $countries[] = ['code' => 'AD', 'name' => 'Andorra', 'd_code' => '+376'];
        $countries[] = ['code' => 'AO', 'name' => 'Angola', 'd_code' => '+244'];
        $countries[] = ['code' => 'AI', 'name' => 'Anguilla', 'd_code' => '+1'];
        $countries[] = ['code' => 'AG', 'name' => 'Antigua', 'd_code' => '+1'];
        $countries[] = ['code' => 'AR', 'name' => 'Argentina', 'd_code' => '+54'];
        $countries[] = ['code' => 'AM', 'name' => 'Armenia', 'd_code' => '+374'];
        $countries[] = ['code' => 'AW', 'name' => 'Aruba', 'd_code' => '+297'];
        $countries[] = ['code' => 'AU', 'name' => 'Australia', 'd_code' => '+61'];
        $countries[] = ['code' => 'AT', 'name' => 'Austria', 'd_code' => '+43'];
        $countries[] = ['code' => 'AZ', 'name' => 'Azerbaijan', 'd_code' => '+994'];
        $countries[] = ['code' => 'BH', 'name' => 'Bahrain', 'd_code' => '+973'];
        $countries[] = ['code' => 'BD', 'name' => 'Bangladesh', 'd_code' => '+880'];
        $countries[] = ['code' => 'BB', 'name' => 'Barbados', 'd_code' => '+1'];
        $countries[] = ['code' => 'BY', 'name' => 'Belarus', 'd_code' => '+375'];
        $countries[] = ['code' => 'BE', 'name' => 'Belgium', 'd_code' => '+32'];
        $countries[] = ['code' => 'BZ', 'name' => 'Belize', 'd_code' => '+501'];
        $countries[] = ['code' => 'BJ', 'name' => 'Benin', 'd_code' => '+229'];
        $countries[] = ['code' => 'BM', 'name' => 'Bermuda', 'd_code' => '+1'];
        $countries[] = ['code' => 'BT', 'name' => 'Bhutan', 'd_code' => '+975'];
        $countries[] = ['code' => 'BO', 'name' => 'Bolivia', 'd_code' => '+591'];
        $countries[] = ['code' => 'BA', 'name' => 'Bosnia and Herzegovina', 'd_code' => '+387'];
        $countries[] = ['code' => 'BW', 'name' => 'Botswana', 'd_code' => '+267'];
        $countries[] = ['code' => 'BR', 'name' => 'Brazil', 'd_code' => '+55'];
        $countries[] = ['code' => 'IO', 'name' => 'British Indian Ocean Territory', 'd_code' => '+246'];
        $countries[] = ['code' => 'VG', 'name' => 'British Virgin Islands', 'd_code' => '+1'];
        $countries[] = ['code' => 'BN', 'name' => 'Brunei', 'd_code' => '+673'];
        $countries[] = ['code' => 'BG', 'name' => 'Bulgaria', 'd_code' => '+359'];
        $countries[] = ['code' => 'BF', 'name' => 'Burkina Faso', 'd_code' => '+226'];
        $countries[] = ['code' => 'MM', 'name' => 'Burma Myanmar', 'd_code' => '+95'];
        $countries[] = ['code' => 'BI', 'name' => 'Burundi', 'd_code' => '+257'];
        $countries[] = ['code' => 'KH', 'name' => 'Cambodia', 'd_code' => '+855'];
        $countries[] = ['code' => 'CM', 'name' => 'Cameroon', 'd_code' => '+237'];
        $countries[] = ['code' => 'CA', 'name' => 'Canada', 'd_code' => '+1'];
        $countries[] = ['code' => 'CV', 'name' => 'Cape Verde', 'd_code' => '+238'];
        $countries[] = ['code' => 'KY', 'name' => 'Cayman Islands', 'd_code' => '+1'];
        $countries[] = ['code' => 'CF', 'name' => 'Central African Republic', 'd_code' => '+236'];
        $countries[] = ['code' => 'TD', 'name' => 'Chad', 'd_code' => '+235'];
        $countries[] = ['code' => 'CL', 'name' => 'Chile', 'd_code' => '+56'];
        $countries[] = ['code' => 'CN', 'name' => 'China', 'd_code' => '+86'];
        $countries[] = ['code' => 'CO', 'name' => 'Colombia', 'd_code' => '+57'];
        $countries[] = ['code' => 'KM', 'name' => 'Comoros', 'd_code' => '+269'];
        $countries[] = ['code' => 'CK', 'name' => 'Cook Islands', 'd_code' => '+682'];
        $countries[] = ['code' => 'CR', 'name' => 'Costa Rica', 'd_code' => '+506'];
        $countries[] = ['code' => 'CI', 'name' => 'Côte d\'Ivoire', 'd_code' => '+225'];
        $countries[] = ['code' => 'HR', 'name' => 'Croatia', 'd_code' => '+385'];
        $countries[] = ['code' => 'CU', 'name' => 'Cuba', 'd_code' => '+53'];
        $countries[] = ['code' => 'CY', 'name' => 'Cyprus', 'd_code' => '+357'];
        $countries[] = ['code' => 'CZ', 'name' => 'Czech Republic', 'd_code' => '+420'];
        $countries[] = ['code' => 'CD', 'name' => 'Democratic Republic of Congo', 'd_code' => '+243'];
        $countries[] = ['code' => 'DK', 'name' => 'Denmark', 'd_code' => '+45'];
        $countries[] = ['code' => 'DJ', 'name' => 'Djibouti', 'd_code' => '+253'];
        $countries[] = ['code' => 'DM', 'name' => 'Dominica', 'd_code' => '+1'];
        $countries[] = ['code' => 'DO', 'name' => 'Dominican Republic', 'd_code' => '+1'];
        $countries[] = ['code' => 'EC', 'name' => 'Ecuador', 'd_code' => '+593'];
        $countries[] = ['code' => 'EG', 'name' => 'Egypt', 'd_code' => '+20'];
        $countries[] = ['code' => 'SV', 'name' => 'El Salvador', 'd_code' => '+503'];
        $countries[] = ['code' => 'GQ', 'name' => 'Equatorial Guinea', 'd_code' => '+240'];
        $countries[] = ['code' => 'ER', 'name' => 'Eritrea', 'd_code' => '+291'];
        $countries[] = ['code' => 'EE', 'name' => 'Estonia', 'd_code' => '+372'];
        $countries[] = ['code' => 'ET', 'name' => 'Ethiopia', 'd_code' => '+251'];
        $countries[] = ['code' => 'FK', 'name' => 'Falkland Islands', 'd_code' => '+500'];
        $countries[] = ['code' => 'FO', 'name' => 'Faroe Islands', 'd_code' => '+298'];
        $countries[] = ['code' => 'FM', 'name' => 'Federated States of Micronesia', 'd_code' => '+691'];
        $countries[] = ['code' => 'FJ', 'name' => 'Fiji', 'd_code' => '+679'];
        $countries[] = ['code' => 'FI', 'name' => 'Finland', 'd_code' => '+358'];
        $countries[] = ['code' => 'FR', 'name' => 'France', 'd_code' => '+33'];
        $countries[] = ['code' => 'GF', 'name' => 'French Guiana', 'd_code' => '+594'];
        $countries[] = ['code' => 'PF', 'name' => 'French Polynesia', 'd_code' => '+689'];
        $countries[] = ['code' => 'GA', 'name' => 'Gabon', 'd_code' => '+241'];
        $countries[] = ['code' => 'GE', 'name' => 'Georgia', 'd_code' => '+995'];
        $countries[] = ['code' => 'DE', 'name' => 'Germany', 'd_code' => '+49'];
        $countries[] = ['code' => 'GH', 'name' => 'Ghana', 'd_code' => '+233'];
        $countries[] = ['code' => 'GI', 'name' => 'Gibraltar', 'd_code' => '+350'];
        $countries[] = ['code' => 'GR', 'name' => 'Greece', 'd_code' => '+30'];
        $countries[] = ['code' => 'GL', 'name' => 'Greenland', 'd_code' => '+299'];
        $countries[] = ['code' => 'GD', 'name' => 'Grenada', 'd_code' => '+1'];
        $countries[] = ['code' => 'GP', 'name' => 'Guadeloupe', 'd_code' => '+590'];
        $countries[] = ['code' => 'GU', 'name' => 'Guam', 'd_code' => '+1'];
        $countries[] = ['code' => 'GT', 'name' => 'Guatemala', 'd_code' => '+502'];
        $countries[] = ['code' => 'GN', 'name' => 'Guinea', 'd_code' => '+224'];
        $countries[] = ['code' => 'GW', 'name' => 'Guinea-Bissau', 'd_code' => '+245'];
        $countries[] = ['code' => 'GY', 'name' => 'Guyana', 'd_code' => '+592'];
        $countries[] = ['code' => 'HT', 'name' => 'Haiti', 'd_code' => '+509'];
        $countries[] = ['code' => 'HN', 'name' => 'Honduras', 'd_code' => '+504'];
        $countries[] = ['code' => 'HK', 'name' => 'Hong Kong', 'd_code' => '+852'];
        $countries[] = ['code' => 'HU', 'name' => 'Hungary', 'd_code' => '+36'];
        $countries[] = ['code' => 'IS', 'name' => 'Iceland', 'd_code' => '+354'];
        $countries[] = ['code' => 'IN', 'name' => 'India', 'd_code' => '+91'];
        $countries[] = ['code' => 'ID', 'name' => 'Indonesia', 'd_code' => '+62'];
        $countries[] = ['code' => 'IR', 'name' => 'Iran', 'd_code' => '+98'];
        $countries[] = ['code' => 'IQ', 'name' => 'Iraq', 'd_code' => '+964'];
        $countries[] = ['code' => 'IE', 'name' => 'Ireland', 'd_code' => '+353'];
        $countries[] = ['code' => 'IL', 'name' => 'Israel', 'd_code' => '+972'];
        $countries[] = ['code' => 'IT', 'name' => 'Italy', 'd_code' => '+39'];
        $countries[] = ['code' => 'JM', 'name' => 'Jamaica', 'd_code' => '+1'];
        $countries[] = ['code' => 'JP', 'name' => 'Japan', 'd_code' => '+81'];
        $countries[] = ['code' => 'JO', 'name' => 'Jordan', 'd_code' => '+962'];
        $countries[] = ['code' => 'KZ', 'name' => 'Kazakhstan', 'd_code' => '+7'];
        $countries[] = ['code' => 'KE', 'name' => 'Kenya', 'd_code' => '+254'];
        $countries[] = ['code' => 'KI', 'name' => 'Kiribati', 'd_code' => '+686'];
        $countries[] = ['code' => 'XK', 'name' => 'Kosovo', 'd_code' => '+381'];
        $countries[] = ['code' => 'KW', 'name' => 'Kuwait', 'd_code' => '+965'];
        $countries[] = ['code' => 'KG', 'name' => 'Kyrgyzstan', 'd_code' => '+996'];
        $countries[] = ['code' => 'LA', 'name' => 'Laos', 'd_code' => '+856'];
        $countries[] = ['code' => 'LV', 'name' => 'Latvia', 'd_code' => '+371'];
        $countries[] = ['code' => 'LB', 'name' => 'Lebanon', 'd_code' => '+961'];
        $countries[] = ['code' => 'LS', 'name' => 'Lesotho', 'd_code' => '+266'];
        $countries[] = ['code' => 'LR', 'name' => 'Liberia', 'd_code' => '+231'];
        $countries[] = ['code' => 'LY', 'name' => 'Libya', 'd_code' => '+218'];
        $countries[] = ['code' => 'LI', 'name' => 'Liechtenstein', 'd_code' => '+423'];
        $countries[] = ['code' => 'LT', 'name' => 'Lithuania', 'd_code' => '+370'];
        $countries[] = ['code' => 'LU', 'name' => 'Luxembourg', 'd_code' => '+352'];
        $countries[] = ['code' => 'MO', 'name' => 'Macau', 'd_code' => '+853'];
        $countries[] = ['code' => 'MK', 'name' => 'Macedonia', 'd_code' => '+389'];
        $countries[] = ['code' => 'MG', 'name' => 'Madagascar', 'd_code' => '+261'];
        $countries[] = ['code' => 'MW', 'name' => 'Malawi', 'd_code' => '+265'];
        $countries[] = ['code' => 'MY', 'name' => 'Malaysia', 'd_code' => '+60'];
        $countries[] = ['code' => 'MV', 'name' => 'Maldives', 'd_code' => '+960'];
        $countries[] = ['code' => 'ML', 'name' => 'Mali', 'd_code' => '+223'];
        $countries[] = ['code' => 'MT', 'name' => 'Malta', 'd_code' => '+356'];
        $countries[] = ['code' => 'MH', 'name' => 'Marshall Islands', 'd_code' => '+692'];
        $countries[] = ['code' => 'MQ', 'name' => 'Martinique', 'd_code' => '+596'];
        $countries[] = ['code' => 'MR', 'name' => 'Mauritania', 'd_code' => '+222'];
        $countries[] = ['code' => 'MU', 'name' => 'Mauritius', 'd_code' => '+230'];
        $countries[] = ['code' => 'YT', 'name' => 'Mayotte', 'd_code' => '+262'];
        $countries[] = ['code' => 'MX', 'name' => 'Mexico', 'd_code' => '+52'];
        $countries[] = ['code' => 'MD', 'name' => 'Moldova', 'd_code' => '+373'];
        $countries[] = ['code' => 'MC', 'name' => 'Monaco', 'd_code' => '+377'];
        $countries[] = ['code' => 'MN', 'name' => 'Mongolia', 'd_code' => '+976'];
        $countries[] = ['code' => 'ME', 'name' => 'Montenegro', 'd_code' => '+382'];
        $countries[] = ['code' => 'MS', 'name' => 'Montserrat', 'd_code' => '+1'];
        $countries[] = ['code' => 'MA', 'name' => 'Morocco', 'd_code' => '+212'];
        $countries[] = ['code' => 'MZ', 'name' => 'Mozambique', 'd_code' => '+258'];
        $countries[] = ['code' => 'NA', 'name' => 'Namibia', 'd_code' => '+264'];
        $countries[] = ['code' => 'NR', 'name' => 'Nauru', 'd_code' => '+674'];
        $countries[] = ['code' => 'NP', 'name' => 'Nepal', 'd_code' => '+977'];
        $countries[] = ['code' => 'NL', 'name' => 'Netherlands', 'd_code' => '+31'];
        $countries[] = ['code' => 'AN', 'name' => 'Netherlands Antilles', 'd_code' => '+599'];
        $countries[] = ['code' => 'NC', 'name' => 'New Caledonia', 'd_code' => '+687'];
        $countries[] = ['code' => 'NZ', 'name' => 'New Zealand', 'd_code' => '+64'];
        $countries[] = ['code' => 'NI', 'name' => 'Nicaragua', 'd_code' => '+505'];
        $countries[] = ['code' => 'NE', 'name' => 'Niger', 'd_code' => '+227'];
        $countries[] = ['code' => 'NG', 'name' => 'Nigeria', 'd_code' => '+234'];
        $countries[] = ['code' => 'NU', 'name' => 'Niue', 'd_code' => '+683'];
        $countries[] = ['code' => 'NF', 'name' => 'Norfolk Island', 'd_code' => '+672'];
        $countries[] = ['code' => 'KP', 'name' => 'North Korea', 'd_code' => '+850'];
        $countries[] = ['code' => 'MP', 'name' => 'Northern Mariana Islands', 'd_code' => '+1'];
        $countries[] = ['code' => 'NO', 'name' => 'Norway', 'd_code' => '+47'];
        $countries[] = ['code' => 'OM', 'name' => 'Oman', 'd_code' => '+968'];
        $countries[] = ['code' => 'PK', 'name' => 'Pakistan', 'd_code' => '+92'];
        $countries[] = ['code' => 'PW', 'name' => 'Palau', 'd_code' => '+680'];
        $countries[] = ['code' => 'PS', 'name' => 'Palestine', 'd_code' => '+970'];
        $countries[] = ['code' => 'PA', 'name' => 'Panama', 'd_code' => '+507'];
        $countries[] = ['code' => 'PG', 'name' => 'Papua New Guinea', 'd_code' => '+675'];
        $countries[] = ['code' => 'PY', 'name' => 'Paraguay', 'd_code' => '+595'];
        $countries[] = ['code' => 'PE', 'name' => 'Peru', 'd_code' => '+51'];
        $countries[] = ['code' => 'PH', 'name' => 'Philippines', 'd_code' => '+63'];
        $countries[] = ['code' => 'PL', 'name' => 'Poland', 'd_code' => '+48'];
        $countries[] = ['code' => 'PT', 'name' => 'Portugal', 'd_code' => '+351'];
        $countries[] = ['code' => 'PR', 'name' => 'Puerto Rico', 'd_code' => '+1'];
        $countries[] = ['code' => 'QA', 'name' => 'Qatar', 'd_code' => '+974'];
        $countries[] = ['code' => 'CG', 'name' => 'Republic of the Congo', 'd_code' => '+242'];
        $countries[] = ['code' => 'RE', 'name' => 'Réunion', 'd_code' => '+262'];
        $countries[] = ['code' => 'RO', 'name' => 'Romania', 'd_code' => '+40'];
        $countries[] = ['code' => 'RU', 'name' => 'Russia', 'd_code' => '+7'];
        $countries[] = ['code' => 'RW', 'name' => 'Rwanda', 'd_code' => '+250'];
        $countries[] = ['code' => 'BL', 'name' => 'Saint Barthélemy', 'd_code' => '+590'];
        $countries[] = ['code' => 'SH', 'name' => 'Saint Helena', 'd_code' => '+290'];
        $countries[] = ['code' => 'KN', 'name' => 'Saint Kitts and Nevis', 'd_code' => '+1'];
        $countries[] = ['code' => 'MF', 'name' => 'Saint Martin', 'd_code' => '+590'];
        $countries[] = ['code' => 'PM', 'name' => 'Saint Pierre and Miquelon', 'd_code' => '+508'];
        $countries[] = ['code' => 'VC', 'name' => 'Saint Vincent and the Grenadines', 'd_code' => '+1'];
        $countries[] = ['code' => 'WS', 'name' => 'Samoa', 'd_code' => '+685'];
        $countries[] = ['code' => 'SM', 'name' => 'San Marino', 'd_code' => '+378'];
        $countries[] = ['code' => 'ST', 'name' => 'São Tomé and Príncipe', 'd_code' => '+239'];
        $countries[] = ['code' => 'SA', 'name' => 'Saudi Arabia', 'd_code' => '+966'];
        $countries[] = ['code' => 'SN', 'name' => 'Senegal', 'd_code' => '+221'];
        $countries[] = ['code' => 'RS', 'name' => 'Serbia', 'd_code' => '+381'];
        $countries[] = ['code' => 'SC', 'name' => 'Seychelles', 'd_code' => '+248'];
        $countries[] = ['code' => 'SL', 'name' => 'Sierra Leone', 'd_code' => '+232'];
        $countries[] = ['code' => 'SG', 'name' => 'Singapore', 'd_code' => '+65'];
        $countries[] = ['code' => 'SK', 'name' => 'Slovakia', 'd_code' => '+421'];
        $countries[] = ['code' => 'SI', 'name' => 'Slovenia', 'd_code' => '+386'];
        $countries[] = ['code' => 'SB', 'name' => 'Solomon Islands', 'd_code' => '+677'];
        $countries[] = ['code' => 'SO', 'name' => 'Somalia', 'd_code' => '+252'];
        $countries[] = ['code' => 'ZA', 'name' => 'South Africa', 'd_code' => '+27'];
        $countries[] = ['code' => 'KR', 'name' => 'South Korea', 'd_code' => '+82'];
        $countries[] = ['code' => 'ES', 'name' => 'Spain', 'd_code' => '+34'];
        $countries[] = ['code' => 'LK', 'name' => 'Sri Lanka', 'd_code' => '+94'];
        $countries[] = ['code' => 'LC', 'name' => 'St. Lucia', 'd_code' => '+1'];
        $countries[] = ['code' => 'SD', 'name' => 'Sudan', 'd_code' => '+249'];
        $countries[] = ['code' => 'SR', 'name' => 'Suriname', 'd_code' => '+597'];
        $countries[] = ['code' => 'SZ', 'name' => 'Swaziland', 'd_code' => '+268'];
        $countries[] = ['code' => 'SE', 'name' => 'Sweden', 'd_code' => '+46'];
        $countries[] = ['code' => 'CH', 'name' => 'Switzerland', 'd_code' => '+41'];
        $countries[] = ['code' => 'SY', 'name' => 'Syria', 'd_code' => '+963'];
        $countries[] = ['code' => 'TW', 'name' => 'Taiwan', 'd_code' => '+886'];
        $countries[] = ['code' => 'TJ', 'name' => 'Tajikistan', 'd_code' => '+992'];
        $countries[] = ['code' => 'TZ', 'name' => 'Tanzania', 'd_code' => '+255'];
        $countries[] = ['code' => 'TH', 'name' => 'Thailand', 'd_code' => '+66'];
        $countries[] = ['code' => 'BS', 'name' => 'The Bahamas', 'd_code' => '+1'];
        $countries[] = ['code' => 'GM', 'name' => 'The Gambia', 'd_code' => '+220'];
        $countries[] = ['code' => 'TL', 'name' => 'Timor-Leste', 'd_code' => '+670'];
        $countries[] = ['code' => 'TG', 'name' => 'Togo', 'd_code' => '+228'];
        $countries[] = ['code' => 'TK', 'name' => 'Tokelau', 'd_code' => '+690'];
        $countries[] = ['code' => 'TO', 'name' => 'Tonga', 'd_code' => '+676'];
        $countries[] = ['code' => 'TT', 'name' => 'Trinidad and Tobago', 'd_code' => '+1'];
        $countries[] = ['code' => 'TN', 'name' => 'Tunisia', 'd_code' => '+216'];
        $countries[] = ['code' => 'TR', 'name' => 'Turkey', 'd_code' => '+90'];
        $countries[] = ['code' => 'TM', 'name' => 'Turkmenistan', 'd_code' => '+993'];
        $countries[] = ['code' => 'TC', 'name' => 'Turks and Caicos Islands', 'd_code' => '+1'];
        $countries[] = ['code' => 'TV', 'name' => 'Tuvalu', 'd_code' => '+688'];
        $countries[] = ['code' => 'UG', 'name' => 'Uganda', 'd_code' => '+256'];
        $countries[] = ['code' => 'UA', 'name' => 'Ukraine', 'd_code' => '+380'];
        $countries[] = ['code' => 'AE', 'name' => 'United Arab Emirates', 'd_code' => '+971'];
        $countries[] = ['code' => 'GB', 'name' => 'United Kingdom', 'd_code' => '+44'];
        $countries[] = ['code' => 'US', 'name' => 'United States', 'd_code' => '+1'];
        $countries[] = ['code' => 'UY', 'name' => 'Uruguay', 'd_code' => '+598'];
        $countries[] = ['code' => 'VI', 'name' => 'US Virgin Islands', 'd_code' => '+1'];
        $countries[] = ['code' => 'UZ', 'name' => 'Uzbekistan', 'd_code' => '+998'];
        $countries[] = ['code' => 'VU', 'name' => 'Vanuatu', 'd_code' => '+678'];
        $countries[] = ['code' => 'VA', 'name' => 'Vatican City', 'd_code' => '+39'];
        $countries[] = ['code' => 'VE', 'name' => 'Venezuela', 'd_code' => '+58'];
        $countries[] = ['code' => 'VN', 'name' => 'Vietnam', 'd_code' => '+84'];
        $countries[] = ['code' => 'WF', 'name' => 'Wallis and Futuna', 'd_code' => '+681'];
        $countries[] = ['code' => 'YE', 'name' => 'Yemen', 'd_code' => '+967'];
        $countries[] = ['code' => 'ZM', 'name' => 'Zambia', 'd_code' => '+260'];
        $countries[] = ['code' => 'ZW', 'name' => 'Zimbabwe', 'd_code' => '+263'];

        foreach ($countries as $country) {
            if ($country['code'] === $countryLocale) {
                return trim($country['d_code'], '+');
            }
        }

        return '1';
    }

    /**
     * Check if correct iDevice (for iOS usage only).
     *
     * @param string $device The Apple device model.
     */
    public static function checkIsValidiDevice(
        $device)
    {
        $iDevices = [
            'iPhone9,1', // iPhone 7 (Global)
            'iPhone9,2', // iPhone 7 Plus (Global)
            'iPhone9,3', // iPhone 7 (GSM)
            'iPhone9,4', // iPhone 7 Plus (GSM)
            'iPhone10,1', // iPhone 8 (Global)
            'iPhone10,2', // iPhone 8 Plus (Global)
            'iPhone10,4', // iPhone 8 (GSM)
            'iPhone10,5', // iPhone 8 Plus (GSM)
            'iPhone10,3', // iPhone X (Global)
            'iPhone11,6', // iPhone XS Max
            'iPhone11,4', // iPhone XS Max (China)
            'iPhone11,8', // iPhone XR
            'iPhone12,1', // iPhone 11
            'iPhone12,3', // iPhone 11 Pro
            'iPhone12,5', // iPhone 11 Pro Max
            'iPhone12,8', // iPhone SE (2020)
            'iPhone13,1', // iPhone 12 mini
            'iPhone13,2', // iPhone 12
            'iPhone13,3', // iPhone Pro
            'iPhone13,4', // iPhone 12 Pro Max
        ];

        if (!in_array($device, $iDevices, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid iPhone model %s.', $device));
        }
    }
}
