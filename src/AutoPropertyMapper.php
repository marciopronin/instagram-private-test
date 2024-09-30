<?php

namespace InstagramAPI;

use LazyJsonMapper\LazyJsonMapper;

/**
 * Automatically maps JSON data onto PHP objects with virtual functions.
 *
 * Configures important core settings for the property mapping process.
 */
class AutoPropertyMapper extends LazyJsonMapper
{
    /** @var bool */
    public const ALLOW_VIRTUAL_PROPERTIES = false;

    /** @var bool */
    public const ALLOW_VIRTUAL_FUNCTIONS = true;

    /** @var bool */
    public const USE_MAGIC_LOOKUP_CACHE = true;
}
