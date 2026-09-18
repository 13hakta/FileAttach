<?php
/**
 * FileAttach
 *
 * Copyright 2015-2026 by Vitaly Checkryzhev <13hakta@gmail.com>
 *
 * @package FileAttach
 */

namespace FileAttach\Processors\Mgr;

use FileAttach\Model\FileItem;
use MODX\Revolution\modResource;
use MODX\Revolution\modUser;
use MODX\Revolution\Processors\Model\GetListProcessor as MODXGetListProcessor;
use xPDO\Om\xPDOQuery;

/**
 * Get a list of Items
 */
class GetListProcessor extends MODXGetListProcessor {
	public $objectType = FileItem::class;
	public $classKey = FileItem::class;
	public $languageTopics = ['fileattach:default'];
	public $defaultSortField = 'id';
	public $defaultSortDirection = 'DESC';
	public $permission = 'list';


	/**
	 * We doing special check of permission
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

		$c->select($this->modx->getSelectColumns(FileItem::class, 'FileItem'));

		if ($query)
			$c->where(['name:LIKE' => "%$query%"]);

		if ($uid || ($docid == 0)) {
			$c->select('User.username');
			$c->leftJoin(modUser::class, 'User', 'User.id=FileItem.uid');
		}

		if ($uid)
			$c->where(['User.username:LIKE' => "%$uid%"]);

		if ($docid > 0)
			$c->where(['docid' => $docid]);
		else {
			$c->select('Res.pagetitle');
			$c->leftJoin(modResource::class, 'Res', 'Res.id=FileItem.docid');
		}

		return $c;
	}
}
