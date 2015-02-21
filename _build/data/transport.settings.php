<?php

$settings = array();

$tmp = array(
    'mediasource' => array(
	'key' => 'fileattach.mediasource',
	'name' => 'setting_fileattach.mediasource',
	'description' => 'setting_fileattach.mediasource_desc',
	'xtype' => 'modx-combo-source',
	'lexicon' => 'fileattach:setting',
	'value' => 1
    ),
    'files_path' => array(
	'key' => 'fileattach.files_path',
	'name' => 'setting_fileattach.files_path',
	'description' => 'setting_fileattach.files_path_desc',
	'xtype' => 'textfield',
	'lexicon' => 'fileattach:setting',
	'value' => ''
    ),
    'templates' => array(
	'key' => 'fileattach.templates',
	'name' => 'setting_fileattach.templates',
	'description' => 'setting_fileattach.templates_desc',
	'xtype' => 'textfield',
	'lexicon' => 'fileattach:setting',
	'value' => ''
    ),
    'user_folders' => array(
    	'key' => 'fileattach.user_folders',
	'name' => 'setting_fileattach.user_folders',
	'description' => 'setting_fileattach.user_folders_desc',
	'xtype' => 'combo-boolean',
	'lexicon' => 'fileattach:setting',
	'value' => false
    ),
    'calchash' => array(
    	'key' => 'fileattach.calchash',
	'name' => 'setting_fileattach.calchash',
	'description' => 'setting_fileattach.calchash_desc',
	'xtype' => 'combo-boolean',
	'lexicon' => 'fileattach:setting',
	'value' => false
    ),
    'put_docid' => array(
    	'key' => 'fileattach.put_docid',
	'name' => 'setting_fileattach.put_docid',
	'description' => 'setting_fileattach.put_docid_desc',
	'xtype' => 'combo-boolean',
	'lexicon' => 'fileattach:setting',
	'value' => false
    ),
    'private' => array(
    	'key' => 'fileattach.private',
	'name' => 'setting_fileattach.private',
	'description' => 'setting_fileattach.private_desc',
	'xtype' => 'combo-boolean',
	'lexicon' => 'fileattach:setting',
	'value' => false
    ),
    'download' => array(
    	'key' => 'fileattach.download',
	'name' => 'setting_fileattach.download',
	'description' => 'setting_fileattach.download_desc',
	'xtype' => 'combo-boolean',
	'lexicon' => 'fileattach:setting',
	'value' => true
    ),
);

foreach ($tmp as $k => $v) {
	/* @var modSystemSetting $setting */
	$setting = $modx->newObject('modSystemSetting');
	$setting->fromArray(array_merge(
		array(
			'key' => 'fileattach.' . $k,
			'namespace' => PKG_NAME_LOWER,
		), $v
	), '', true, true);

	$settings[] = $setting;
}

unset($tmp);
return $settings;
