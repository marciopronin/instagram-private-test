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

//////////////////////
$queryUser = 'selenagomez'; // :)
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    // Explore and search session, will be used for the Graph API events.
    $searchSession = \InstagramAPI\Signatures::generateUUID();

    $topicData =
    [
        'topic_cluster_title'       => 'For You',
        'topic_cluster_id'          => 'explore_all:0',
        'topic_cluster_type'        => 'explore_all',
        'topic_cluster_session_id'  => $searchSession,
        'topic_nav_order'           => 0,
    ];

    // Send navigation from 'feed_timeline' to 'explore_popular'.
    $ig->event->sendNavigation('main_search', 'feed_timeline', 'explore_popular', null, null, $topicData);

    // Send navigation from 'explore_popular' to 'explore_popular'.
    $ig->event->sendNavigation('explore_topic_load', 'explore_popular', 'explore_popular', null, null, $topicData);

    // Get explore feed sections and items.
    $sectionalItems = $ig->discover->getExploreFeed('explore_all:0', $searchSession)->getSectionalItems();

    // TODO: Add explore_home_impression per thumbnail shown in sectional items.

    // Get suggested searches and recommendations from Instagram.
    $ig->event->sendNavigation('button', 'explore_popular', 'search');
    $ig->event->sendNavigation('button', 'search', 'blended_search');
    $ig->discover->getNullStateDynamicSections();

    // Time spent to search.
    $timeToSearch = mt_rand(2000, 3500);
    sleep($timeToSearch / 1000);

    // Search query and parse results.
    $searchResponse = $ig->discover->search($queryUser);
    $searchResults = $searchResponse->getList();

    $rankToken = $searchResponse->getRankToken();
    $resultList = [];
    $resultTypeList = [];
    $position = 0;
    $found = false;

    // We are now classifying each result into a hashtag or user result.
    foreach ($searchResults as $searchResult) {
        if ($searchResult->getHashtag() !== null) {
            $resultList[] = $searchResult->getHashtag()->getId();
            $resultTypeList[] = 'HASHTAG';
        } elseif ($searchResult->getUser() !== null) {
            $resultList[] = $searchResult->getUser()->getPk();
            // We will save the data when the result matches our query.
            // Hashtag ID is required in the next steps for Graph API and
            // like().
            if ($searchResult->getUser()->getUsername() === $queryUser) {
                $userId = $searchResult->getUser()->getPk();
                // This request tells Instagram that we have clicked in this specific user.
                $ig->discover->registerRecentSearchClick('user', $userId);
                // When this flag is set to true, position won't increment
                // anymore. We are using this to track the result position.
                $found = true;
            }
            $resultTypeList[] = 'USER';
        } else {
            $resultList[] = $searchResult->getPlace()->getLocation()->getPk();
            $resultTypeList[] = 'PLACE';
        }
        if ($found !== true) {
            $position++;
        }
    }

    // Send restults from search.
    $ig->event->sendSearchResults($queryUser, $resultList, $resultTypeList, $rankToken, $searchSession, 'blended_search');
    // Send selected result from results.
    $ig->event->sendSearchResultsPage($queryUser, $userId, $resultList, $resultTypeList, $rankToken, $searchSession, $position, 'USER', 'blended_search');

    // When we clicked the user, we are navigating from 'blended_search' to 'profile'.
    $ig->event->sendNavigation('button', 'blended_search', 'profile', null, null,
        [
            'rank_token'            => null,
            'query_text'            => $queryUser,
            'search_session_id'     => $searchSession,
            'selected_type'         => 'user',
            'position'              => 0,
            'username'              => $queryUser,
            'user_id'               => $userId,
        ]
    );
    $ig->event->sendProfileView($userId);

    ///
    /// YOU CAN GET INFO OF THE USER
    /// USING THE FOLLOWING REQUESTS
    ///
    $ig->people->getFriendship($userId);
    $ig->highlight->getUserFeed($userId);
    $ig->people->getInfoById($userId, 'blended_search');
    $ig->story->getUserStoryFeed($userId);

    // Starting at "null" means starting at the first page.
    $maxId = null;
    do {
        $userFeed = $ig->timeline->getUserFeed($userId, $maxId); // Pagination with maxId
        $items = $userFeed->getItems();

        $c = 0;
        foreach ($items as $item) {
            if ($c === 5) {
                break;
            }
            $ig->event->sendThumbnailImpression('instagram_thumbnail_impression', $item, 'profile');
            $c++;
        }

        $ig->event->sendNavigation('button', 'profile', 'feed_contextual_profile'); // Navigating to feed contextual profile (user feed)

        foreach ($items as $item) {
            $ig->event->sendOrganicMediaImpression($item, 'feed_contextual_profile');
            $commentInfos = $ig->media->getCommentInfos($item->getId())->getCommentInfos()->getData(); // comment previews (event)
            $ig->event->sendOrganicNumberOfLikes($item, 'feed_contextual_profile'); // number of likes of media (event)

            foreach ($commentInfos as $key => $value) {
                $previewComments = $value->getPreviewComments();
                if ($previewComments !== null) {
                    foreach ($previewComments as $comment) {
                        $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount()); // sending comment impression (event)
                    }
                }
            }

            $ig->event->sendNavigation('button', 'feed_contextual_profile', 'comments_v2', null, null, ['user_id' => $userId]); // navigating to comments v2 feed
            $comments = $ig->media->getComments($item->getId())->getComments(); // getting comments. You can expand this paginating in order to see all comments
            $c = 0;
            foreach ($comments as $comment) {
                $ig->event->sendCommentImpression($item, $comment->getUserId(), $comment->getPk(), $comment->getCommentLikeCount());
                if ($c === 5) { // You can skip this and iterate all of them
                    break;
                }
                $c++;
            }
        }
        $maxId = $userFeed->getNextMaxId();
    } while ($maxId !== null); // Must use "!==" for comparison instead of "!=".

    // forceSendBatch() should be only used if you are "closing" the app so all the events that
    // are queued will be sent. Batch event will automatically be sent when it reaches 50 events.
    $ig->event->forceSendBatch();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
