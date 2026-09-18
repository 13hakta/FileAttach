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
use MODX\Revolution\Processors\Model\GetProcessor as MODXGetProcessor;

/**
 * Calculate file hash
 */
class HashProcessor extends MODXGetProcessor {
	public $objectType = FileItem::class;
	public $classKey = FileItem::class;
	public $languageTopics = ['fileattach:default'];
	public $permission = 'save';

	public function cleanup() {
		$hash = sha1_file($this->object->getFullPath());

		if ($hash === false) {
			return $this->failure($this->modx->lexicon('fileattach.item_err_nf'));
		}

		$this->object->set('hash', $hash);
		$this->object->save();

		return $this->success('', ['hash' => $hash]);
	}
}
