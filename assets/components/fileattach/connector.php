<?php
/** @noinspection PhpIncludeInspection */
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';

/** @var FileAttach $FileAttach */
$FileAttach = $modx->getService('fileattach', 'FileAttach', $modx->getOption('fileattach.core_path', null, $modx->getOption('core_path') . 'components/fileattach/') . 'model/fileattach/');
$modx->lexicon->load('fileattach:default');

if ($modx->user->hasSessionContext($modx->context->get('key'))) {
    $_SERVER['HTTP_MODAUTH'] = $_SESSION["modx.{$modx->context->get('key')}.user.token"];
} else {
    $_SERVER['HTTP_MODAUTH'] = 0;
}

// handle request
$corePath = $modx->getOption('fileattach.core_path', null, $modx->getOption('core_path') . 'components/fileattach/');
$path = $modx->getOption('processorsPath', $FileAttach->config, $corePath . 'processors/');
$modx->request->handleRequest(array(
	'processors_path' => $path,
	'location' => '',
));