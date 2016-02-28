<?php
/**
* The default Permission scheme for the FileAttach.
*
* @package FileAttach
* @subpackage build
*/
$permissions = array();

$permissions[] = $modx->newObject('modAccessPermission',array(
    'name' => 'fileattach.totallist',
    'description' => 'perm.fileattach_all',
    'value' => true,
));

$permissions[] = $modx->newObject('modAccessPermission',array(
    'name' => 'fileattach.doclist',
    'description' => 'perm.fileattach_doc',
    'value' => true,
));

$permissions[] = $modx->newObject('modAccessPermission',array(
    'name' => 'fileattach.download',
    'description' => 'perm.fileattach_download',
    'value' => true,
));

$permissions[] = $modx->newObject('modAccessPermission',array(
    'name' => 'fileattach.list',
    'description' => 'perm.fileattach_list',
    'value' => true,
));

$permissions[] = $modx->newObject('modAccessPermission',array(
    'name' => 'fileattach.remove',
    'description' => 'perm.fileattach_remove',
    'value' => true,
));

return $permissions;