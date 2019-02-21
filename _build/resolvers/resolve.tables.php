<?php

if ($object->xpdo) {
	/** @var modX $modx */
	$modx =& $object->xpdo;

	$modelPath = $modx->getOption('fileattach.core_path', null, $modx->getOption('core_path') . 'components/fileattach/') . 'model/';
	$modx->addPackage('fileattach', $modelPath);
	$manager = $modx->getManager();

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
			// Create tables
			$objects = array(
				'FileItem', 'FileAttachMediaSource'
			);
			foreach ($objects as $tmp) {
				$manager->createObjectContainer($tmp);
			}
			break;

		case xPDOTransport::ACTION_UPGRADE:
			// Upgrade DB scheme if there were older packages installed
			// Find latest installed version

			$c = $modx->newQuery('modTransportPackage');
			$c->select(array('version_major', 'version_minor', 'version_patch'));
			$c->where(array(
				'package_name' => 'FileAttach',
					'installed:IS NOT' => NULL
			));
			$c->sortby('version_major', 'DESC');
			$c->sortby('version_minor', 'DESC');
			$c->sortby('version_patch', 'DESC');

			$package = $modx->getObject('modTransportPackage', $c);
			if ($package) {
					$oldLogLevel = $modx->getLogLevel();
					$modx->setLogLevel(0);

					$version =
					$package->get('version_major') * 1000 +
					$package->get('version_minor') * 100 +
					$package->get('version_patch');

					// Update tables
					if ($version < 1002)
						$manager->addField('FileItem', 'rank', array('after' => 'uid'));

					if ($version < 1007) {
						$manager->addField('FileItem', 'fid', array('after' => 'id'));
						$manager->addIndex('FileItem', 'fid');
					}

					if ($version < 1011) {
						$manager->addField('FileItem', 'tag', array('after' => 'hash'));
						$manager->alterField('FileItem', 'hash');
					}

					$modx->setLogLevel($oldLogLevel);
			}

			// Find old records with empty file ID
			$needID = $modx->getCollection('FileItem', array('fid' => ''));
			foreach ($needID as $item) {
				$item->set('fid', $item->generateName());
				$item->save();
			}

			break;

		case xPDOTransport::ACTION_UNINSTALL:
			break;
	}
}

return true;