<?php

$properties = array();

$tmp = array(
	'fid' => array(
		'type' => 'numberfield',
		'value' => 0,
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