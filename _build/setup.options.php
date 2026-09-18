<?php

$output = null;

// Check if anonymous policy is installed
$access = NULL;

$policy = $modx->getObject('modAccessPolicy', array('name' => 'File Attach Download'));
if ($policy) {
	$access = $modx->getObject('modAccessContext', array(
		'target' => 'web',
		'principal_class' => 'modUserGroup',
		'principal' => 0,
		'authority' => 9999,
		'policy' => $policy->get('id')
  ));
}

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
	case xPDOTransport::ACTION_INSTALL:
	case xPDOTransport::ACTION_UPGRADE:
		if ($modx->getOption('manager_language') == 'ru') {
			$output = ($access)?
				'Анонимным пользователям разрешено скачивание' :
				'<label><input type="checkbox" name="allow_anonymous"> Разрешить скачивание анонимам</label>';
		} else {
			$output = ($access)?
				'Anonymous users downloading allowed' :
				'<label><input type="checkbox" name="allow_anonymous"> Allow anonymous download</label>';
		}

	case xPDOTransport::ACTION_UNINSTALL:
		break;
}

return $output;
