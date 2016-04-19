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
 * Get a list of Items
 */
class FileItemGetListProcessor extends modObjectGetListProcessor {
	public $objectType = 'FileItem';
	public $classKey = 'FileItem';
	public $defaultSortField = 'id';
	public $defaultSortDirection = 'DESC';
	public $permission = 'fileattach.list';

	/**
	 * * We doing special check of permission
	 * because of our objects is not an instances of modAccessibleObject
	 *
	 * @return boolean|string
	 */
	public function beforeQuery() {
		if (!$this->checkPermissions())
			return $this->modx->lexicon('access_denied');

		$docid = (int) $this->getProperty('docid');

		if (!$docid)
			return $this->modx->lexicon('fileattach.item_err_ns');

		return true;
	}


	/**
	 * @param xPDOQuery $c
	 *
	 * @return xPDOQuery
	 */
	public function prepareQueryBeforeCount(xPDOQuery $c) {
		$docid = (int) $this->getProperty('docid');
		$query = trim($this->getProperty('query'));

		$c->select($this->modx->getSelectColumns('FileItem'));

		if ($query)
			$c->where(array('name:LIKE' => "%$query%"));

		$c->where(array('docid' => $docid));

		return $c;
	}

	public function prepareRow(xPDOObject $object) {
		$resArray = array(
			'id' => $object->get('id'),
			'fid' => $object->get('fid'),
			'name' => $object->get('name'),
			'hash' => $object->get('hash')
		);

		return $resArray;
	}
}

return 'FileItemGetListProcessor';