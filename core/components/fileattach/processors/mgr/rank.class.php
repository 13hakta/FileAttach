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
 * Set rank
 */
class FileItemSetRankProcessor extends modObjectProcessor {
	public $objectType = 'FileItem';
	public $classKey = 'FileItem';
	public $languageTopics = array('fileattach');
	public $permission = 'save';

	/**
	 * @return array|string
	 */
	public function process() {
		if (!$this->checkPermissions())
			return $this->failure($this->modx->lexicon('access_denied'));

		$ranklist = $this->modx->fromJSON($this->getProperty('rank'));

		if (empty($ranklist))
			return $this->failure($this->modx->lexicon('fileattach.item_err_ns'));

		foreach ($ranklist as $id => $value) {
			/** @var FileItemItem $object */
			if (!$object = $this->modx->getObject($this->classKey, $id))
				return $this->failure($this->modx->lexicon('fileattach.item_err_nf'));

			$object->set('rank', $value);
			$object->save();
		}

		return $this->success();
	}
}

return 'FileItemSetRankProcessor';