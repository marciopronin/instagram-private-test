<?php

namespace InstagramAPI\Realtime\Command\Direct;

final class SendLike extends SendItem
{
    public const TYPE = 'like';

    /**
     * Constructor.
     *
     * @param string $threadId
     * @param array  $options
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $threadId,
        array $options = []
    ) {
        parent::__construct($threadId, self::TYPE, $options);
    }
}
