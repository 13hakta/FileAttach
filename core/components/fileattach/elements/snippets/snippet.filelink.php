<?php
/**
 * FileAttach
 *
 * Copyright 2020-2026 by Vitaly Checkryzhev <13hakta@gmail.com>
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

use FileAttach\Model\FileItem;
use MODX\Revolution\Sources\modMediaSource;

/** @var modX $modx */
/** @var array $scriptProperties */

$corePath = $modx->getOption('fileattach.core_path', $scriptProperties, $modx->getOption('core_path') . 'components/fileattach/');

if (!class_exists(\FileAttach\FileAttach::class)) {
	require_once $corePath . 'bootstrap.php';
}

if (!$FileAttach = $modx->getService('fileattach', \FileAttach\FileAttach::class, $corePath, $scriptProperties)) {
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

// Get item
$item = $modx->getObject(FileItem::class, ['id' => $fid, 'docid' => $modx->resource->get('id')]);

if (!$item) return '';

$itemArr = $item->toArray();

if ($itemArr['private']) {
	$private_url = $modx->getOption('fileattach.assets_url', null, $modx->getOption('assets_url')) .
		'components/fileattach/connector.php?action=web/download&ctx=' .
		$modx->context->key . '&inline=1&fid=';

	$itemArr['url'] = $private_url . $itemArr['fid'];
} else {
	// Public file: build url from the media source
	$mediaSource = $modx->getOption('fileattach.mediasource', null, 1);

	/** @var modMediaSource|null $ms */
	$ms = $modx->getObject(modMediaSource::class, ['id' => $mediaSource]);
	if (!$ms) {
		$modx->log(xPDO::LOG_LEVEL_ERROR, '[FileAttach] Could not load media source: ' . $mediaSource);
		return '';
	}

	$ms->initialize();

	$files_path = $modx->getOption('fileattach.files_path');
	$itemArr['url'] = $ms->getBaseUrl() . $files_path . $itemArr['path'] . $itemArr['internal_name'];
}

return $itemArr['url'];
