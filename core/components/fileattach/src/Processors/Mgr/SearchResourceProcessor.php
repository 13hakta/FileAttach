<?php
/**
 * FileAttach
 *
 * Copyright 2015-2026 by Vitaly Checkryzhev <13hakta@gmail.com>
 *
 * @package FileAttach
 */

namespace FileAttach\Processors\Mgr;

use MODX\Revolution\modContext;
use MODX\Revolution\modResource;
use MODX\Revolution\Processors\Model\GetListProcessor as MODXGetListProcessor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

/**
 * Searches for specific resources and returns them in an array.
 */
class SearchResourceProcessor extends MODXGetListProcessor {
	public $classKey = modResource::class;
	public $languageTopics = ['resource'];
	public $permission = 'search';
	public $defaultSortField = 'pagetitle';

	/** @var string $charset */
	public $charset = 'UTF-8';

	public function beforeQuery() {
		$contextKeys = $this->getContextKeys();
		if (empty($contextKeys))
			return $this->modx->lexicon('permission_denied');

		return true;
	}

	public function prepareQueryBeforeCount(xPDOQuery $c) {
		$id = $this->getProperty('id');
		$query = $this->getProperty('query');
		$templates = $this->modx->getOption('fileattach.templates');

		$where = ['context_key:IN' => $this->getContextKeys()];

		if ($templates != '')
			$where['template:IN'] = explode(',', $templates);

		if (!empty($id)) $where['id'] = $id;
		if (!empty($query)) $where['pagetitle:LIKE'] = "%$query%";

		$c->select('id,pagetitle,description');
		$c->where($where);

		return $c;
	}

	/**
	 * Get a collection of Context keys that the User can access for all the Resources
	 * @return array
	 */
	public function getContextKeys() {
		$contextKeys = [];
		$contexts = $this->modx->getCollection(modContext::class, ['key:!=' => 'mgr']);

		/** @var modContext $context */
		foreach ($contexts as $context) {
			if ($context->checkPolicy('list'))
				$contextKeys[] = $context->get('key');
		}

		return $contextKeys;
	}

	public function beforeIteration(array $list) {
		$this->charset = $this->modx->getOption('modx_charset', null, 'UTF-8');
		return $list;
	}

	public function prepareRow(xPDOObject $object) {
		$objectArray = $object->toArray();

		$objectArray['pagetitle'] = htmlentities($objectArray['pagetitle'], ENT_COMPAT, $this->charset);
		$objectArray['description'] = htmlentities($objectArray['description'], ENT_COMPAT, $this->charset);

		return [
			'id' => $objectArray['id'],
			'pagetitle' => $objectArray['pagetitle'],
			'description' => $objectArray['description'],
		];
	}
}
