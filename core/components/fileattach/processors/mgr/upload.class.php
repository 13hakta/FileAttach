<?php
/**
 * FileAttach
 *
 * Copyright 2015-2019 by Vitaly Checkryzhev <13hakta@gmail.com>
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
 * Upload files to a directory
 *
 * @param string $docid resource ID
 */
class modFileAttachUploadProcessor extends modProcessor {
	/** @var modMediaSource $source */
	private $source;
	private $privatemode;
	private $filename;
	public $path;
	private $localpath;

	private $calc_hash;
	private $user_folders;
	private $doc_folders;

	public function checkPermissions() {
		return $this->modx->hasPermission('file_upload');
	}

	public function getLanguageTopics() {
		return array('file');
	}

	public function initialize() {
		$this->calc_hash = $this->modx->getOption('fileattach.calchash');
		$this->user_folders = $this->modx->getOption('fileattach.user_folders');
		$this->doc_folders = $this->modx->getOption('fileattach.put_docid');
		$this->path = $this->modx->getOption('fileattach.files_path');
		$this->privatemode = $this->modx->getOption('fileattach.private');
		$this->translit = $this->modx->getOption('fileattach.translit');

		$this->setDefaultProperties(array('docid' => 0));

		$this->localpath = '';

		if ($this->user_folders)
			$this->localpath .= (int) $this->modx->user->get('id') . '/';

		if ($this->doc_folders)
			$this->localpath .=  (int) $this->getProperty('docid') . '/';

		$this->setProperty('path', $this->path . $this->localpath);
		$this->setProperty('source', $this->modx->getOption('fileattach.mediasource'));

		if (!$this->getProperty('path')) return $this->modx->lexicon('file_folder_err_ns');

		return true;
	}

	public function process() {
		if (!$this->getSource())
			return $this->failure($this->modx->lexicon('permission_denied'));

		$this->source->setRequestProperties($this->getProperties());
		$this->source->initialize();

		if (!$this->source->checkPolicy('create'))
			return $this->failure($this->modx->lexicon('permission_denied'));

		// Create subfolder
		if ($this->user_folders || $this->doc_folders) {
			$path = $this->source->getBasePath() . $this->getProperty('path');
			$d = $this->source->fileHandler->make($path, array(), 'modDirectory');

			if (!$d->exists()) {
				if (!$this->source->createContainer($this->getProperty('path'), ''))
					return $this->failure($this->modx->lexicon('permission_denied'));
			}
		}

		$path = $this->source->getBasePath() . $this->getProperty('path');
		$list = array();

		$this->modx->loadClass('FileItem');

		// Create serie of FileItem objects
		foreach ($_FILES as $file) {
			$filename = $file['name'];
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			$ext = strtolower($ext);

			if ($this->translit)
				$filename = modResource::filterPathSegment($this->modx, $filename);

			// Generate name and check for existence
			if ($this->privatemode)
				$this->filename = FileItem::generateName() . ".$ext";
			else
				$this->filename = $filename;

			$fullpath = '';

			while(1) {
				$fullpath = $path . '/' . $this->filename;
				$f = $this->source->fileHandler->make($fullpath, array(), 'modFile');
				if (!$f->exists()) break;

				// Generate new name again
				if ($this->privatemode)
					$this->filename = FileItem::generateName() . ".$ext";
				else
					$this->filename = '_' . $this->filename;
			}

			$success = $this->source->uploadObjectsToContainer(
				$this->getProperty('path'),
				array(array( // emulate a $_FILES object
					"name" => $this->filename,
					"tmp_name" => $file['tmp_name'],
					"error" => "0")
				));

			if (empty($success)) {
				$msg = '';
				$errors = $this->source->getErrors();
				foreach ($errors as $k => $msg) {
					$this->modx->error->addField($k,$msg);
				}

				return $this->failure($msg);
			} else {
				$fid = FileItem::generateName();
				$fileitem = $this->modx->newObject('FileItem', array(
					'fid' => $fid,
					'docid' => $this->getProperty('docid'),
					'name' => $filename,
					'internal_name' => $this->filename,
					'path' => $this->localpath,
					'private' => $this->privatemode,
					'uid' => $this->modx->user->get('id'),
					'hash' => ($this->calc_hash)? sha1_file($fullpath) : NULL
					));

				if (!$fileitem->save())
					return $this->failure($this->modx->lexicon('fileattach.item_err_save'));

				$list[] = array(
					'id' => $fileitem->get('id'),
					'fid' => $fid,
					'name' => $filename);
			}
		}

		return $this->outputArray($list, count($list));
	}

	/**
	 * Get the active Source
	 * @return modMediaSource|boolean
	 */
	public function getSource() {
		if (empty($this->source)) {
			$this->modx->loadClass('sources.modMediaSource');
			$this->source = modMediaSource::getDefaultSource($this->modx,$this->getProperty('source'));
		}

		if (empty($this->source) || !$this->source->getWorkingContext())
			return false;

		return $this->source;
	}
}

return 'modFileAttachUploadProcessor';