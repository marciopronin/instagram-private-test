<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../../vendor/autoload.php';

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
    $ig->event->sendNavigationTabClicked('main_home', 'main_profile', 'feed_timeline');
    $ig->event->sendNavigation('main_profile', 'feed_timeline', 'self_profile');

    $traySession = \InstagramAPI\Signatures::generateUUID();

    $ig->highlight->getSelfUserFeed();
    $ig->people->getSelfInfo();
    $userFeed = $ig->timeline->getSelfUserFeed();
    $ig->discover->profileSuBadge();
    $ig->story->getArchiveBadgeCount();
    $items = $userFeed->getItems();

    $c = 0;
    foreach ($items as $item) {
        if ($c === 5) {
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

            $ig->event->sendPerfPercentPhotosRendered('self_profile', $item->getId(), $options);
            $c++;
        }
        $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $item, 'self_profile');
    }

    $navstack = 
        [
            [
                'module'        => 'self_profile',
                'click_point'   => 'inferred_source',
            ],
            [
                'module'        => 'feed_timeline',
                'click_point'   => 'main_profile',
            ],
            [
                'module'        => 'login',
                'click_point'   => 'cold start',
            ],
        ];

    $ig->event->sendProfileAction('tap_follow_details', $ig->account_id, $navstack, ['module' => 'self']);
    $ig->event->sendProfileAction('tap_followers', $ig->account_id, $navstack, ['module' => 'self']);

    $ig->event->sendNavigation('button', 'self_profile', 'self_followers', null, null,
    [
        'source_tab'    => 'followers',
        'dest_tab'      => 'followers',
    ]);

    $ig->event->sendProfileAction('tap_followers', $ig->account_id, $navstack, ['module' => 'self']);

    $ig->event->sendNavigation('followers', 'self_unified_follow_lists', 'self_unified_follow_lists', null, null,
        [
            'source_tab'    => 'followers',
            'dest_tab'      => 'followers',
        ]
    );

    $rankToken = \InstagramAPI\Signatures::generateUUID();
    $followers = $ig->people->getSelfFollowers($rankToken)->getUsers();

    $followerList = [];
    foreach ($followers->getUsers() as $follower) {
        $followerList[] = $follower->getPk();
    }

    $ig->people->getFriendships($followerList);
    $ig->discover->markSuSeen();
    $ig->discover->getAyml();
    $ig->people->getFriendships($followerList);

    $cc = 0;
    foreach($followerList as $follower) {
        if ($cc === 5) {
            break;
        }
        $ig->event->sendNavigation('button', 'unified_follow_lists', 'profile');

        $ig->event->sendProfileView($follower);
    
        $ig->people->getFriendship($follower);
        $ig->highlight->getUserFeed($follower);
        $ig->people->getInfoById($follower);
        $ig->story->getUserStoryFeed($follower);
        $userFeed = $ig->timeline->getUserFeed($follower);
        $items = $userFeed->getItems();
    
        $c = 0;
        foreach ($items as $item) {
            if ($c === 5) {
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
    
        $ig->event->sendNavigation('button', 'profile', 'feed_contextual_profile');
        $ig->event->sendOrganicMediaImpression($items[0], 'feed_contextual_profile');
    
        $commentInfos = $ig->media->getCommentInfos($items[0]->getId())->getCommentInfos()->getData();
        $ig->event->sendOrganicNumberOfLikes($items[0], 'feed_contextual_profile');
    
        foreach ($commentInfos as $key => $value) {
            $previewComments = $value->getPreviewComments();
            if ($previewComments !== null) {
                foreach ($previewComments as $comment) {
                    $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
                }
            }
        }
    
        // Since we are going to like the first item of the media, the position in
        // the feed is 0. If you want to like the second item, it would position 1, and so on.
        $ig->media->like($items[0]->getId(), 0);
        $ig->event->sendOrganicLike($items[0], 'feed_contextual_profile', null, null, $ig->session_id);

        $ig->event->sendNavigation('back', 'feed_contextual_profile', 'profile');
        $ig->event->sendNavigation('back', 'profile', 'unified_follow_lists');

        $cc++;
        usleep(mt_rand(1000000, 6000000));
    }

    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
