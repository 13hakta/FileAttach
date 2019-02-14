<?php
/**
 * FileAttach
 *
 * Copyright 2015-2019 by Vitaly Checkryzhev <13hakta@gmail.com>
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
$tpl = $modx->getOption('tpl', $scriptProperties, 'FileItemTpl');
$sortby = $modx->getOption('sortBy', $scriptProperties, 'name');
$sortdir = $modx->getOption('sortDir', $scriptProperties, 'ASC');
$limit = $modx->getOption('limit', $scriptProperties, 0);
$outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, "\n");
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);
$resource = $modx->getOption('resource', $scriptProperties, 0);
$makeUrl = $modx->getOption('makeUrl', $scriptProperties, true);
$privateUrl = $modx->getOption('privateUrl', $scriptProperties, false);
$showHASH = $modx->getOption('showHASH', $scriptProperties, false);
$showSize = $modx->getOption('showSize', $scriptProperties, false);
$showExt = $modx->getOption('showExt', $scriptProperties, false);
$showTime = $modx->getOption('showTime', $scriptProperties, false);
$ext = $modx->getOption('ext', $scriptProperties, '');
$tag = $modx->getOption('tag', $scriptProperties, '');
$groups = $modx->getOption('groups', $scriptProperties, '');

$offset = $modx->getOption('offset', $scriptProperties, 0);
$totalVar = $modx->getOption('totalVar', $scriptProperties, 'total');

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

if ($makeUrl) {
	if (!$privateUrl || $showSize) {
		// Get base URLs
		$mediaSource = $modx->getOption('fileattach.mediasource', null, 1);

		$ms = $modx->getObject('sources.modMediaSource', array('id' => $mediaSource));
		$ms->initialize();

		$files_path = $modx->getOption('fileattach.files_path');
		$public_url = $ms->getBaseUrl() . $files_path;
		$docs_path  = $ms->getBasePath() . $files_path;
	}

	$private_url = $modx->getOption('fileattach.assets_url', null, $modx->getOption('assets_url')) . 'components/fileattach/';
	$private_url .= 'connector.php?action=web/download&ctx=' . $modx->context->key . '&fid=';
}

// Build query
$c = $modx->newQuery('FileItem');

if ($showHASH)
	$c->select($modx->getSelectColumns('FileItem', 'FileItem'));
else
	$c->select($modx->getSelectColumns('FileItem', 'FileItem', '', array('hash'), true));

$c->where(array('docid' => ($resource > 0)? $resource : $modx->resource->get('id')));

if ($tag != '')
	$c->where(array('tag' => $tag));

if (!empty($limit)) {
	$total = $modx->getCount('FileItem', $c);
	$modx->setPlaceholder($totalVar, $total);
}

if (!empty($limit)) $c->limit($limit, $offset);
$c->sortby($sortby, $sortdir);

$items = $modx->getIterator('FileItem', $c);

// Iterate through items
$list = array();
/** @var FileItem $item */
foreach ($items as $item) {
	$item->source = $ms;
	$item->files_path = $files_path;

	$itemArr = $item->toArray();

	if ($makeUrl) {
		if ($itemArr['private'] || $privateUrl)
			$itemArr['url'] = $private_url . $itemArr['fid'];
		else
			$itemArr['url'] = $public_url . $itemArr['path'] . $itemArr['name'];
	}

	if ($showSize)
		$itemArr['size'] = $item->getSize();

	if ($showTime)
		$itemArr['timestamp'] = filectime($item->getFullPath());

	if ($showExt) {
		$itemArr['ext'] = strtolower(
			pathinfo($itemArr['name'], PATHINFO_EXTENSION));

		if (($ext != '') && ($ext != $itemArr['ext'])) continue;
	}

	$list[] = $modx->getChunk($tpl, $itemArr);
}

// Output
$output = implode($outputSeparator, $list);
if (!empty($toPlaceholder)) {
	// If using a placeholder, output nothing and set output to specified placeholder
	$modx->setPlaceholder($toPlaceholder, $output);

	return '';
}

return $output;
