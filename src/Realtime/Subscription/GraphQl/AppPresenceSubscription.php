<?php

namespace InstagramAPI\Realtime\Subscription\GraphQl;

use InstagramAPI\Realtime\Subscription\GraphQlSubscription;

class AppPresenceSubscription extends GraphQlSubscription
{
    const ID = 'presence_subscribe';
    const QUERY = '17846944882223835';
    const QUERY2 = '17875950769655493';

    /**
     * Constructor.
     *
     * @param string $subscriptionId
     */
    public function __construct(
        $subscriptionId)
    {
        parent::__construct(self::QUERY, [
            'client_subscription_id' => $subscriptionId,
        ]);
    }

    /** {@inheritdoc} */
    public function getId()
    {
        return self::ID;
    }
}
