<?php
/**
 * FileAttach
 *
 * Copyright 2020 by Vitaly Checkryzhev <13hakta@gmail.com>
 *
 * This file is part of FileAttach, tool to attach files to resources with
 * MODX Revolution's Manager.
 *
 * FileAttach is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation version 3,
 *
 * FileAttach is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * FileAttach; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package FileAttach
*/

/** @var array $scriptProperties */
/** @var FileAttach $FileAttach */
if (!$FileAttach = $modx->getService('fileattach', 'FileAttach', $modx->getOption('fileattach.core_path', null, $modx->getOption('core_path') . 'components/fileattach/') . 'model/fileattach/', $scriptProperties)) {
	return 'Could not load FileAttach class!';
}

// Get script options
$fid = $modx->getOption('fid', $scriptProperties, 0);
$groups = $modx->getOption('groups', $scriptProperties, '');

// Check access
if ($groups != '') {
	// Forbid access for non-authorized visitor
	if (empty($modx->user)) return;

	$accessGroups = explode(',', $groups);

	// Argument set erroneously
	if (empty($accessGroups)) return;

	$accessGroups = array_map('trim', $accessGroups);

	if (!$modx->user->isMember($accessGroups)) return;
}

// Build query
$item = $modx->getObject('FileItem', array('id' => $fid, 'docid' => $modx->resource->get('id')));

$itemArr = $item->toArray();

if ($itemArr['private']) {
	$private_url = $modx->getOption('fileattach.assets_url', null, $modx->getOption('assets_url')) .
		'components/fileattach/connector.php?action=web/download&ctx=' .
		$modx->context->key . '&inline=1&fid=';

	$itemArr['url'] = $private_url . $itemArr['fid'];
} else
	$itemArr['url'] = $public_url . $itemArr['path'] . $itemArr['name'];

return $itemArr['url'];
