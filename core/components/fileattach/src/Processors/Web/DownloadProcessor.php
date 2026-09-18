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
 * Download file
 */
class DownloadProcessor extends ModelProcessor {
	public $objectType = FileItem::class;
	public $classKey = FileItem::class;
	public $languageTopics = ['fileattach:default'];
	public $permission = 'fileattach.download';

	/** @var string $primaryKeyField */
	public $primaryKeyField = 'fid';

	/** @var string|false $primaryKey */
	private $primaryKey;


	/**
	 * {@inheritDoc}
	 * @return boolean|string
	 */
	public function initialize() {
		$this->primaryKey = $this->getProperty($this->primaryKeyField, false);
		if (empty($this->primaryKey))
			return $this->modx->lexicon('fileattach.item_err_ns');

		$this->object = $this->modx->getObject($this->classKey, [$this->primaryKeyField => $this->primaryKey]);
		if (empty($this->object))
			return $this->modx->lexicon('fileattach.item_err_nfs', [$this->primaryKeyField => $this->primaryKey]);

		return true;
	}


	/**
	 * {@inheritDoc}
	 * @return redirect or bytestream
	 */
	public function process() {
		@session_write_close();

		$perform_count = true;
		$range = '';
		$start = 0;

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
			$inline = (int) $this->getProperty('inline');
			if (!$inline) {
				header('Content-Type: application/force-download');
				header('Content-Disposition: attachment; filename="' . $this->object->get('name') . '"');
				$perform_count = false;
			}

			header('Last-Modified: ' . gmdate('r', $mtime));
			header('ETag: ' . sprintf('%x-%x-%x', fileinode($filename), $filesize, $mtime));
			header('Accept-Ranges: bytes');
			header('Content-Length: ' . $remain);
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
			$c->set([
				'download' => $this->object->get('download') + 1
			]);
			$c->where([
				'fid' => $this->primaryKey,
			]);
			$c->prepare();
			$c->stmt->execute();
		}
	}
}
