<?php

/** @noinspection PhpUndefinedConstantInspection */
/* @noinspection PhpComposerExtensionStubsInspection */

set_time_limit(0);
date_default_timezone_set('America/New_York');
if (php_sapi_name() !== 'cli') {
    exit('This script may not be run on a website!');
}

if (!defined('PHP_MAJOR_VERSION') || PHP_MAJOR_VERSION < 7) {
    echo 'This script requires PHP version 7 or higher! Please update your php installation before attempting to run this script again!';
    exit();
}

//Argument Processing
$helpData = [];
$helpData = registerArgument($helpData, $argv, 'help', 'Displays this message.', 'h', 'help');
$helpData = registerArgument($helpData, $argv, 'promptLogin', 'Ignores config.php and prompts you for your username and password.', 'p', 'prompt-login');
$helpData = registerArgument($helpData, $argv, 'autoSelect', 'Automatically selects the first vod that the script finds.', 'a', 'auto-select');
$helpData = registerArgument($helpData, $argv, 'thisIsPlaceHolder', 'Sets the broadcast id to grab data from. (Example: --broadcast-id=17854587811139572).', '-broadcast-id');
$helpData = registerArgument($helpData, $argv, 'placeHolder', 'Sets the file to output data to. (Example: --output=info.txt).', '-output');
$helpData = registerArgument($helpData, $argv, 'autoDelete', 'Automatically deletes the selected live stream.', 'd', 'delete');
$helpData = registerArgument($helpData, $argv, 'autoInfo', 'Automatically prints info about the selected live stream.', 'i', 'info');
$helpData = registerArgument($helpData, $argv, 'debugMode', 'Enable Debug Mode', 'Displays all requests being sent to Instagram.', '-debug');
$helpData = registerArgument($helpData, $argv, 'authList', '', 'Authenticate by selecting existing sessions', 'authList');

$preSelectedBroadcast = '0';
$outputFile = '';
$setUser = null;
foreach ($argv as $curArg) {
    if (strpos($curArg, '--broadcast-id=') !== false) {
        $preSelectedBroadcast = (string) str_replace('--broadcast-id=', '', $curArg);
    } elseif (strpos($curArg, '--output=') !== false) {
        $outputFile = (string) str_replace('--output=', '', $curArg);
    }
    if (strpos($curArg, '--set-user=') !== false) {
        $setUser = str_replace('_', ' ', str_replace('--set-user=', '', $curArg));
    }
}

require_once __DIR__.'/utils.php';
require_once __DIR__.'/config.php';

if (help) {
    Utils::log('Command Line Arguments:');
    foreach ($helpData as $option) {
        $dOption = json_decode($option, true);
        Utils::log($dOption['tacks']['mini'].($dOption['tacks']['full'] !== null ? ' ('.$dOption['tacks']['full'].'): ' : ': ').$dOption['description']);
    }
    exit();
}

Utils::existsOrError(VENDOR_PATH.'/autoload.php', 'Instagram API Files');
Utils::existsOrError('config.php', 'Username & Password Storage');

require_once VENDOR_PATH.'/autoload.php';

$username = IG_USERNAME;
$password = IG_PASS;
if (promptLogin) {
    Utils::log('Please enter your credentials...');
    $username = Utils::promptInput('Username:');
    $password = Utils::promptInput('Password:');
} elseif ($setUser !== null) {
    $username = $setUser;
    $password = 'passwd';
} elseif (authList) {
    Utils::log("\n\n\e[93m=========== AUTH LIST ===========\e[0m\n");
    if (STORAGE_PATH !== '') {
        $sessionFolder = STORAGE_PATH;
    } else {
        Utils::log("\e[31m[x] Storage path: Please configure STORAGE_PATH in config.php.\e[0m");
        exit();
    }
    $sessions = array_diff(scandir($sessionFolder), ['..', '.', '.DS_Store']);
    $sessions = array_values($sessions);
    if (count($sessions) !== 0) {
        for ($i = 0; $i < count($sessions); $i++) {
            echo sprintf("[%d] %s\n", $i + 1, $sessions[$i]);
        }
        Utils::log("\n\e[93m=================================\e[0m\n\n");
        do {
            $selection = Utils::promptInput('[>] Select number to auth:');
        } while (($selection < 1) || ($selection > count($sessions)));
        $username = $sessions[$selection - 1];
        $password = 'passwd';
    } else {
        Utils::log("\e[31m[x] Auth list: No authed sessions.\e[0m");
        exit();
    }
}

if ($username == 'USERNAME' || $password == 'PASSWORD') {
    Utils::log('Default Username or Password have not been changed! Exiting...');
    exit();
}

Utils::log('[>] Login: Starting Instagram logon, please wait...');

if (STORAGE_PATH !== '') {
    $storagePath = [
        'storage'       => 'file',
        'basefolder'    => STORAGE_PATH,
    ];
} else {
    $storagePath = [];
}

