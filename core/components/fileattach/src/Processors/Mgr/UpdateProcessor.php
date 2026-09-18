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
use MODX\Revolution\Processors\Model\UpdateProcessor as MODXUpdateProcessor;

/**
 * Update an Item
 */
class UpdateProcessor extends MODXUpdateProcessor {
	public $objectType = FileItem::class;
	public $classKey = FileItem::class;
	public $languageTopics = ['fileattach:default'];
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
		$id = (int) $this->getProperty('id');
		$docid = (int) $this->getProperty('docid');

		if (empty($id))
			return $this->modx->lexicon('fileattach.item_err_ns');

		if (!$docid)
			$this->modx->error->addField('docid', $this->modx->lexicon('notset'));

		$private = ($this->getProperty('private'))? true : false;

		// Allow filename change only in private mode. May be changed further
		$name = trim($this->getProperty('name'));
		$name = FileItem::sanitizeName($name);
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
