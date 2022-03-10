<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * MediaGrid.
 *
 * @method bool getAutoLoadMoreEnabled()
 * @method string getGridPostClickExperience()
 * @method bool getHasMore()
 * @method string getNextMaxId()
 * @method string getRankToken()
 * @method mixed getRefinements()
 * @method Section[] getSections()
 * @method string getTitle()
 * @method mixed getTopicStatus()
 * @method bool isAutoLoadMoreEnabled()
 * @method bool isGridPostClickExperience()
 * @method bool isHasMore()
 * @method bool isNextMaxId()
 * @method bool isRankToken()
 * @method bool isRefinements()
 * @method bool isSections()
 * @method bool isTitle()
 * @method bool isTopicStatus()
 * @method $this setAutoLoadMoreEnabled(bool $value)
 * @method $this setGridPostClickExperience(string $value)
 * @method $this setHasMore(bool $value)
 * @method $this setNextMaxId(string $value)
 * @method $this setRankToken(string $value)
 * @method $this setRefinements(mixed $value)
 * @method $this setSections(Section[] $value)
 * @method $this setTitle(string $value)
 * @method $this setTopicStatus(mixed $value)
 * @method $this unsetAutoLoadMoreEnabled()
 * @method $this unsetGridPostClickExperience()
 * @method $this unsetHasMore()
 * @method $this unsetNextMaxId()
 * @method $this unsetRankToken()
 * @method $this unsetRefinements()
 * @method $this unsetSections()
 * @method $this unsetTitle()
 * @method $this unsetTopicStatus()
 */
class MediaGrid extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'title'                        => 'string',
        'refinements'                  => '',
        'sections'                     => 'Section[]',
        'rank_token'                   => 'string',
        'next_max_id'                  => 'string',
        'has_more'                     => 'bool',
        'auto_load_more_enabled'       => 'bool',
        'grid_post_click_experience'   => 'string',
        'topic_status'                 => '',
    ];
}
