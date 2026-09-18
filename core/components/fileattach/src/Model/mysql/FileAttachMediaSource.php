<?php
namespace FileAttach\Model\mysql;

use xPDO\xPDO;

class FileAttachMediaSource extends \FileAttach\Model\FileAttachMediaSource
{

    public static $metaMap = array (
        'package' => 'FileAttach\\Model',
        'version' => '3.0',
        'extends' => 'MODX\\Revolution\\Sources\\modMediaSource',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
        ),
        'fieldMeta' => 
        array (
        ),
    );

}
