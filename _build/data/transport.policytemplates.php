<?php
/**
* Default FileAttach Policy Templates
*
* @package fileattach
* @subpackage build
*/
$templates = array();

/* administrator template/policy */
$templates['1']= $modx->newObject('modAccessPolicyTemplate');
$templates['1']->fromArray(array(
    'id' => 1,
    'name' => 'FileAttachTemplate',
    'description' => 'A policy template for attached files containers.',
    'lexicon' => 'fileattach:permissions',
    'template_group' => 1,
));

$permissions = include dirname(__FILE__).'/permissions/fileattach.policy.php';

if (is_array($permissions)) {
 $templates['1']->addMany($permissions);
} else {
 $modx->log(modX::LOG_LEVEL_ERROR,'Could not load FileAttach Policy Template.');
}

return $templates;