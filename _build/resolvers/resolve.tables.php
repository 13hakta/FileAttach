<?php

if ($object->xpdo) {
	/** @var modX $modx */
	$modx =& $object->xpdo;

	$corePath = $modx->getOption('fileattach.core_path', null, $modx->getOption('core_path') . 'components/fileattach/');

	// The namespace bootstrap is not loaded yet during install,
	// so register the composer autoloader before working with the model
	$autoload = $corePath . 'vendor/autoload.php';
	if (file_exists($autoload)) {
		require_once $autoload;
	}

	$modx->addPackage('FileAttach\\Model', $corePath . 'src/', null, 'FileAttach');

	$manager = $modx->getManager();

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
			// Create tables
			$objects = array(
				'FileAttach\Model\FileItem'
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
					$manager->addField('FileAttach\Model\FileItem', 'rank', array('after' => 'uid'));

				if ($version < 1007) {
					$manager->addField('FileAttach\Model\FileItem', 'fid', array('after' => 'id'));
					$manager->addIndex('FileAttach\Model\FileItem', 'fid');
				}

				if ($version < 1011) {
					$manager->addField('FileAttach\Model\FileItem', 'tag', array('after' => 'hash'));
					$manager->alterField('FileAttach\Model\FileItem', 'hash');
				}

				$modx->setLogLevel($oldLogLevel);
			}

			// Find old records with empty file ID
			$needID = $modx->getCollection('FileAttach\Model\FileItem', array('fid' => ''));
			foreach ($needID as $item) {
				$item->set('fid', \FileAttach\Model\FileItem::generateName());
				$item->save();
			}

			break;

		case xPDOTransport::ACTION_UNINSTALL:
			break;
	}
}

return true;
