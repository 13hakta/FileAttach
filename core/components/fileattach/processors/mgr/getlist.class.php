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
 * Get a list of Items
 */
class FileItemGetListProcessor extends modObjectGetListProcessor {
	public $objectType = 'FileItem';
	public $classKey = 'FileItem';
	public $defaultSortField = 'id';
	public $defaultSortDirection = 'DESC';
	public $permission = 'list';

	/**
	 * * We doing special check of permission
	 * because of our objects is not an instances of modAccessibleObject
	 *
	 * @return boolean|string
	 */
	public function beforeQuery() {
		if (!$this->checkPermissions())
			return $this->modx->lexicon('access_denied');

		return true;
	}


	/**
	 * @param xPDOQuery $c
	 *
	 * @return xPDOQuery
	 */
	public function prepareQueryBeforeCount(xPDOQuery $c) {
		$docid = (int) $this->getProperty('docid');
		$uid = trim($this->getProperty('uid'));
		$query = trim($this->getProperty('query'));

		$c->select($this->modx->getSelectColumns('FileItem', 'FileItem'));

		if ($query)
			$c->where(array('name:LIKE' => "%$query%"));

		if ($uid || ($docid == 0)) {
			$c->select('User.username');
			$c->leftJoin('modUser', 'User', 'User.id=FileItem.uid');
		}

		if ($uid)
			$c->where(array('User.username:LIKE' => "%$uid%"));

		if ($docid > 0)
			$c->where(array('docid' => $docid));
		else {
			$c->select('Res.pagetitle');
			$c->leftJoin('modResource', 'Res', 'Res.id=FileItem.docid');
		}

		return $c;
	}
}

return 'FileItemGetListProcessor';