<?php
/**
 * FileAttach
 *
 * Copyright 2016 by Vitaly Checkryzhev <13hakta@gmail.com>
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
 * Remove an Items
 */
class FileItemRemoveProcessor extends modObjectProcessor {
	public $objectType = 'FileItem';
	public $classKey = 'FileItem';
	public $languageTopics = array('File');
	public $permission = 'fileattach.remove';
	public $permission2 = 'fileattach.totallist';


	/**
	 * @return array|string
	 */
	public function process() {
		if (!$this->checkPermissions())
			return $this->failure($this->modx->lexicon('access_denied'));

		$adminmode = $this->modx->hasPermission($this->permission2);

		$docid = (int) $this->getProperty('docid');

		if (!$docid)
			return $this->failure($this->modx->lexicon('fileattach.item_err_ns'));

		$ids = $this->modx->fromJSON($this->getProperty('ids'));
		if (empty($ids))
			return $this->failure($this->modx->lexicon('fileattach.item_err_ns'));

		foreach ($ids as $id) {
			/** @var FileItem $object */
			if (!$object = $this->modx->getObject($this->classKey, $id))
				return $this->failure($this->modx->lexicon('fileattach.item_err_nf'));

			// Forbid deletion for another resources
			if ($object->get('docid') != $docid)
				return $this->failure($this->modx->lexicon('fileattach.item_err_remove'));

			// Allow remove only for admins and file owners
			if ($adminmode || (($this->modx->user->get('id') == $object->get('uid'))))
				$object->remove();
			else
				return $this->failure($this->modx->lexicon('fileattach.item_err_remove'));
		}

		return $this->success();
	}
}

return 'FileItemRemoveProcessor';