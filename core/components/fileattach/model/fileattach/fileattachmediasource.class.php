<?php
/**
 * FileAttach
 *
 * Copyright 2015 by Vitaly Checkryzhev <13hakta@gmail.com>
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
    /** @var Gallery $fileattach */
    public $fileattach;
    /**
     * Initialize the source, preparing it for usage.
     *
     * @return boolean
     */
    public function initialize() {
	$this->fileattach = $this->xpdo->getService('fileattach', 'FileAttach', $this->xpdo->getOption('fileattach.core_path', null, $this->xpdo->getOption('core_path') . 'components/fileattach/') . 'model/fileattach/');
        if (!($this->fileattach instanceof FileAttach)) return false;
        $this->xpdo->lexicon->load('fileattach:default','fileattach:source');
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
        $c = $this->xpdo->newQuery('FileItem');

	$c->select('FileItem.id,FileItem.docid,modResource.pagetitle');
	$c->leftJoin('modResource','modResource', 'modResource.id=FileItem.docid'); 
        $c->sortby('modResource.pagetitle', 'ASC');
	$c->groupby('docid');

        $resources = $this->xpdo->getCollection('FileItem', $c);
            /** @var modResource $resource */
            foreach ($resources as $resource) {
                $list[] = array(
                    'id' => $resource->get('docid'),
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
            $c->sortby('name','ASC');
            $c->where(array('docid' => $id));

            $items = $this->xpdo->getCollection('FileItem',$c);

	    $t_description = $this->xpdo->lexicon('description');
	    $t_download = $this->xpdo->lexicon('fileattach.downloads');
	    $t_size = $this->xpdo->lexicon('size');
	    $t_hash = $this->xpdo->lexicon('fileattach.hash');

            /** @var galItem $item */
            foreach ($items as $item) {
		$tip = $t_description . ': ' . $item->get('description') . '<br/>' .
		    $t_download . ': ' . $item->get('download') . '<br/>' .
		    $t_size . ': ' . $item->getSize() . '<br/>' .
		    $t_hash . ': ' . $item->get('hash');

                $list[] = array(
                    'id' => $item->get('id'),
                    'text' => $item->get('name'),
		    'iconCls' => 'icon icon-file' . (($item->get('private'))? ' icon-access' : ''),
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
        $properties = $this->getPropertyList();
        $list = array();

	if ($path == '/') {
	    $thumb = $this->ctx->getOption('manager_url', MODX_MANAGER_URL).'templates/default/images/tree/folder.gif';
    	    $c = $this->xpdo->newQuery('FileItem');

	    $c->select('FileItem.id,FileItem.docid,modResource.pagetitle');
	    $c->leftJoin('modResource','modResource', 'modResource.id=FileItem.docid'); 
    	    $c->sortby('modResource.pagetitle', 'ASC');
	    $c->groupby('docid');

    	    $resources = $this->xpdo->getCollection('FileItem', $c);
            /** @var modResource $resource */
            foreach ($resources as $resource) {
                $list[] = array(
                    'id' => $resource->get('docid'),
                    'name' => $resource->get('pagetitle') . ' (' . $resource->get('id') . ')',
		    'thumb' => $thumb,
                    'leaf' => false
                );
	    }

	    return $list;
	} else {
	    $thumb = $this->ctx->getOption('manager_url', MODX_MANAGER_URL).'templates/default/images/restyle/nopreview.jpg';

            $id = (int)$path;

            /* get items */
            $c = $this->xpdo->newQuery('FileItem');
            $c->sortby('name','ASC');
            $c->where(array('docid' => $id));

            $items = $this->xpdo->getCollection('FileItem',$c);

            /** @var galItem $item */
            foreach ($items as $item) {
                $list[] = array(
                    'id' => $item->get('id'),
                    'name' => $item->get('name'),
		    'thumb' => $thumb,
                    'leaf' => true
                );
            }
        }

        return $list;
    }

    public function getTypeName() {
        $this->xpdo->lexicon->load('fileattach:source');
        return $this->xpdo->lexicon('fileattach.source_name');
    }

    public function getTypeDescription() {
        $this->xpdo->lexicon->load('fileattach:source');
        return $this->xpdo->lexicon('fileattach.source_desc');
    }
}