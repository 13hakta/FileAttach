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

class FileAttachMediaSource extends modMediaSource implements modMediaSourceInterface {
	/** @var FileAttach $fileattach */
	public $fileattach;

	/** @var string $files_path */
	private $files_path;

	/** @var int $parentSourceID */
	private $parentSourceID;


	/**
	 * Initialize the source, preparing it for usage.
	 *
	 * @return boolean
	 */
	public function initialize() {
		$this->fileattach = $this->xpdo->getService('fileattach', 'FileAttach', $this->xpdo->getOption('fileattach.core_path', null, $this->xpdo->getOption('core_path') . 'components/fileattach/') . 'model/fileattach/');
		if (!($this->fileattach instanceof FileAttach))
			return false;

		$this->parentSourceID = $this->xpdo->getOption('fileattach.mediasource', null, 1);
		$this->files_path = $this->xpdo->getOption('fileattach.files_path');

		$this->xpdo->lexicon->load('fileattach:default', 'fileattach:source');

		return true;
	}


	/**
	 * Return an array of containers at this current level in the container structure. Used for the tree
	 * navigation on the files tree.
	 *
	 * @param string $path
	 * @return array
	 */
	public function getContainerList($path) {
		$properties = $this->getPropertyList();
		$list = array();

		if ($path == '/') {
			$c = $this->xpdo->newQuery('modResource');

			$c->select('modResource.id,modResource.pagetitle');
			$c->rightJoin('FileItem', 'FileItem', 'modResource.id=FileItem.docid'); 
			$c->sortby('modResource.pagetitle', 'ASC');
			$c->groupby('modResource.id');

			$resources = $this->xpdo->getCollection('modResource', $c);
			/** @var modResource $resource */
			foreach ($resources as $resource) {
				$list[] = array(
					'id' => $resource->get('id'),
					'text' => $resource->get('pagetitle') . ' (' . $resource->get('id') . ')',
					'iconCls' => 'icon icon-folder',
					'leaf' => false
				);
			}

			return $list;
		} else {
			$id = (int)$path;

			/* get items */
			$c = $this->xpdo->newQuery('FileItem');
			$c->sortby('name', 'ASC');
			$c->where(array('docid' => $id));

			$items = $this->xpdo->getCollection('FileItem', $c);

			$t_description = $this->xpdo->lexicon('description');
			$t_download = $this->xpdo->lexicon('fileattach.downloads');
			$t_size = $this->xpdo->lexicon('fileattach.size');
			$t_hash = $this->xpdo->lexicon('fileattach.hash');

			/** @var FileItem $item */
			foreach ($items as $item) {
				$ext = strtolower(pathinfo($item->get('internal_name'), PATHINFO_EXTENSION));

				$tip = $t_description . ': ' . $item->get('description') . '<br/>' .
				$t_download . ': ' . $item->get('download') . '<br/>' .
				$t_size . ': ' . $item->getSize() . '<br/>' .
				$t_hash . ': ' . $item->get('hash');

				$list[] = array(
					'id' => $item->get('id'),
					'text' => $item->get('name'),
					'iconCls' => 'icon icon-file icon-' . $ext . (($item->get('private'))? ' icon-access' : ''),
					'qtip' => $tip,
					'leaf' => true
				);
			}

			return $list;
		}
	}


