<?php

/**
 * Default FileAttach Access Policies
 *
 * @package fileattach
 * @subpackage build
 */

$policies = array();

$tmp = array(
	'File Attach' => array(
	    'description' => 'A policy for editing attached files to resources.',
	    'data' => '{"fileattach.totallist":true,"fileattach.doclist":true,"fileattach.download":true,"fileattach.list":true,"fileattach.remove":true}'),
	'File Attach Download' => array(
	    'description' => 'A policy for downloading attached files to resources.',
	    'data' => '{"fileattach.download":true}'),
	'File Attach Frontend' => array(
	    'description' => 'A policy for frontend uploading files to resources.',
	    'data' => '{"fileattach.download":true,"fileattach.list":true,"fileattach.remove":true}')
	);

foreach ($tmp as $k => $v) {
	/* @avr modplugin $plugin */
	$policy = $modx->newObject('modAccessPolicy');

	$policy->fromArray(array(
		'name' => $k,
		'parent' => 0,
		'class' => '',
		'lexicon' => PKG_NAME_LOWER . ':permissions',
		'data' => @$v['data'],
		'description' => @$v['description']
	), '', true, true);

	$policies[] = $policy;
}

unset($tmp, $template);
return $policies;