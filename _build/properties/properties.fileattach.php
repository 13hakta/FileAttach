<?php

$properties = array();

$tmp = array(
	'tpl' => array(
		'type' => 'textfield',
		'value' => 'FileAttachTpl',
	),
	'sortBy' => array(
		'type' => 'textfield',
		'value' => 'name',
	),
	'sortDir' => array(
		'type' => 'list',
		'options' => array(
			array('text' => 'ASC', 'value' => 'ASC'),
			array('text' => 'DESC', 'value' => 'DESC'),
		),
		'value' => 'ASC'
	),
	'limit' => array(
		'type' => 'numberfield',
		'value' => 0,
	),
	'outputSeparator' => array(
		'type' => 'textfield',
		'value' => "\n",
	),
	'toPlaceholder' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),
	'resource' => array(
		'type' => 'numberfield',
		'value' => 0,
	),
	'makeUrl' => array(
		'type' => 'combo-boolean',
		'value' => true,
	),
	'privateUrl' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),
	'showHASH' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),
	'showSize' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),
	'showExt' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),
	'groups' => array(
		'type' => 'textfield',
		'value' => '',
	),
);

foreach ($tmp as $k => $v) {
	$properties[] = array_merge(
		array(
			'name' => $k,
			'desc' => PKG_NAME_LOWER . '.prop_' . $k,
			'lexicon' => PKG_NAME_LOWER . ':properties',
		), $v
	);
}

return $properties;