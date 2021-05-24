<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $ig->event->sendNavigation('main_inbox', 'feed_timeline', 'newsfeed_you');

    $suggedtedUsers = $ig->people->getRecentActivityInbox()->getSuggestedUsers()->getSuggestionCards();

    $users = [];
    foreach ($suggedtedUsers as $suggestedUser) {
        $users[] = $suggestedUser->getUserCard()->getUser();
    }

    $userId = $users[0]->getPk();

    $ig->event->sendNavigation('button', 'newsfeed_you', 'profile');

    $ig->people->getFriendship($userId);
    $ig->highlight->getUserFeed($userId);
    $ig->people->getInfoById($userId);
    $ig->story->getUserStoryFeed($userId);
    $ig->event->sendProfileView($userId);
    $navstack = [
        [
            'module'        => 'newsfeed_you',
            'click_point'   => 'button',
        ],
        [
            'module'        => 'feed_timeline',
            'click_point'   => 'main_inbox',
        ],
    ];
    $userFeed = $ig->timeline->getUserFeed($userId);
    $items = $userFeed->getItems();

    $c = 0;
    foreach ($items as $item) {
        if ($c === 6) {
            break;
        }

        if ($item->getMediaType() === 1) {
            $imageResponse = $ig->request($item->getImageVersions2()->getCandidates()[0]->getUrl());

            if (isset($imageResponse->getHttpResponse()->getHeaders()['x-encoded-content-length'])) {
                $imageSize = $imageResponse->getHttpResponse()->getHeaders()['x-encoded-content-length'][0];
            } elseif (isset($imageResponse->getHttpResponse()->getHeaders()['Content-Length'])) {
                $imageSize = $imageResponse->getHttpResponse()->getHeaders()['Content-Length'][0];
            }  elseif (isset($imageResponse->getHttpResponse()->getHeaders()['content-length'])) {
                $imageSize = $imageResponse->getHttpResponse()->getHeaders()['content-length'][0];
            } else {
                continue;
            }

            $options = [
                'is_grid_view'                      => true,
                'rendered'                          => true,
                'did_fallback_render'               => false,
                'is_carousel'                       => false,
                'image_size_kb'                     => $imageSize,
                'estimated_bandwidth'               => mt_rand(1000, 4000),
                'estimated_bandwidth_totalBytes_b'  => $ig->client->totalBytes,
                'estimated_bandwidth_totalTime_ms'  => $ig->client->totalTime,
            ];

            $ig->event->sendPerfPercentPhotosRendered('profile', $item->getId(), $options);
            $c++;
        }
        $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $item, 'profile');
    }

    $ig->event->sendFollowButtonTapped($userId, 'profile', $navstack);
    $ig->people->follow($userId);
    $ig->event->sendProfileAction('follow', $userId, $navstack);
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
