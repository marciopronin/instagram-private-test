<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Clip.
 *
 * @method string getClientContext()
 * @method Item getClip()
 * @method string getDesign()
 * @method string getId()
 * @method bool getIsSentByViewer()
 * @method bool getIsShhMode()
 * @method Item[] getItems()
 * @method string getMaxId()
 * @method bool getMoreAvailable()
 * @method bool getShowForwardAttribution()
 * @method string getType()
 * @method bool isClientContext()
 * @method bool isClip()
 * @method bool isDesign()
 * @method bool isId()
 * @method bool isIsSentByViewer()
 * @method bool isIsShhMode()
 * @method bool isItems()
 * @method bool isMaxId()
 * @method bool isMoreAvailable()
 * @method bool isShowForwardAttribution()
 * @method bool isType()
 * @method $this setClientContext(string $value)
 * @method $this setClip(Item $value)
 * @method $this setDesign(string $value)
 * @method $this setId(string $value)
 * @method $this setIsSentByViewer(bool $value)
 * @method $this setIsShhMode(bool $value)
 * @method $this setItems(Item[] $value)
 * @method $this setMaxId(string $value)
 * @method $this setMoreAvailable(bool $value)
 * @method $this setShowForwardAttribution(bool $value)
 * @method $this setType(string $value)
 * @method $this unsetClientContext()
 * @method $this unsetClip()
 * @method $this unsetDesign()
 * @method $this unsetId()
 * @method $this unsetIsSentByViewer()
 * @method $this unsetIsShhMode()
 * @method $this unsetItems()
 * @method $this unsetMaxId()
 * @method $this unsetMoreAvailable()
 * @method $this unsetShowForwardAttribution()
 * @method $this unsetType()
 */
class Clip extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'id'                        => 'string',
        'items'                     => 'Item[]',
        'clip'                      => 'Item',
        'max_id'                    => 'string',
        'more_available'            => 'bool',
        'type'                      => 'string',
        'design'                    => 'string',
        'client_context'            => 'string',
        'show_forward_attribution'  => 'bool',
        'is_shh_mode'               => 'bool',
        'is_sent_by_viewer'         => 'bool',
    ];
}
