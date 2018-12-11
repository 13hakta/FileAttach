<?php

$menus = array();

$tmp = array(
	'fileattach' => array(
		'description' => 'fileattach.menu_desc',
		'permissions' => 'fileattach.totallist',
		'action' => 'list'
	),
);

$i = 0;
foreach ($tmp as $k => $v) {
	/* @var modMenu $menu */
	$menu = $modx->newObject('modMenu');
	$menu->fromArray(array_merge(
		array(
			'namespace' => PKG_NAME_LOWER,
			'text' => $k,
			'parent' => 'components',
			'menuindex' => $i,
			'params' => '',
			'handler' => '',
		), $v
	), '', true, true);

	$menus[] = $menu;
	$i++;
}

unset($action, $menu, $i);
return $menus;