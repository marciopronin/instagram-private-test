<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * AssetModel.
 *
 * @method string getAssetCompressionType()
 * @method string getAssetUrl()
 * @method string getCacheKey()
 * @method int getFilesizeBytes()
 * @method string getId()
 * @method string getMd5Hash()
 * @method bool isAssetCompressionType()
 * @method bool isAssetUrl()
 * @method bool isCacheKey()
 * @method bool isFilesizeBytes()
 * @method bool isId()
 * @method bool isMd5Hash()
 * @method $this setAssetCompressionType(string $value)
 * @method $this setAssetUrl(string $value)
 * @method $this setCacheKey(string $value)
 * @method $this setFilesizeBytes(int $value)
 * @method $this setId(string $value)
 * @method $this setMd5Hash(string $value)
 * @method $this unsetAssetCompressionType()
 * @method $this unsetAssetUrl()
 * @method $this unsetCacheKey()
 * @method $this unsetFilesizeBytes()
 * @method $this unsetId()
 * @method $this unsetMd5Hash()
 */
class AssetModel extends AutoPropertyMapper
{
    public const JSON_PROPERTY_MAP = [
        'asset_compression_type'                   => 'string',
        'asset_url'                                => 'string',
        'id'                                       => 'string',
        'cache_key'                                => 'string',
        'filesize_bytes'                           => 'int',
        'md5_hash'                                 => 'string',
    ];
}
