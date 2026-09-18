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
use MODX\Revolution\Processors\Processor;
use MODX\Revolution\Sources\modMediaSource;

/**
 * Upload files to a directory
 *
 * @param string $docid resource ID
 */
class UploadProcessor extends Processor {
	/** @var modMediaSource|false $source */
	private $source = false;

	/** @var bool $privatemode */
	private $privatemode;

	/** @var string $filename */
	private $filename;

	/** @var string $path */
	public $path = '';

	/** @var string $localpath */
	private $localpath;

	/** @var bool $calc_hash */
	private $calc_hash;

	/** @var bool $user_folders */
	private $user_folders;

	/** @var bool $doc_folders */
	private $doc_folders;

	/** @var bool $translit */
	private $translit;

	/** @var bool $replaceable */
	private $replaceable;

	public function checkPermissions() {
		return $this->modx->hasPermission('file_upload');
	}

	public function getLanguageTopics() {
		return ['file'];
	}

	public function initialize() {
		$this->calc_hash = $this->modx->getOption('fileattach.calchash');
		$this->user_folders = $this->modx->getOption('fileattach.user_folders');
		$this->doc_folders = $this->modx->getOption('fileattach.put_docid');
		$this->path = $this->modx->getOption('fileattach.files_path');
		$this->privatemode = $this->modx->getOption('fileattach.private');
		$this->translit = $this->modx->getOption('fileattach.translit');
		$this->replaceable = $this->modx->getOption('fileattach.replaceable');

		$this->setDefaultProperties(['docid' => 0]);

		$this->localpath = '';

		if ($this->user_folders)
			$this->localpath .= (int) $this->modx->user->get('id') . '/';

		if ($this->doc_folders)
			$this->localpath .= (int) $this->getProperty('docid') . '/';

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
			$meta = $this->source->getMetaData($this->getProperty('path'));

			if (($meta === false) || (isset($meta['type']) && ($meta['type'] !== 'dir'))) {
				if (!$this->source->createContainer($this->getProperty('path'), ''))
					return $this->failure($this->modx->lexicon('permission_denied'));
			}
		}

		$list = [];

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

			// MODX 3 may silently rename files on upload (upload_translit),
			// so apply the same transformation to keep DB in sync with the filesystem
			$this->filename = $this->prepareName($this->filename);

			// Resolve name collisions with a growing numeric suffix: upload_translit
			// strips leading separators, so a "_name" prefix can collapse back to
			// the existing name and loop forever
			$namenoext = pathinfo($filename, PATHINFO_FILENAME);
			$attempt = 0;

			while (true) {
				$meta = $this->source->getMetaData($this->getProperty('path') . $this->filename);
				if ($meta === false) break;

				$attempt++;

				// Generate new name again
				if ($this->privatemode)
					$this->filename = $this->prepareName(FileItem::generateName() . ".$ext");
				else
					$this->filename = $this->prepareName($namenoext . '-' . $attempt . (($ext !== '')? ".$ext" : ''));
			}

			$success = $this->source->uploadObjectsToContainer(
				$this->getProperty('path'),
				[[
					"name" => $this->filename,
					"tmp_name" => $file['tmp_name'],
					"error" => "0"
				]]);

			if (empty($success)) {
				$msg = '';
				$errors = $this->source->getErrors();
				foreach ($errors as $k => $msg) {
					$this->modx->error->addField($k, $msg);
				}

				return $this->failure($msg);
			} else {
				$fullpath = $this->source->getBasePath() . $this->getProperty('path') . $this->filename;

				$fid = FileItem::generateName();

				if ($this->replaceable) {
					// Find an existing item with the same name to replace
					/** @var FileItem|null $fileitem */
					$fileitem = $this->modx->getObject(FileItem::class, [
						'docid' => $this->getProperty('docid'),
						'name' => $filename
					]);

					if ($fileitem) {
						// Remove the previous file and apply new parameters
						$fileitem->removeFile();

						$fileitem->set('internal_name', $this->filename);
						$fileitem->set('path', $this->localpath);
						$fileitem->set('private', $this->privatemode);
						$fileitem->set('hash', ($this->calc_hash)? sha1_file($fullpath) : NULL);
					} else {
						$fileitem = $this->modx->newObject(FileItem::class, [
							'fid' => $fid,
							'docid' => $this->getProperty('docid'),
							'name' => $filename,
							'internal_name' => $this->filename,
							'path' => $this->localpath,
							'private' => $this->privatemode,
							'uid' => $this->modx->user->get('id'),
							'hash' => ($this->calc_hash)? sha1_file($fullpath) : NULL
						]);
					}
				} else {
					$fileitem = $this->modx->newObject(FileItem::class, [
						'fid' => $fid,
						'docid' => $this->getProperty('docid'),
						'name' => $filename,
						'internal_name' => $this->filename,
						'path' => $this->localpath,
						'private' => $this->privatemode,
						'uid' => $this->modx->user->get('id'),
						'hash' => ($this->calc_hash)? sha1_file($fullpath) : NULL
					]);
				}

				if (!$fileitem->save())
					return $this->failure($this->modx->lexicon('fileattach.item_err_save'));

				$list[] = [
					'id' => $fileitem->get('id'),
					'fid' => $fileitem->get('fid'),
					'name' => $filename];

				$this->modx->invokeEvent('faOnUploadItem', [
					'id' => $this->getProperty('docid'),
					'object' => &$fileitem
				]);
			}
		}

		$this->modx->invokeEvent('faOnUpload', ['id' => $this->getProperty('docid')]);

		return $this->outputArray($list, count($list));
	}

	/**
	 * Apply core translit rules (upload_translit) to the filename,
	 * mirroring modMediaSource::uploadObjectsToContainer behavior,
	 * so the stored internal name matches the name on the filesystem.
	 *
	 * @param string $filename
	 * @return string
	 */
	private function prepareName(string $filename): string {
		if (!(bool) $this->modx->getOption('upload_translit'))
			return $filename;

		$uploadRegex = $this->modx->getOption('upload_translit_restrict_chars_pattern');
		$options = !empty($uploadRegex)
			? [
				'friendly_alias_restrict_chars' => 'pattern',
				'friendly_alias_restrict_chars_pattern' => $uploadRegex
			]
			: [];

		return $this->modx->filterPathSegment($filename, $options);
	}

	/**
	 * Get the active Source
	 * @return modMediaSource|boolean
	 */
	public function getSource() {
		if (empty($this->source)) {
			$this->source = modMediaSource::getDefaultSource($this->modx, $this->getProperty('source'));
		}

		if (empty($this->source) || !$this->source->getWorkingContext())
			return false;

		return $this->source;
	}
}
