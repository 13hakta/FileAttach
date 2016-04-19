<?php
/**
 * FileAttach
 *
 * Copyright 2015-2016 by Vitaly Checkryzhev <13hakta@gmail.com>
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

switch ($modx->event->name) {
	// Add a custom tab to the MODX create/edit resource pages
	case 'OnDocFormPrerender':

		// Check access
		if (!$modx->hasPermission('fileattach.doclist')) return;

		// Skip form building when resource template is not in permitted list
		$templates = $modx->getOption('fileattach.templates');

		if ($templates != '') {
			$templatelist = explode(',', $templates);
			$template = is_object($resource)? $resource->get('template') : 0;
			if (!in_array($template, $templatelist)) return;
		}

		$modx->controller->addLexiconTopic('fileattach:default');

		$corePath = $modx->getOption('fileattach.core_path', null, $modx->getOption('core_path') . 'components/fileattach/');
		require_once $corePath . 'model/fileattach/fileattach.class.php';

		$modx->FileAttach = new FileAttach($modx);
		$modx->controller->addJavascript($modx->FileAttach->config['jsUrl'] . 'mgr/fileattach.js');
		$modx->controller->addJavascript($modx->FileAttach->config['jsUrl'] . 'mgr/widgets/items.grid.js');
		$modx->controller->addLastJavascript($modx->FileAttach->config['jsUrl'] . 'mgr/filestab.js');
		$modx->controller->addHtml('<script type="text/javascript">FileAttach.config = ' . $modx->toJSON($modx->FileAttach->config) . ';</script>');

		break;

	// Remove attached files to resources
	case 'OnEmptyTrash':
		// Load service
		if (!$FileAttach = $modx->getService('fileattach', 'FileAttach',
			$modx->getOption('fileattach.core_path',
			null,
			$modx->getOption('core_path') . 'components/fileattach/') . 'model/fileattach/')) {
			$modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not load FileAttach class OnEmptyTrash!');
			return;
		}

		foreach ($ids as &$id) {
			$c = $modx->newQuery('FileItem');
			$c->where(array('docid' => $id));

			$iter = $modx->getIterator('FileItem', $c);
			foreach ($iter as $item) $item->remove();
		}

		break;
}