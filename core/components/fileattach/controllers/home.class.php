<?php
/**
 * FileAttach
 *
 * Copyright 2015-2017 by Vitaly Checkryzhev <13hakta@gmail.com>
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
 * The home manager controller for FileAttach.
 *
 */
class FileAttachHomeManagerController extends FileAttachMainController {
	/* @var FileAttach $FileAttach */
	public $FileAttach;


	/**
	 * @param array $scriptProperties
	 */
	public function process(array $scriptProperties = array()) {}


	/**
	 * @return null|string
	 */
	public function getPageTitle() {
		return $this->modx->lexicon('fileattach');
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
	 * @return string
	 */
	public function getTemplateFile() {
		return $this->FileAttach->config['templatesPath'] . 'home.tpl';
	}
}