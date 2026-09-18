<?php
/**
 * FileAttach bootstrap. Included by MODX on every request
 * when the fileattach namespace is registered.
 *
 * Registers composer autoloader (if present) and adds the
 * PSR-4 model package to xPDO.
 */

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/** @var \MODX\Revolution\modX|null $modx */
if (isset($modx) && $modx instanceof \MODX\Revolution\modX) {
    $modx->addPackage('FileAttach\Model', __DIR__ . '/src/', null, 'FileAttach');
}
