<?php

if (!defined('MODX_BASE_PATH')) {
	require 'build.config.php';
}

/* define sources */
$root = dirname(dirname(__FILE__)) . '/';
$sources = array(
	'root' => $root,
	'build' => $root . '_build/',
	'source_core' => $root . 'core/components/' . PKG_NAME_LOWER,
	'src' => $root . 'core/components/' . PKG_NAME_LOWER . '/src/',
	'schema' => $root . 'core/components/' . PKG_NAME_LOWER . '/model/schema/',
	'xml' => $root . 'core/components/' . PKG_NAME_LOWER . '/model/schema/' . PKG_NAME_LOWER . '.mysql.schema.xml',
);
unset($root);

require MODX_CORE_PATH . 'model/modx/modx.class.php';
require $sources['build'] . '/includes/functions.php';

$modx = new modX();
$modx->initialize('mgr');
$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

// Make existing model classes loadable, so the generator can
// reconstruct the platform (mysql) classes instead of writing junk
$autoload = $sources['source_core'] . '/vendor/autoload.php';
if (file_exists($autoload)) {
	require_once $autoload;
}

$modx->loadClass('transport.modPackageBuilder', '', false, true);
if (!XPDO_CLI_MODE) {
	echo '<pre>';
}

/** @var xPDO\Om\xPDOManager $manager */
$manager = $modx->getManager();
/** @var xPDO\Om\xPDOGenerator $generator */
$generator = $manager->getGenerator();

// Generate PSR-4 model into src/ (existing domain classes are kept,
// maps and metadata are regenerated)
$generator->parseSchema($sources['xml'], $sources['src'], array(
	'namespacePrefix' => 'FileAttach',
	'update' => 1
));

$modx->log(modX::LOG_LEVEL_INFO, 'Model generated.');
if (!XPDO_CLI_MODE) {
	echo '</pre>';
}
