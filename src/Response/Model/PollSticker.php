<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * PollSticker.
 *
 * @method bool getFinished()
 * @method string getId()
 * @method bool getIsSharedResult()
 * @method string getPollId()
 * @method mixed getPromotionTallies()
 * @method string getQuestion()
 * @method Tallies[] getTallies()
 * @method bool getViewerCanVote()
 * @method int getViewerVote()
 * @method bool isFinished()
 * @method bool isId()
 * @method bool isIsSharedResult()
 * @method bool isPollId()
 * @method bool isPromotionTallies()
 * @method bool isQuestion()
 * @method bool isTallies()
 * @method bool isViewerCanVote()
 * @method bool isViewerVote()
 * @method $this setFinished(bool $value)
 * @method $this setId(string $value)
 * @method $this setIsSharedResult(bool $value)
 * @method $this setPollId(string $value)
 * @method $this setPromotionTallies(mixed $value)
 * @method $this setQuestion(string $value)
 * @method $this setTallies(Tallies[] $value)
 * @method $this setViewerCanVote(bool $value)
 * @method $this setViewerVote(int $value)
 * @method $this unsetFinished()
 * @method $this unsetId()
 * @method $this unsetIsSharedResult()
 * @method $this unsetPollId()
 * @method $this unsetPromotionTallies()
 * @method $this unsetQuestion()
 * @method $this unsetTallies()
 * @method $this unsetViewerCanVote()
 * @method $this unsetViewerVote()
 */
class PollSticker extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'                => 'string',
        'poll_id'           => 'string',
        'question'          => 'string',
        'tallies'           => 'Tallies[]',
        'promotion_tallies' => '',
        'viewer_can_vote'   => 'bool',
        'is_shared_result'  => 'bool',
        'finished'          => 'bool',
        'viewer_vote'       => 'int',
    ];
}
