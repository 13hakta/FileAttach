<?php
/**
 * FileAttach
 *
 * Copyright 2015-2017 by Vitaly Checkryzhev <13hakta@gmail.com>
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

class FileItemDownloadProcessor extends modObjectProcessor {
	public $objectType = 'FileItem';
	public $classKey = 'FileItem';
	public $primaryKeyField = 'fid';
	public $languageTopics = array('fileattach:default');
	public $permission = 'fileattach.download';
	private $primaryKey;

	/**
	 * {@inheritDoc}
	 * @return boolean
	 */
	public function initialize() {
		$this->primaryKey = $this->getProperty($this->primaryKeyField, false);
		if (empty($this->primaryKey))
			return $this->modx->lexicon('fileattach.item_err_ns');

		$this->object = $this->modx->getObject($this->classKey, array($this->primaryKeyField => $this->primaryKey));
		if (empty($this->object))
			return $this->modx->lexicon('fileattach.item_err_nfs', array($this->primaryKeyField => $this->primaryKey));

		return parent::initialize();
	}


	/*
	 * {@inheritDoc}
	 * @return redirect or bytestream
	*/
	public function process() {
		@session_write_close();

		$perform_count = true;

		// If file is private then redirect else read file directly
		if ($this->object->get('private')) {
			// Get file info
			$filename = $this->object->getFullPath();
			$filesize = filesize($filename);
			$mtime = filemtime($filename);

			if (isset($_SERVER['HTTP_RANGE'])) {
				// Get range
				$range = str_replace('bytes=', '', $_SERVER['HTTP_RANGE']);
				list($start, $end) = explode('-', $range);

				// Check data
				if (empty($start)) {
					header($_SERVER['SERVER_PROTOCOL'] . ' 416 Requested Range Not Satisfiable');
					return;
				} else
					$perform_count = false;

				// Check range
				$start = intval($start);
				$end = intval($end);

				if (($end == 0) || ($end < $start) || ($end >= $filesize)) $end = $filesize - 1;

				$remain = $end - $start;

				if ($remain == 0) {
					header($_SERVER['SERVER_PROTOCOL'] . ' 416 Requested Range Not Satisfiable');
					return;
				}

				header($_SERVER['SERVER_PROTOCOL'] . ' 206 Partial Content');
				header("Content-Range: bytes $start-$end/$filesize");
			} else {
				$remain = $filesize;
			}

			// Put headers
			header('Last-Modified: ' . gmdate('r', $mtime));
			header('ETag: ' . sprintf('%x-%x-%x', fileinode($filename), $filesize, $mtime));
			header('Accept-Ranges: bytes');
			header('Content-Type: application/force-download');
			header('Content-Length: ' . $remain);
			header('Content-Disposition: attachment; filename="' . $this->object->get('name') . '"');
			header('Connection: close');

			if ($range) {
				$fh = fopen($filename, 'rb');
				fseek($fh, $start);

				// Output contents
				$blocksize = 8192;

				while (!feof($fh) && ($remain > 0)) {
					echo fread($fh, ($remain > $blocksize)? $blocksize : $remain);
					flush();

					$remain -= $blocksize;
				}

				fclose($fh);
			} else {
				readfile($filename);
			}
		} else {
			// In public mode redirect to file url
			$fileurl = $this->object->getUrl();
			header("Location: $fileurl", true, 302);
		}

		// Count downloads if allowed by config
		if ($perform_count && $this->modx->getOption('fileattach.download', null, true)) {
			$c = $this->modx->newQuery($this->classKey);
			$c->command('update');
			$c->set(array(
				'download' => $this->object->get('download') + 1
			));
			$c->where(array(
				'fid' => $this->primaryKey,
			));
			$c->prepare();
			$c->stmt->execute();
		}
	}
}

return 'FileItemDownloadProcessor';