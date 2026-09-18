<?php
/** @noinspection PhpIncludeInspection */
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('fileattach.core_path', null, $modx->getOption('core_path') . 'components/fileattach/');
$modx->lexicon->load('fileattach:default');

/** @var FileAttach\FileAttach|null $FileAttach */
$FileAttach = $modx->getService('fileattach', \FileAttach\FileAttach::class, $corePath);

$key = $modx->context->get('key');
if ($modx->user->hasSessionContext($key))
	$_SERVER['HTTP_MODAUTH'] = $_SESSION["modx.$key.user.token"];
else {
	define('MODX_REQP', false);
	$_SERVER['HTTP_MODAUTH'] = '';
}

// handle request
$path = ($FileAttach)? $FileAttach->config['processorsPath'] : $corePath . 'processors/';
$modx->request->handleRequest([
	'processors_path' => $path,
	'location' => '',
]);
