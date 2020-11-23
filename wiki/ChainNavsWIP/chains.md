# Chains

## Version v167

```php
        switch ($module) {
            case 'feed_timeline':
            case 'explore_popular':
            case 'newsfeed_you':
                $class = '1Ur';
                break;
            case 'search':
            case 'blended_search':
            case 'search_users':
            case 'search_tags':
            case 'search_places':
            case 'search_result':
                $class = 'AXP';
                break;
            case 'feed_hashtag':
                $class = '8n6';
                break;
            case 'feed_location':
                $class = '8pu';
                break;
            case 'feed_contextual_chain':
                $class = '8W5';
                break;
            case 'feed_contextual_place':
            case 'feed_contextual_location':
            case 'feed_contextual_hashtag':
            case 'feed_contextual_profile':
            case 'feed_contextual_self_profile':
                $class = '8Ev';
                break;
            case 'profile':
            case 'self_profile':
                $class = 'UserDetailFragment';
                break;
            case 'following_sheet':
                $class = 'ProfileFollowRelationshipFragment';
                break;
            case 'unified_follow_lists':
            case 'self_unified_follow_lists':
                $class = 'UnifiedFollowFragment';
                break;
            case 'likers':
                $class = '7oE';
                break;
            default:
                $class = false;
        }

        return $class;
    }
```