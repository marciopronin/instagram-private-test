<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * SliderSticker.
 *
 * @method string getBackgroundColor()
 * @method string getEmoji()
 * @method string getQuestion()
 * @method string getSliderId()
 * @method float getSliderVoteAverage()
 * @method int getSliderVoteCount()
 * @method string getTextColor()
 * @method bool getViewerCanVote()
 * @method bool isBackgroundColor()
 * @method bool isEmoji()
 * @method bool isQuestion()
 * @method bool isSliderId()
 * @method bool isSliderVoteAverage()
 * @method bool isSliderVoteCount()
 * @method bool isTextColor()
 * @method bool isViewerCanVote()
 * @method $this setBackgroundColor(string $value)
 * @method $this setEmoji(string $value)
 * @method $this setQuestion(string $value)
 * @method $this setSliderId(string $value)
 * @method $this setSliderVoteAverage(float $value)
 * @method $this setSliderVoteCount(int $value)
 * @method $this setTextColor(string $value)
 * @method $this setViewerCanVote(bool $value)
 * @method $this unsetBackgroundColor()
 * @method $this unsetEmoji()
 * @method $this unsetQuestion()
 * @method $this unsetSliderId()
 * @method $this unsetSliderVoteAverage()
 * @method $this unsetSliderVoteCount()
 * @method $this unsetTextColor()
 * @method $this unsetViewerCanVote()
 */
class SliderSticker extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'slider_id'                 => 'string',
        'question'                  => 'string',
        'emoji'                     => 'string',
        'text_color'                => 'string',
        'background_color'          => 'string',
        'viewer_can_vote'           => 'bool',
        'slider_vote_average'       => 'float',
        'slider_vote_count'         => 'int',
    ];
}
