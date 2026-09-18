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
use MODX\Revolution\Processors\ModelProcessor;

/**
 * Remove an Items
 */
class RemoveProcessor extends ModelProcessor {
	public $objectType = FileItem::class;
	public $classKey = FileItem::class;
	public $languageTopics = ['fileattach:default'];
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
