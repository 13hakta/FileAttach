<?php
/**
 * FileAttach
 *
 * Copyright 2015-2026 by Vitaly Checkryzhev <13hakta@gmail.com>
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
 * @package fileattach
 */

$success = false;

if ($object->xpdo) {
	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		// The fileattach namespace bootstrap.php now takes care of
		// autoloading and package registration on every request
		case xPDOTransport::ACTION_UPGRADE:
		case xPDOTransport::ACTION_INSTALL:
			$modx =& $object->xpdo;

			// Remove legacy extension package record from 1.x versions
			if ($modx instanceof modX)
				$modx->removeExtensionPackage('fileattach');

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
