<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * ReelShareToFbConfigResponse.
 *
 * @method bool getDefaultShareToFbEnabled()
 * @method mixed getMessage()
 * @method bool getOaReuseOnFbEnabled()
 * @method bool getShowShareToFbPromptInCreationFlow()
 * @method bool getShowShareToFbPromptInMediaViewer()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isDefaultShareToFbEnabled()
 * @method bool isMessage()
 * @method bool isOaReuseOnFbEnabled()
 * @method bool isShowShareToFbPromptInCreationFlow()
 * @method bool isShowShareToFbPromptInMediaViewer()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setDefaultShareToFbEnabled(bool $value)
 * @method $this setMessage(mixed $value)
 * @method $this setOaReuseOnFbEnabled(bool $value)
 * @method $this setShowShareToFbPromptInCreationFlow(bool $value)
 * @method $this setShowShareToFbPromptInMediaViewer(bool $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetDefaultShareToFbEnabled()
 * @method $this unsetMessage()
 * @method $this unsetOaReuseOnFbEnabled()
 * @method $this unsetShowShareToFbPromptInCreationFlow()
 * @method $this unsetShowShareToFbPromptInMediaViewer()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class ReelShareToFbConfigResponse extends Response
{
    public const JSON_PROPERTY_MAP = [
        'default_share_to_fb_enabled'               => 'bool',
        'show_share_to_fb_prompt_in_creation_flow'  => 'bool',
        'show_share_to_fb_prompt_in_media_viewer'   => 'bool',
        'oa_reuse_on_fb_enabled'                    => 'bool',
    ];
}
