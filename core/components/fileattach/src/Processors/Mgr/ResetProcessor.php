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
use MODX\Revolution\Processors\ModelProcessor;

/**
 * Reset download counter
 */
class ResetProcessor extends ModelProcessor {
	public $objectType = FileItem::class;
	public $classKey = FileItem::class;
	public $languageTopics = ['fileattach:default'];
	public $permission = 'save';


	/**
	 * @return array|string
	 */
	public function process() {
		if (!$this->checkPermissions())
			return $this->failure($this->modx->lexicon('access_denied'));

		$ids = $this->modx->fromJSON($this->getProperty('ids'));
		if (empty($ids))
			return $this->failure($this->modx->lexicon('fileattach.item_err_ns'));

		foreach ($ids as $id) {
			/** @var FileItem $object */
			if (!$object = $this->modx->getObject($this->classKey, $id))
				return $this->failure($this->modx->lexicon('fileattach.item_err_nf'));

			$object->set('download', 0);
			$object->save();
		}

		return $this->success();
	}
}
