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

class FileItemDownloadProcessor extends modObjectGetProcessor {
    public $objectType = 'FileItem';
    public $classKey = 'FileItem';
    public $languageTopics = array('fileattach:default');
    public $permission = 'fileattach.download';

    public function cleanup() {
        @session_write_close();

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

    public function beforeOutput() {
	// Count downloads if allowed by config
	if ($this->modx->getOption('fileattach.download', null, true)) {
	    $this->object->set('download', $this->object->get('download') + 1);
	    $this->object->save();
	}
    }
}

return 'FileItemDownloadProcessor';