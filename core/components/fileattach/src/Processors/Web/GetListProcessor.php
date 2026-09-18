<?php
/**
 * FileAttach
 *
 * Copyright 2015-2026 by Vitaly Checkryzhev <13hakta@gmail.com>
 *
 * @package FileAttach
 */

namespace FileAttach\Processors\Web;

use FileAttach\Model\FileItem;
use MODX\Revolution\Processors\Model\GetListProcessor as MODXGetListProcessor;
use xPDO\Om\xPDOObject;
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
	public $permission = 'fileattach.list';


	/**
	 * We doing special check of permission
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

		$c->select($this->modx->getSelectColumns(FileItem::class));

		if ($query)
			$c->where(['name:LIKE' => "%$query%"]);

		$c->where(['docid' => $docid]);

		return $c;
	}

	public function prepareRow(xPDOObject $object) {
		return [
			'id' => $object->get('id'),
			'fid' => $object->get('fid'),
			'name' => $object->get('name'),
			'hash' => $object->get('hash')
		];
	}
}
