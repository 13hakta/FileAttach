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
 * @package FileAttach
*/

/**
 * Class FileAttachMainController
 */
abstract class FileAttachMainController extends modExtraManagerController {
	/** @var FileAttach $FileAttach */
	public $FileAttach;


	/**
	 * @return void
	 */
	public function initialize() {
		$corePath = $this->modx->getOption('fileattach.core_path', null, $this->modx->getOption('core_path') . 'components/fileattach/');
		require_once $corePath . 'model/fileattach/fileattach.class.php';

		$this->FileAttach = new FileAttach($this->modx);
		$this->addJavascript($this->FileAttach->config['jsUrl'] . 'mgr/fileattach.js');
		$this->addHtml('<script type="text/javascript">
			FileAttach.config = ' . $this->modx->toJSON($this->FileAttach->config) . ';
		</script>');

		parent::initialize();
	}


	/**
	 * @return array
	 */
	public function getLanguageTopics() {
		return array('fileattach:default');
	}


	/**
	 * @return bool
	 */
	public function checkPermissions() {
		return true;
	}
}


/**
 * Class IndexManagerController
 */
class IndexManagerController extends FileAttachMainController {
	/**
	 * @return string
	 */
	public static function getDefaultController() {
		return 'home';
	}
}