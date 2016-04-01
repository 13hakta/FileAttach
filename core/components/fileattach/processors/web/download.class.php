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
        if (empty($this->primaryKey)) return $this->modx->lexicon('fileattach.item_err_ns');

        $this->object = $this->modx->getObject($this->classKey, array($this->primaryKeyField => $this->primaryKey));
        if (empty($this->object)) return $this->modx->lexicon('fileattach.item_err_nfs', array($this->primaryKeyField => $this->primaryKey));
        return parent::initialize();
    }


    /*
     * {@inheritDoc}
     * @return redirect or bytestream
    */
    public function process() {
	// Count downloads if allowed by config
	if ($this->modx->getOption('fileattach.download', null, true)) {
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

        @session_write_close();

        // If file is private then redirect else read file directly
	if ($this->object->get('private')) {
	    header("Content-Type: application/force-download");
    	    header("Content-Disposition: attachment; filename=\"" . $this->object->get('name') . "\"");
	    readfile($this->object->getFullPath());
	} else {
	    // In private mode redirect to file url
	    $fileurl = $this->object->getUrl();
	    header("Location: $fileurl", true, 302);
	}
    }
}

return 'FileItemDownloadProcessor';