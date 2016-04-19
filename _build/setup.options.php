<?php

$output = null;
$installed = false;

$value = $modx->getOption('extension_packages');
if ($value) {
	$exts = $modx->fromJSON($value);
	foreach ($exts as $idx => $extPack) {
		foreach ($extPack as $key => $opt) {
			if ($key == 'fileattach') {
				$installed = true;
				break;
			}
		}
	}
}

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
			$output = '<label><input type="checkbox" name="install_pack"' . (($installed)? ' checked' : '') . '> Установить пакет расширения</label>
Позволяет использовать файловый медиа источник<br/><br/>';

		$output .= ($access)?
			'Анонимным пользователям разрешено скачивание' :
			'<label><input type="checkbox" name="allow_anonymous"> Разрешить скачивание анонимам</label>';
		} else {
			$output = '<label><input type="checkbox" name="install_pack"' . (($installed)? ' checked' : '') . '> Install extension package</label>
This will allow to use media file source<br/><br/>';

		$output .= ($access)?
			'Anonymous users downloading allowed' :
			'<label><input type="checkbox" name="allow_anonymous"> Allow anonymous download</label>';
		}

	case xPDOTransport::ACTION_UNINSTALL:
		break;
}

return $output;
