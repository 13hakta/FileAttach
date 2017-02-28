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
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * FileAttach is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * FileAttach; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package fileattach
 */

$success = false;

if ($object->xpdo) {
	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_UPGRADE:
			if (!isset($options['install_pack'])) {
				$modx =& $object->xpdo;
				if ($modx instanceof modX)
					$modx->removeExtensionPackage('fileattach');
			}

		case xPDOTransport::ACTION_INSTALL:
			if (isset($options['install_pack'])) {
				/** @var modX $modx */
				$modx =& $object->xpdo;
				$modelPath = $modx->getOption('fileattach.core_path');
				if (empty($modelPath))
					$modelPath = '[[++core_path]]components/fileattach/';

				$modelPath = rtrim($modelPath, '/') . '/model/';
				if ($modx instanceof modX)
					$modx->addExtensionPackage('fileattach', $modelPath);
			}

			$success = true;
			break;
		case xPDOTransport::ACTION_UNINSTALL:
			$modx =& $object->xpdo;
			if ($modx instanceof modX)
				$modx->removeExtensionPackage('fileattach');

			$success = true;
			break;
	}
}

return $success;