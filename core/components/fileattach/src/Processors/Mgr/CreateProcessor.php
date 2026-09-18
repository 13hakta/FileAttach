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
use MODX\Revolution\Processors\Model\CreateProcessor as MODXCreateProcessor;

/**
 * Create an Item
 */
class CreateProcessor extends MODXCreateProcessor {
	public $objectType = FileItem::class;
	public $classKey = FileItem::class;
	public $languageTopics = ['fileattach:default'];
	public $permission = 'create';


	/**
	 * @return bool
	 */
	public function beforeSet() {
		$docid = (int) $this->getProperty('docid');

		if (!$docid)
			$this->modx->error->addField('docid', $this->modx->lexicon('notset'));

		$name = trim($this->getProperty('name'));
		$name = FileItem::sanitizeName($name);
		$this->setProperty('name', $name);

		$this->setProperty('fid', FileItem::generateName());

		if (empty($name))
			$this->modx->error->addField('name', $this->modx->lexicon('fileattach.item_err_name'));

		return parent::beforeSet();
	}
}
