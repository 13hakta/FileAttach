<?php
/**
 * FileAttach
 *
 * Copyright 2015 by Vitaly Checkryzhev <13hakta@gmail.com>
 *
 * This file is part of FileAttach, a simple commenting component for MODx Revolution.
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
/**
 * Auto-assign the FileAttachPolicy to the Administrator User Group
 *
 * @package fileattach
 * @subpackage build
 */
if ($object->xpdo) {
	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
		case xPDOTransport::ACTION_UPGRADE:
			$modx =& $object->xpdo;
			$modelPath = $modx->getOption('fileattach.core_path', null, $modx->getOption('core_path') . 'components/fileattach/') . 'model/';
			$modx->addPackage('fileattach', $modelPath);

			$modx->setLogLevel(modX::LOG_LEVEL_ERROR);

			/* assign policy to template */
			$template = $transport->xpdo->getObject('modAccessPolicyTemplate', array('name' => 'FileAttachTemplate'));
			if (!$template)
				$modx->log(xPDO::LOG_LEVEL_ERROR,'[FileAttach] Could not find FileAttacTemplate Access Policy Template!');

			$policyList = array('File Attach', 'File Attach Download', 'File Attach Frontend');

			foreach ($policyList as $policyName) {
				$policy = $transport->xpdo->getObject('modAccessPolicy', array('name' => $policyName));

				if ($policy) {
					$policy->set('template', $template->get('id'));
					$policy->save();
				} else
					$modx->log(xPDO::LOG_LEVEL_ERROR,'[FileAttach] Could not find ' . $policyName . ' Access Policy!');
			}

			/* assign policy to admin group */
			$policy = $modx->getObject('modAccessPolicy', array('name' => 'File Attach'));

			$adminGroup = $modx->getObject('modUserGroup', array('name' => 'Administrator'));
			if ($policy && $adminGroup) {
				$access = $modx->getObject('modAccessContext', array(
					'target' => 'mgr',
					'principal_class' => 'modUserGroup',
					'principal' => $adminGroup->get('id'),
					'authority' => 9999,
					'policy' => $policy->get('id'),
				));
				if (!$access) {
					$access = $modx->newObject('modAccessContext');
					$access->fromArray(array(
						'target' => 'mgr',
						'principal_class' => 'modUserGroup',
						'principal' => $adminGroup->get('id'),
						'authority' => 9999,
						'policy' => $policy->get('id'),
					));
					$access->save();
				}
			}

			/* assign policy to anonymous group */
			if (isset($options['allow_anonymous'])) {
				$policy = $modx->getObject('modAccessPolicy', array('name' => 'File Attach Download'));

				$access = $modx->getObject('modAccessContext', array(
					'target' => 'web',
					'principal_class' => 'modUserGroup',
					'principal' => 0,
					'authority' => 9999,
					'policy' => $policy->get('id'),
				));
				if (!$access) {
					$access = $modx->newObject('modAccessContext');
					$access->fromArray(array(
						'target' => 'web',
						'principal_class' => 'modUserGroup',
						'principal' => 0,
						'authority' => 9999,
						'policy' => $policy->get('id'),
					));
					$access->save();
				}
			}

			$modx->setLogLevel(modX::LOG_LEVEL_INFO);
			break;
	}
}

return true;