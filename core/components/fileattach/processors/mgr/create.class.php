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
 * Create an Item
 */
class FileItemCreateProcessor extends modObjectCreateProcessor {
	public $objectType = 'FileItem';
	public $classKey = 'FileItem';
	public $languageTopics = array('fileattach');
	public $permission = 'create';

	/**
	 * @return bool
	 */
	public function beforeSet() {
		$docid = (int) $this->getProperty('docid');

		if (!$docid)
			$this->modx->error->addField('docid', $this->modx->lexicon('notset'));

		$name = trim($this->getProperty('name'));
		$name = $this->object->sanitizeName($name);
		$this->setProperty('name', $name);

		$this->setProperty('fid', $this->object->generateName());

		if (empty($name))
			$this->modx->error->addField('name', $this->modx->lexicon('fileattach.item_err_name'));

		return parent::beforeSet();
	}
}

return 'FileItemCreateProcessor';