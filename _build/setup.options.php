<?php

$output = null;
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
	case xPDOTransport::ACTION_INSTALL:
	case xPDOTransport::ACTION_UPGRADE:
		return '<label><input type="checkbox" name="install_pack"> Install extension package</label><br/>
This will allow to use media file source.';
		break;

	case xPDOTransport::ACTION_UNINSTALL:
		break;
}
