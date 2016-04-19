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
 * Searches for specific resources and returns them in an array.
 *
 * @param integer $start The page to start on
 * @param integer $limit (optional) The number of results to limit by
 * @param string $sort The column to sort by
 * @param string $dir The direction to sort
 * @return array An array of modResources
 *
 * @package modx
 * @subpackage processors.resource
 */
class modFileItemSearchResourceProcessor extends modObjectGetListProcessor {
	public $classKey = 'modResource';
	public $languageTopics = array('resource');
	public $permission = 'search';
	public $defaultSortField = 'pagetitle';

	/** @var array $contextKeys */
	public $contextKeys = array();
	/** @var array $actions */
	public $actions = array();
	/** @var string $charset */
	public $charset = 'UTF-8';

	public function beforeQuery() {
		$this->contextKeys = $this->getContextKeys();
		if (empty($this->contextKeys))
			return $this->modx->lexicon('permission_denied');

		return true;
	}

	public function prepareQueryBeforeCount(xPDOQuery $c) {
		$id = $this->getProperty('id');
		$query = $this->getProperty('query');
		$templates = $this->modx->getOption('fileattach.templates');

		$where = array('context_key:IN' => $this->contextKeys);

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
		$contextKeys = array();
		$contexts = $this->modx->getCollection('modContext', array('key:!=' => 'mgr'));

		/** @var modContext $context */
		foreach ($contexts as $context) {
			if ($context->checkPolicy('list'))
				$contextKeys[] = $context->get('key');
		}

		return $contextKeys;
	}

	public function beforeIteration(array $list) {
		$this->charset = $this->modx->getOption('modx_charset',null,'UTF-8');
		return $list;
	}

	public function prepareRow(xPDOObject $object) {
		$objectArray = $object->toArray();

		$objectArray['pagetitle'] = htmlentities($objectArray['pagetitle'],ENT_COMPAT,$this->charset);
		$objectArray['description'] = htmlentities($objectArray['description'],ENT_COMPAT,$this->charset);

		return array(
			'id' => $objectArray['id'],
			'pagetitle' => $objectArray['pagetitle'],
			'description' => $objectArray['description'],
			);
	}
}

return 'modFileItemSearchResourceProcessor';