<?php

namespace InstagramAPI\Realtime\Command;

use InstagramAPI\Constants;
use InstagramAPI\Realtime\CommandInterface;
use InstagramAPI\Realtime\Mqtt;

class IrisSubscribe implements CommandInterface
{
    public const INVALID_SEQUENCE_ID = -1;

    /** @var int */
    private $_sequenceId;
    /** @var int */
    private $_snapshotMs;

    /**
     * Constructor.
     *
     * @param int   $sequenceId
     * @param mixed $snapshotMs
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $sequenceId,
        $snapshotMs
    ) {
        if ($sequenceId === self::INVALID_SEQUENCE_ID) {
            throw new \InvalidArgumentException('Invalid Iris sequence identifier.');
        }
        $this->_sequenceId = intval($sequenceId);
        $this->_snapshotMs = intval($snapshotMs);
    }

    /** {@inheritdoc} */
    public function getTopic()
    {
        return Mqtt\Topics::IRIS_SUB;
    }

    /** {@inheritdoc} */
    public function getQosLevel()
    {
        return Mqtt\QosLevel::ACKNOWLEDGED_DELIVERY;
    }

    /** {@inheritdoc} */
    public function jsonSerialize()
    {
        return [
            'seq_id'                => $this->_sequenceId,
            'snapshot_at_ms'        => $this->_snapshotMs,
            'snapshot_app_version'  => Constants::IG_VERSION,
        ];
    }
}