$debug = debugMode ? debugMode : DEBUG_MODE;

//Run our login flow to handle two-factor and challenges
$ig = Utils::loginFlow($username, $password, $debug, false, $storagePath);
if (!$ig->isMaybeLoggedIn) {
    Utils::log("\e[31m[x] Login: Unsuccessful login.\e[0m");
    Utils::dump(null, $ig->client->getLastRequest());
    exit(1);
}
Utils::log("\e[96m[✓] Login: Successfully logged in as ".$ig->username."!\e[0m");

Utils::log("\e[95m[*] Fetching Previous Livestreams...\e[0m");
$storyFeed = $ig->story->getUserStoryFeed($ig->account_id);

if ($storyFeed->getPostLiveItem() === null || $storyFeed->getPostLiveItem()->getBroadcasts() === null) {
    Utils::log("\e[91m[x]\e[0m You do not have any saved live broadcasts :(. If you recently saved one, and you're getting this message, check back in a few minutes.\e[0m");
    exit();
}

$postLiveIndex = 0;
if (!autoSelect && $preSelectedBroadcast === '0') {
    Utils::log('Please select the livestream you want information about:');
}
$postLiveCache = [];
foreach ($storyFeed->getPostLiveItem()->getBroadcasts() as $broadcast) {
    if (!autoSelect) {
        if ($preSelectedBroadcast === '0') {
            Utils::log("[$postLiveIndex] - Published At: ".date('Y-m-d H:i:s', substr($broadcast->getPublishedTime(), 0, 10)));
        }
        $postLiveCache[$broadcast->getId()] = $postLiveIndex;
        $postLiveIndex++;
    }
}
if (!autoSelect && $preSelectedBroadcast === '0') {
    Utils::log('Type the Livestream ID from the above selection...');
    $postLiveIndex = Utils::promptInput();
}

if ($preSelectedBroadcast !== '0') {
    if (!isset($postLiveCache[$preSelectedBroadcast])) {
        Utils::log('Invalid Livestream ID! Exiting...', $outputFile);
        exit();
    }
    $postLiveIndex = $postLiveCache[$preSelectedBroadcast];
}

@$selectedBroadcast = $storyFeed->getPostLiveItem()->getBroadcasts()[$postLiveIndex];
if ($selectedBroadcast === null) {
    Utils::log('Invalid Livestream ID! Exiting...', $outputFile);
    exit();
}
Utils::log("\nSelected Broadcast ID: ".$selectedBroadcast->getId(), $outputFile);

if (!autoDelete && !autoInfo) {
    Utils::log("\nWhat would you selected stream? Type one of the following commands:\ninfo - Displays info about the broadcast.\ndelete - Removes the broadcast from public view.");
    $cmd = Utils::promptInput();
} elseif (autoDelete) {
    $cmd = 'delete';
} elseif (autoInfo) {
    $cmd = 'info';
}

switch ($cmd) {
    case 'info':
        Utils::log("\nID: ".$selectedBroadcast->getId(), $outputFile);
        Utils::log('Published Date: '.date('Y-m-d H:i:s', substr($selectedBroadcast->getPublishedTime(), 0, 10)), $outputFile);
        Utils::log('Expiry Date: '.date('Y-m-d H:i:s', substr($selectedBroadcast->getExpireAt(), 0, 10)), $outputFile);
        Utils::log('Unique Viewers: '.$selectedBroadcast->getTotalUniqueViewerCount(), $outputFile);
        Utils::log('Cover Frame: '.$selectedBroadcast->getCoverFrameUrl(), $outputFile);
        Utils::log('Playback URL: '.$selectedBroadcast->getRtmpPlaybackUrl(), $outputFile);
        break;
    case 'delete':
        Utils::log("\nRemoving Livestream from your Story...");
        $ig->live->deletePostLive($selectedBroadcast->getId());
        Utils::log('Removed Livestream from your Story!');
        break;
    default:
        Utils::log("\nYou entered an unknown command! Exiting...");
        break;
}

/**
 * Registers a command line argument to a global variable.
 *
 * @param array       $helpData    The array which holds the command data for the help menu.
 * @param array       $argv        The array of arguments passed to the script.
 * @param string      $name        The name to be used in the global variable.
 * @param string      $description The description of the argument to be used in the help menu.
 * @param string      $tack        The mini-tack argument name.
 * @param string|null $fullTack    The full-tack argument name.
 *
 * @return array The array of help data with the new argument.
 */
function registerArgument(array $helpData, array $argv, string $name, string $description, string $tack, string $fullTack = null): array
{
    if ($fullTack !== null) {
        $fullTack = '--'.$fullTack;
    }
    define($name, in_array('-'.$tack, $argv) || in_array($fullTack, $argv));
    array_push($helpData, json_encode([
        'name'        => $name,
        'description' => $description,
        'tacks'       => [
            'mini' => '-'.$tack,
            'full' => $fullTack,
        ],
    ]));

    return $helpData;
}
