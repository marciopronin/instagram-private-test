<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * TwoByTwoItem.
 *
 * @method Channel getChannel()
 * @method IGTV getIgtv()
 * @method Item getMedia()
 * @method Item getMediaOrAd()
 * @method Shopping getShopping()
 * @method bool isChannel()
 * @method bool isIgtv()
 * @method bool isMedia()
 * @method bool isMediaOrAd()
 * @method bool isShopping()
 * @method $this setChannel(Channel $value)
 * @method $this setIgtv(IGTV $value)
 * @method $this setMedia(Item $value)
 * @method $this setMediaOrAd(Item $value)
 * @method $this setShopping(Shopping $value)
 * @method $this unsetChannel()
 * @method $this unsetIgtv()
 * @method $this unsetMedia()
 * @method $this unsetMediaOrAd()
 * @method $this unsetShopping()
 */
class TwoByTwoItem extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'shopping'        => 'Shopping',
        'igtv'            => 'IGTV',
        'channel'         => 'Channel',
        'media'           => 'Item',
        'media_or_ad'     => 'Item',
    ];
}
