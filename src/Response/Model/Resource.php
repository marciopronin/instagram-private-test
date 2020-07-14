<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Resource.
 *
 * @method int getBuildNumber()
 * @method string getCompressionFormat()
 * @method string getDownloadUrl()
 * @method string getFileChecksum()
 * @method int getFileSize()
 * @method string getResourceFlavor()
 * @method string getResourceName()
 * @method bool isBuildNumber()
 * @method bool isCompressionFormat()
 * @method bool isDownloadUrl()
 * @method bool isFileChecksum()
 * @method bool isFileSize()
 * @method bool isResourceFlavor()
 * @method bool isResourceName()
 * @method $this setBuildNumber(int $value)
 * @method $this setCompressionFormat(string $value)
 * @method $this setDownloadUrl(string $value)
 * @method $this setFileChecksum(string $value)
 * @method $this setFileSize(int $value)
 * @method $this setResourceFlavor(string $value)
 * @method $this setResourceName(string $value)
 * @method $this unsetBuildNumber()
 * @method $this unsetCompressionFormat()
 * @method $this unsetDownloadUrl()
 * @method $this unsetFileChecksum()
 * @method $this unsetFileSize()
 * @method $this unsetResourceFlavor()
 * @method $this unsetResourceName()
 */
class Resource extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'resource_name'             => 'string',
        'resource_flavor'           => 'string',
        'download_url'              => 'string',
        'file_checksum'             => 'string',
        'file_size'                 => 'int',
        'compression_format'        => 'string',
        'build_number'              => 'int',
    ];
}
