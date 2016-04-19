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
 * Update an Item
 */
class FileItemUpdateProcessor extends modObjectUpdateProcessor {
	public $objectType = 'FileItem';
	public $classKey = 'FileItem';
	public $languageTopics = array('fileattach');
	public $permission = 'save';


	/**
	 * We doing special check of permission
	 * because of our objects is not an instances of modAccessibleObject
	 *
	 * @return bool|string
	 */
	public function beforeSave() {
		if (!$this->checkPermissions())
			return $this->modx->lexicon('access_denied');

		return true;
	}


	/**
	 * @return bool
	 */
	public function beforeSet() {
		$id = (int)$this->getProperty('id');
		$docid = (int)$this->getProperty('docid');

		if (empty($id))
			return $this->modx->lexicon('fileattach.item_err_ns');

		if (!$docid)
			$this->modx->error->addField('docid', $this->modx->lexicon('notset'));

		$private = ($this->getProperty('private'))? true : false;

		// Allow filename change only in private mode. May be changed further
		$name = trim($this->getProperty('name'));
		$name = $this->object->sanitizeName($name);
		if (empty($name))
			$this->modx->error->addField('name', $this->modx->lexicon('fileattach.item_err_name'));

		// If file is open we should rename file, otherwize just change field value
		if (!$this->object->get('private')) {
			$this->unsetProperty('name');

			// Rename if name changed
			if ($name != $this->object->get('name'))
				if (!$this->object->rename($name))
					$this->modx->error->addField('name', $this->modx->lexicon('fileattach.item_err_nr'));
		}

		if (!$this->object->setPrivate($private))
			$this->modx->error->addField('name', $this->modx->lexicon('fileattach.item_err_nr'));

		return parent::beforeSet();
	}
}

return 'FileItemUpdateProcessor';