<?php
/**
 * FileAttach
 *
 * Copyright 2015-2018 by Vitaly Checkryzhev <13hakta@gmail.com>
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
 * Class FileattachManageManagerController
 */
class FileattachListManagerController extends modExtraManagerController {
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
	 * @return void
	 */
	public function loadCustomCssJs() {
		$this->addJavascript($this->FileAttach->config['jsUrl'] . 'mgr/widgets/items.grid.js');
		$this->addJavascript($this->FileAttach->config['jsUrl'] . 'mgr/widgets/home.panel.js');
		$this->addHtml('<script type="text/javascript">
		Ext.onReady(function() {
			MODx.load({ xtype: "fileattach-page-home"});
		});
		</script>');
	}


	/**
	 * @return null|string
	 */
	public function getPageTitle() {
		return $this->modx->lexicon('fileattach');
	}


	/**
	 * @return array
	 */
	public function getLanguageTopics() {
		return array('fileattach:default');
	}


	/**
	 * @return string
	 */
	public function getTemplateFile() {
		return $this->FileAttach->config['templatesPath'] . 'home.tpl';
	}


	/**
	 * @return bool
	 */
	public function checkPermissions() { return true; }
}
