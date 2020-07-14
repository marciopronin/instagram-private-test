<?php

$output = [];
chdir(__DIR__.'/../');
exec('git log $(git describe --tags --abbrev=0)..HEAD', $output);
$history = [];

foreach ($output as $line) {
    if (strpos($line, 'commit') === 0) {
        if (!empty($commit)) {
            unset($commit);
        }
        $commit['hash'] = substr($line, strlen('commit'));
    } elseif (strpos($line, 'Author') === 0) {
        $commit['author'] = substr($line, strlen('Author:'));
    } elseif (strpos($line, 'Date') === 0) {
        $commit['date'] = substr($line, strlen('Date:'));
    } else {
        if (!empty($line)) {
            $re = '/(\w+\(\)|\w+_\w+)/m';
            preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);
            if (!empty($matches[0])) {
                $line = str_replace($matches[0][0], '`'.$matches[0][0].'`', $line);
            }
            $commit['message'] = $line;
            array_push($history, $commit);
        }
    }
}

$breaks = [];
$updates = [];
$features = [];
$examples = [];
$documentation = [];

$re = '/\s?(\w+): (.*)/m';

foreach ($history as $commit) {
    preg_match_all($re, $commit['message'], $matches, PREG_SET_ORDER, 0);
    if (strpos($commit['message'], '[UPDATE]') !== false) {
        @$updates[] = sprintf("- %s **%s**: %s\n", exec('git rev-parse --short '.$commit['hash']), $matches[0][1], $matches[0][2]);
    } elseif (strpos($commit['message'], '[BREAK]') !== false) {
        @$breaks[] = sprintf("- %s **%s**: %s\n", exec('git rev-parse --short '.$commit['hash']), $matches[0][1], $matches[0][2]);
    } elseif (strpos($commit['message'], '[FEATURE]') !== false) {
        @$features[] = sprintf("- %s **%s**: %s\n", exec('git rev-parse --short '.$commit['hash']), $matches[0][1], $matches[0][2]);
    } elseif (strpos($commit['message'], '[EXAMPLE]') !== false) {
        @$examples[] = sprintf("- %s **%s**: %s\n", exec('git rev-parse --short '.$commit['hash']), $matches[0][1], $matches[0][2]);
    } elseif (strpos($commit['message'], '[DOCUMENTATION]') !== false) {
        @$documentations[] = sprintf("- %s **%s**: %s\n", exec('git rev-parse --short '.$commit['hash']), $matches[0][1], $matches[0][2]);
    }
}

$currentRelease = explode('.', exec('git describe --tags --abbrev=0'));
if (!empty($breaks)) {
    $currentRelease[0]++;
    $currentRelease[1] = 0;
    $currentRelease[2] = 0;
} elseif (!empty($features)) {
    $currentRelease[1]++;
    $currentRelease[2] = 0;
} else {
    $currentRelease[2]++;
}

echo sprintf('# Stable release %s', implode('.', $currentRelease));
echo sprintf("\n## Date: %s\n\n", date('d/m/Y'));

if (!empty($breaks)) {
    echo "### Backward breaks ⚠️\n\n";
    foreach ($breaks as $break) {
        echo $break;
    }
    echo "\n\n";
}
if (!empty($features)) {
    echo "### New features\n\n";
    foreach ($features as $feature) {
        echo $feature;
    }
    echo "\n\n";
}
if (!empty($updates)) {
    echo "### Updates and fixes\n\n";
    foreach ($updates as $update) {
        echo $update;
    }
    echo "\n\n";
}
if (!empty($documentations)) {
    echo "### Documentation\n\n";
    foreach ($documentations as $documentation) {
        echo $documentation;
    }
    echo "\n\n";
}
if (!empty($examples)) {
    echo "### Examples\n\n";
    foreach ($examples as $example) {
        echo $example;
    }
    echo "\n\n";
}
