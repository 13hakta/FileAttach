<?php
/**
 * FileAttach
 *
 * Copyright 2015-2026 by Vitaly Checkryzhev <13hakta@gmail.com>
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

namespace FileAttach;

use MODX\Revolution\modX;

/**
 * The base class for FileAttach.
 */
class FileAttach {
	/** @var modX $modx */
	public modX $modx;

	/** @var array $config */
	public array $config = [];


	/**
	 * @param modX $modx
	 * @param array $config
	 */
	public function __construct(modX $modx, array $config = []) {
		$this->modx = $modx;

		$corePath = $this->modx->getOption('fileattach.core_path', $config, $this->modx->getOption('core_path') . 'components/fileattach/');
		$assetsUrl = $this->modx->getOption('fileattach.assets_url', $config, $this->modx->getOption('assets_url') . 'components/fileattach/');
		$connectorUrl = $assetsUrl . 'connector.php';

		$this->config = array_merge([
			'assetsUrl' => $assetsUrl,
			'jsUrl' => $assetsUrl . 'js/',
			'connectorUrl' => $connectorUrl,

			'corePath' => $corePath,
			'modelPath' => $corePath . 'src/',
			'chunksPath' => $corePath . 'elements/chunks/',
			'templatesPath' => $corePath . 'elements/templates/',
			'chunkSuffix' => '.chunk.tpl',
			'snippetsPath' => $corePath . 'elements/snippets/',
			'processorsPath' => $corePath . 'processors/'
		], $config);

		$this->modx->addPackage('FileAttach\Model', $this->config['modelPath'], null, 'FileAttach');
		$this->modx->lexicon->load('fileattach:default');
	}
}
