<?php

namespace InstagramAPI\Exception;

use InstagramAPI\Response;
use Psr\Http\Message\ResponseInterface as HttpResponseInterface;

/**
 * Parses Instagram's API error messages and throws an appropriate exception.
 *
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class ServerMessageThrower
{
    /**
     * Map from server messages to various exceptions.
     *
     * If the first letter of a pattern is "/", we treat it as a regex.
     *
     * The exceptions should be roughly arranged by how common they are, with
     * the most common ones checked first, at the top.
     *
     * Note that not all exceptions are listed below. Some are thrown via other
     * methods than this automatic message parser.
     *
     * WARNING TO CONTRIBUTORS: Do NOT "contribute" a bunch of endpoint function
     * specific garbage exceptions here, such as "User not found", "Duplicate
     * comment", "No permission to view profile" or other garbage. Those server
     * messages are human-readable, unreliable (they can change at any time) and
     * are also totally non-critical. You should handle them yourself in your
     * end-user applications by simply catching their generic EndpointException
     * and looking at the contents of its getMessage() property, or checking if
     * it hasResponse() and then getResponse() to see the full server response.
     * The exceptions listed below are *critical* exceptions related to the CORE
     * of the API! Nothing else.
     *
     * @var array
     */
    const EXCEPTION_MAP = [
        /*
         * WARNING: We MUST be sure to list these exception messages in an order
         * which guarantees that they will be properly detected without being
         * detected as something else!
         *
         * For example, the "challenge_required" string ALSO exists inside of
         * "checkpoint_challenge_required", so if we check for ChallengeRequired
         * problems above CheckpointRequired, then we would ALWAYS detect
         * checkpoints as "challenge required" since that string exists in both
         * of them.
         *
         * Always list all exceptions in an order that guarantees that they
         * cannot be misdetected as each other! The exceptions with the longest
         * strings, in case of similar strings, MUST be checked/listed EARLIER!
         *
         * So in that example, CheckpointRequired MUST be listed above
         * ChallengeRequired!
         */
        'LoginRequiredException'                  => ['login_required', 'require_login'],
        'LoginAndSignupPageException'             => ['LoginAndSignupPage'],
        'IPLoginBlockException'                   => [
            'RleLoginBlocked', // exception_name
            'ip_block', // error_type
        ],
        'Checkpoint\CheckpointRequiredException'  => [
            'checkpoint_required', // message
            'checkpoint_challenge_required', // error_type
        ],
        'AccountDeletionException'                 => [
            '/You requested to delete(.*?)/',
        ],
        'RetryUploadFlowException'                 => ['retry_entire_upload_flow'],
        'Checkpoint\ChallengeRequiredException'    => ['challenge_required'],
        'Checkpoint\ChallengeFinishedException'    => ['challenge_finished'],
        'Checkpoint\SubmitPhoneException'          => ['submit_phone'],
        'Checkpoint\SubmitEmailException'          => ['submit_email'],
        'Checkpoint\SelectVerifyMethodException'   => ['select_verify_method', 'select_contact_point_recovery', 'review_contact_point_change'],
        'Checkpoint\VerifyCodeException'           => [
            'Please check the code we sent you and try again.', // message
            'verify_code', // step_name
            'verify_email', // step_name
            'verify_sms_code', // step_name
        ],
        'Checkpoint\DeltaLoginReviewException'          => ['delta_login_review', 'delta_acknowledge_approved'], // step name
        'Checkpoint\EscalationInformationalException'   => ['escalation_challenge_informational'], // step name
        'FeedbackRequiredException'                     => ['feedback_required'],
        'Checkpoint\BirthdayRequiredException'          => ['add_birthday'],
        'ConsentRequiredException'                      => ['consent_required'],
        'IncorrectPasswordException'                    => [
            // "The password you entered is incorrect".
            '/password(.*?)incorrect/', // message
            'bad_password', // error_type
        ],
        'InvalidSmsCodeException'      => [
            // "Please check the security code we sent you and try again".
            '/check(.*?)security(.*?)code/', // message
            'sms_code_validation_code_invalid', // error_type
        ],
        'AccountDisabledException'     => [
            // "Your account has been disabled for violating our terms".
            '/account(.*?)disabled(.*?)violating/',
        ],
        'SentryBlockException'                      => ['sentry_block'],
        'Checkpoint\ScrapingWarningException'       => ['scraping_warning'],
        'Checkpoint\ReviewLinkedAccountsException'  => ['review_linked_accounts'],
        'Checkpoint\SelfieCaptchaException'         => ['selfie_captcha'],
        'Checkpoint\UFACBloksException'             => ['ufac_www_bloks'],
        'InvalidUserException'                      => [
            'invalid_user', // error_type
        ],
        'Checkpoint\ChangePasswordException'                 => ['/reset(.*?)password/', 'set_new_password', 'change_password'],
        'Checkpoint\DummyStepException'                      => ['dummy_step'],
        'Checkpoint\RecaptchaChallengeException'             => ['RecaptchaChallengeForm', 'RecaptchaRestrictChallengeForm'],
        'Checkpoint\EscalationChallengeInformationException' => [
            'ESCALATION',
            'EscalationChallengeInformationalForm',
            'EscalationChallengeAppealForm',
        ],
        'Checkpoint\SubmitPhoneNumberFormException'          => ['SubmitPhoneNumberForm'],
        'Checkpoint\VerifySMSCodeFormForSMSCaptchaException' => ['VerifySMSCodeFormForSMSCaptcha'],
        'Checkpoint\SelectVerificationMethodFormException'   => ['SelectVerificationMethodForm'],
        'Checkpoint\UFACBlockingFormException'               => ['UFACBlockingForm'],
        'Checkpoint\SelectContactPointRecoveryFormException' => ['SelectContactPointRecoveryForm'],
        'Checkpoint\IeForceSetNewPasswordFormException'      => ['IeForceSetNewPasswordForm'],
        'Checkpoint\AcknowledgeFormException'                => ['AcknowledgeForm'],
        'Checkpoint\VerifyEmailCodeFormException'            => ['VerifyEmailCodeForm'],
        'Checkpoint\VerifySMSCodeFormException'              => ['VerifySMSCodeForm'],
        'Checkpoint\LegacyForceSetNewPasswordFormException'  => ['LegacyForceSetNewPasswordForm'],
        'Checkpoint\ReviewContactPointChangeFormException'   => ['ReviewContactPointChangeForm'],
        'Checkpoint\ReviewLoginFormException'                => ['ReviewLoginForm'],
        'Checkpoint\ScrapingWarningFormException'            => ['ScrapingWarningForm'],
    ];

    /**
     * Parses a server message and throws the appropriate exception.
     *
     * Uses the generic EndpointException if no other exceptions match.
     *
     * @param string|null                $prefixString   What prefix to use for
     *                                                   the message in the
     *                                                   final exception. Should
     *                                                   be something helpful
     *                                                   such as the name of the
     *                                                   class or function which
     *                                                   threw. Can be `NULL`.
     * @param string|null                $serverMessage  The failure string from
     *                                                   Instagram's API (from
     *                                                   `getMessage()`). Might
     *                                                   be empty in some cases.
     * @param Response|null              $serverResponse The complete server
     *                                                   response object, if one
     *                                                   is available
     *                                                   (optional).
     * @param HttpResponseInterface|null $httpResponse   The HTTP response
     *                                                   object (if available).
     *
     * @throws InstagramException The appropriate exception.
     */
    public static function autoThrow(
        $prefixString,
        $serverMessage,
        Response $serverResponse = null,
        HttpResponseInterface $httpResponse = null)
    {
        // We will analyze both the `message` AND `error_type` (if available).
        $messages = [$serverMessage];
        $serverErrorType = null;
        if ($serverResponse instanceof Response) {
            // We are reading a property that isn't defined in the class
            // property map, so we must use "has" first, to ensure it exists.
            if ($serverResponse->hasErrorType()
                && is_string($serverResponse->getErrorType())) {
                $serverErrorType = $serverResponse->getErrorType();
                $messages[] = $serverErrorType;
            }
            if ($serverResponse->hasExceptionName()
                && is_string($serverResponse->getExceptionName())) {
                $serverErrorType = $serverResponse->getExceptionName();
                $messages[] = $serverErrorType;
            }
            if ($serverResponse->hasStepName()
                && is_string($serverResponse->getStepName())) {
                $serverErrorType = $serverResponse->getStepName();
                if ($serverErrorType === '') {
                    $serverErrorType = $serverResponse->getChallengeTypeEnumStr();
                }
                $messages[] = $serverErrorType;
            }
            if ($serverResponse->hasEntryData()) {
                $resp = $serverResponse->asArray();
                $entryData = isset($resp['entry_data']) ? $resp['entry_data'] : [];
                if (isset($entryData['Challenge']) && !empty($entryData['Challenge'])) {
                    $serverErrorType = $serverResponse->getChallengeType();
                    $messages[] = $serverErrorType;
                }
                if (isset($entryData['HttpErrorPage'])) {
                    // pass
                }
                if (isset($entryData['LoginAndSignupPage'])) {
                    $serverErrorType = 'LoginAndSignupPage';
                    $messages[] = $serverErrorType;
                }
            }
            if ($serverResponse->hasChallenge()
                && $serverResponse->getMessage() === ''
                && $serverResponse->hasErrorType()
                && $serverResponse->getErrorType() !== 'two_factor_required'
                && $serverResponse->getErrorType() !== null) {
                $serverErrorType = $serverResponse->getChallengeType();
                $messages[] = $serverErrorType;
            }
            if ($serverResponse->hasChallengeType()) {
                $resp = $serverResponse->asArray();
                if (isset($resp['challengeType'])) {
                    $serverErrorType = $serverResponse->getChallengeType();
                    $messages[] = $serverErrorType;
                }
            }
            if ($serverResponse->hasRetryEntireUploadFlow()) {
                if ($serverResponse->getRetryEntireUploadFlow() === true) {
                    $messages[] = 'retry_entire_upload_flow';
                }
            }
            if ($serverResponse->hasRequireLogin()) {
                if ($serverResponse->getRequireLogin() === true) {
                    $messages[] = 'require_login';
                }
            }
        }

        $messages = array_filter($messages);
        $exceptionClass = null;

        // Check if the server message is in our CRITICAL exception table.
        foreach ($messages as $message) {
            foreach (self::EXCEPTION_MAP as $className => $patterns) {
                foreach ($patterns as $pattern) {
                    if ($pattern[0] == '/') {
                        // Regex check.
                        if (preg_match($pattern, $message)) {
                            $exceptionClass = $className;
                            break 3;
                        }
                    } else {
                        // Regular string search.
                        if (strpos($message, $pattern) !== false) {
                            $exceptionClass = $className;
                            break 3;
                        }
                    }
                }
            }
        }

        // Check the HTTP status code if no critical exception has been found.
        if ($exceptionClass === null) {
            // NOTE FOR CONTRIBUTORS: All HTTP status exceptions below MUST be
            // derived from EndpointException, since all HTTP errors are
            // endpoint-error-related responses and MUST be easily catchable!
            $httpStatusCode = $httpResponse !== null ? $httpResponse->getStatusCode() : null;
            switch ($httpStatusCode) {
                case 400:
                    $exceptionClass = 'BadRequestException';
                    break;
                case 404:
                    $exceptionClass = 'NotFoundException';
                    break;
                default:
                    // No critical exceptions and no HTTP code exceptions have
                    // been found, so use the generic "API function exception"!
                    $exceptionClass = 'EndpointException';
            }
        }
        // We need to specify the full namespace path to the exception class.
        $fullClassPath = '\\'.__NAMESPACE__.'\\'.$exceptionClass;

        // Determine which message to display to the user.
        $displayMessage = is_string($serverMessage) && strlen($serverMessage)
                        ? $serverMessage : $serverErrorType;
        if (!is_string($displayMessage) || !strlen($displayMessage)) {
            $displayMessage = 'Request failed.';
        }

        // Some Instagram messages already have punctuation, and others need it.
        $displayMessage = self::prettifyMessage($displayMessage);

        // Create an instance of the final exception class, with the pretty msg.
        $e = new $fullClassPath(
            $prefixString !== null
            ? sprintf('%s: %s', $prefixString, $displayMessage)
            : $displayMessage
        );

        // Attach the server response to the exception, IF a response exists.
        // NOTE: Only possible on exceptions derived from InstagramException.
        if ($serverResponse instanceof Response
            && $e instanceof \InstagramAPI\Exception\InstagramException) {
            $e->setResponse($serverResponse);
        }

        throw $e;
    }

    /**
     * Nicely reformats externally generated exception messages.
     *
     * This is used for guaranteeing consistent message formatting with full
     * English sentences, ready for display to the user.
     *
     * @param string $message The original message.
     *
     * @return string The cleaned-up message.
     */
    public static function prettifyMessage(
        $message)
    {
        // Some messages already have punctuation, and others need it. Prettify
        // the message by ensuring that it ALWAYS ends in punctuation, for
        // consistency with all of our internal error messages.
        $lastChar = substr($message, -1);
        if ($lastChar !== '' && $lastChar !== '.' && $lastChar !== '!' && $lastChar !== '?') {
            $message .= '.';
        }

        // Guarantee that the first letter is uppercase.
        $message = ucfirst($message);

        // Replace all underscores (ie. "Login_required.") with spaces.
        $message = str_replace('_', ' ', $message);

        return $message;
    }
}
