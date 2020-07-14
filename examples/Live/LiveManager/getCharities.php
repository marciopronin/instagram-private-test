<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/config.php';
require VENDOR_PATH.'/autoload.php';
require_once __DIR__.'/utils.php';

/////// CONFIG ///////
$debug = false;
$truncatedDebug = false;
//////////////////////

if ($argc > 2) {
    $username = $argv[1];
    $password = $argv[2];
} else {
    echo "================\n";
    echo "Instagram Login\n";
    echo "================\n\n";
    echo '[>] Insert username: ';
    $username = trim(fgets(STDIN));
    echo '[>] Insert password: ';
    $password = trim(fgets(STDIN));
    if ($username === null || $password === null) {
        echo 'Usage: php auth.php <username> <password>';
        echo 'Credentials for the accounts are missing.';
        exit();
    }
}
$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}
echo "\e[96m[✓] Login: Successfully logged in as ".$username."!\e[0m";

try {
    echo "\n";
    echo '[>] Query: ';
    $query = trim(fgets(STDIN));
    $charities = $ig->live->searchCharity($query)->getSearchedCharities();
    foreach ($charities as $charity) {
        echo "----------------------------\n";
        echo sprintf("Name: %s\n", $charity->getFullName());
        echo sprintf("ID: %s\n", $charity->getPk());
    }
    echo "----------------------------\n\n";
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
