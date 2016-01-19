<?php

$output = null;
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
	case xPDOTransport::ACTION_INSTALL:
	case xPDOTransport::ACTION_UPGRADE:
	    if ($modx->getOption('manager_language') == 'ru') {
		return '<label><input type="checkbox" name="install_pack"> Установить пакет расширения</label>
Позволяет использовать файловый медиа источник<br/><br/>
<label><input type="checkbox" name="allow_anonymous"> Разрешить скачивание анонимам</label>';
	    } else {
		return '<label><input type="checkbox" name="install_pack"> Install extension package</label>
This will allow to use media file source<br/><br/>
<label><input type="checkbox" name="allow_anonymous"> Allow anonymous download</label>';
	    }

	case xPDOTransport::ACTION_UNINSTALL:
		break;
}
