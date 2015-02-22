<?php

if ($object->xpdo) {
	/** @var modX $modx */
	$modx =& $object->xpdo;

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
			$modelPath = $modx->getOption('fileattach.core_path', null, $modx->getOption('core_path') . 'components/fileattach/') . 'model/';
			$modx->addPackage('fileattach', $modelPath);

			$manager = $modx->getManager();
			$objects = array(
				'FileItem', 'FileAttachMediaSource'
			);
			foreach ($objects as $tmp) {
				$manager->createObjectContainer($tmp);
			}
			break;

		case xPDOTransport::ACTION_UPGRADE:
			// Upgrade DB scheme if there were older packages installed
			$package = $modx->getObject('modTransportPackage', array(
			    'package_name' => PKG_NAME,
			    'version_major' => '1',
			    'version_minor' => '0',
			    'version_patch:<' => '2'));

			if ($package) {
        		    $oldLogLevel = $modx->getLogLevel();
        		    $modx->setLogLevel(0);

			    $modelPath = $modx->getOption('fileattach.core_path', null, $modx->getOption('core_path') . 'components/fileattach/') . 'model/';
			    $modx->addPackage('fileattach', $modelPath);

			    $manager = $modx->getManager();
        		    $manager->addField('FileItem', 'rank');

        		    $modx->setLogLevel($oldLogLevel);
			}

			break;

		case xPDOTransport::ACTION_UNINSTALL:
			break;
	}
}
return true;