	/**
	 * Return a detailed list of objects in a specific path. Used for thumbnails in the Browser.
	 *
	 * @param string $path
	 * @return array
	 */
	public function getObjectsInContainer($path) {
		// Initialize config
		$properties = $this->getPropertyList();
		$list = array();

		$modAuth = $this->xpdo->user->getUserToken($this->xpdo->context->get('key'));

		$thumbnailType = $this->getOption('thumbnailType', $properties, 'png');
		$thumbnailQuality = $this->getOption('thumbnailQuality', $properties, 90);
		$thumbWidth = $this->xpdo->context->getOption('filemanager_thumb_width', 100);
		$thumbHeight = $this->xpdo->context->getOption('filemanager_thumb_height', 80);
		$imageWidth = $this->ctx->getOption('filemanager_image_width', 800);
		$imageHeight = $this->ctx->getOption('filemanager_image_height', 600);

		$thumb_default = $this->xpdo->context->getOption('manager_url', MODX_MANAGER_URL) . 'templates/default/images/restyle/nopreview.jpg';
		$thumbUrl = $this->xpdo->context->getOption('connectors_url', MODX_CONNECTORS_URL) . 'system/phpthumb.php?';

		$imagesExts = $this->getOption('imageExtensions', $properties, 'jpg,jpeg,png,gif');
		$imagesExts = explode(',', $imagesExts);

		if ($path != '/') {
			$thumb = $this->ctx->getOption('manager_url', MODX_MANAGER_URL).'templates/default/images/restyle/nopreview.jpg';

			$id = (int)$path;

			/* get items */
			$c = $this->xpdo->newQuery('FileItem');
			$c->sortby('name', 'ASC');
			$c->where(array('docid' => $id));

			$items = $this->xpdo->getCollection('FileItem', $c);

			/** @var FileItem $item */
			foreach ($items as $item) {
				$ext = strtolower(pathinfo($item->get('internal_name'), PATHINFO_EXTENSION));

				$listItem = array(
					'id' => $item->get('id'),
					'name' => $item->get('name'),
					'ext' => $ext,
					'type' => 'file',
					'size' => $item->getSize(),
					'thumb' => $thumb,
					'leaf' => true,
					'perms' => '',
					'thumb_width' => $thumbWidth,
					'thumb_height' => $thumbHeight,
					'disabled' => false
				);

				if (in_array($ext, $imagesExts)) {
					/* get thumbnail */
					$preview = 1;

					/* generate thumb/image URLs */
					$thumbQuery = http_build_query(array(
						'src' => $item->getPath(),
						'w' => $thumbWidth,
						'h' => $thumbHeight,
						'f' => $thumbnailType,
						'q' => $thumbnailQuality,
						'far' => 'C',
						'HTTP_MODAUTH' => $modAuth,
						'wctx' => $this->xpdo->context->get('key'),
						'source' => $this->parentSourceID
					));

					$imageQuery = http_build_query(array(
						'src' => $item->getPath(),
						'w' => $imageWidth,
						'h' => $imageHeight,
						'f' => $thumbnailType,
						'q' => $thumbnailQuality,
						'far' => 'C',
						'HTTP_MODAUTH' => $modAuth,
						'wctx' => $this->xpdo->context->get('key'),
						'source' => $this->parentSourceID
					));

					$thumb = $thumbUrl . urldecode($thumbQuery);

					$listItem['image'] = $thumbUrl . urldecode($imageQuery);
				} else {
					$preview = 0;
					$thumb = $thumb_default;
					$listItem['image'] = $thumb;
				}

				$listItem['thumb']   = $thumb;
				$listItem['preview'] = $preview;

				$list[] = $listItem;
			}
		}

		return $list;
	}


	/**
	 * Get the default properties for the filesystem media source type.
	 *
	 * @return array
	 */
	public function getDefaultProperties() {
		return array(
			'imageExtensions' => array(
				'name' => 'imageExtensions',
				'desc' => 'prop_file.imageExtensions_desc',
				'type' => 'textfield',
				'value' => 'jpg,jpeg,png,gif',
				'lexicon' => 'core:source',
			),
			'thumbnailType' => array(
				'name' => 'thumbnailType',
				'desc' => 'prop_file.thumbnailType_desc',
				'type' => 'list',
				'options' => array(
					array('name' => 'PNG','value' => 'png'),
					array('name' => 'JPG','value' => 'jpg'),
					array('name' => 'GIF','value' => 'gif'),
				),
				'value' => 'png',
				'lexicon' => 'core:source',
			),
			'thumbnailQuality' => array(
				'name' => 'thumbnailQuality',
				'desc' => 'prop_file.thumbnailQuality_desc',
				'type' => 'textfield',
				'options' => '',
				'value' => 90,
				'lexicon' => 'core:source',
			)
		);
	}


	/**
	 * Prepare the source path for phpThumb
	 *
	 * @param string $src
	 * @return string
	 */
	public function prepareSrcForThumb($value) {
		return $this->getObjectUrl($value);
	}


	/**
	 * Get the name of this source type
	 * @return string
	 */
	public function getTypeName() {
		$this->xpdo->lexicon->load('fileattach:source');
		return $this->xpdo->lexicon('fileattach.source_name');
	}


	/**
	 * Get the description of this source type
	 * @return string
	 */
	public function getTypeDescription() {
		$this->xpdo->lexicon->load('fileattach:source');
		return $this->xpdo->lexicon('fileattach.source_desc');
	}
}