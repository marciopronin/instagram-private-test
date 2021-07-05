<?php

namespace InstagramAPI\Media\Constraints;

/**
 * Instagram's Reels media constraints.
 */
class ReelsConstraints extends StoryConstraints
{
    /**
     * Lowest allowed aspect ratio.
     *
     * // TODO Use the experiment.
     *
     * @see https://help.instagram.com/1038071743007909
     *
     * @var float
     */
    const MIN_RATIO = 0.562;

    /**
     * Highest allowed aspect ratio.
     *
     * // TODO Use the experiment.
     *
     * @see https://help.instagram.com/1038071743007909
     *
     * @var float
     */
    const MAX_RATIO = 1.91;

    /**
     * Minimum allowed video duration.
     *
     * // TODO Use the experiment.
     *
     * @see https://help.instagram.com/1038071743007909
     *
     * @var float
     */
    const MIN_DURATION = 15.0;

    /**
     * Maximum allowed video duration.
     *
     * // TODO Use the experiment.
     *
     * @see https://help.instagram.com/1038071743007909
     *
     * @var float
     */
    const MAX_DURATION = 30.0;

    /** {@inheritdoc} */
    public function getMinAspectRatio()
    {
        return self::MIN_RATIO;
    }

    /** {@inheritdoc} */
    public function getMaxAspectRatio()
    {
        return self::MAX_RATIO;
    }

    /** {@inheritdoc} */
    public function getMinDuration()
    {
        return self::MIN_DURATION;
    }

    /** {@inheritdoc} */
    public function getMaxDuration()
    {
        return self::MAX_DURATION;
    }
}
