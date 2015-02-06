<?php

/**
 * Default FileAttach Access Policies
 *
 * @package fileattach
 * @subpackage build
 */
function bld_policyFormatData($permissions) {
    $data = array();
    foreach ($permissions as $permission) {
        $data[$permission->get('name')] = true;
    }
    return $data;
}
$policies = array();
$policies[1]= $modx->newObject('modAccessPolicy');
$policies[1]->fromArray(array (
  'id' => 1,
  'name' => 'File Attach',
  'description' => 'A policy for editing attached files to resources.',
  'parent' => 0,
  'class' => '',
  'lexicon' => 'fileattach:permissions',
  'data' => '{"fileattach.totallist":true,"fileattach.doclist":true,"fileattach.download":true}',), '', true, true);

return $policies;