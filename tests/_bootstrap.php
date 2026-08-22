<?php

use OpenDxp\Tests\Support\Util\Autoloader;


if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    include __DIR__ . '/../vendor/autoload.php';
    $opendxpTestDir = __DIR__ . '/../vendor/open-dxp/opendxp/tests';
} elseif (file_exists(__DIR__ . '/../../../../vendor/autoload.php')) {
    include __DIR__ . '/../../../../vendor/autoload.php';
    $opendxpTestDir = __DIR__ . '/../../../../vendor/open-dxp/opendxp/tests';
} elseif (getenv('OPENDXP_PROJECT_ROOT') != '' && file_exists(getenv('OPENDXP_PROJECT_ROOT') . '/vendor/autoload.php')) {
    include getenv('OPENDXP_PROJECT_ROOT') . '/vendor/autoload.php';
    $opendxpTestDir = getenv('OPENDXP_PROJECT_ROOT') . '/vendor/open-dxp/opendxp/tests';
} elseif (getenv('OPENDXP_PROJECT_ROOT') != '') {
    throw new \Exception('Invalid OpenDxp project root "' . getenv('OPENDXP_PROJECT_ROOT') . '"');
} else {
    throw new \Exception('Unknown configuration! OpenDxp project root not found, please set env variable OPENDXP_PROJECT_ROOT.');
}

$opendxpTestsSupportDir = $opendxpTestDir . '/Support';

//OpenDxp 10 BC layer
if (!is_dir($opendxpTestsSupportDir)) {
    $opendxpTestsSupportDir = $opendxpTestDir . '/_support';
}

include $opendxpTestsSupportDir . '/Util/Autoloader.php';

\OpenDxp\Bootstrap::setProjectRoot();
\OpenDxp\Bootstrap::bootstrap();

//error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING);

Autoloader::addNamespace('OpenDxp\Tests', $opendxpTestsSupportDir); //OpenDxp 10 BC layer
Autoloader::addNamespace('OpenDxp\Tests\Support', $opendxpTestsSupportDir);
Autoloader::addNamespace('OpenDxp\Model\DataObject', OPENDXP_CLASS_DIRECTORY . '/DataObject');
Autoloader::addNamespace('OpenDxp\Bundle\PerspectiveEditor\Tests', __DIR__);
Autoloader::addNamespace('OpenDxp\Bundle\PerspectiveEditor\Tests', __DIR__ . '/_support');


echo __DIR__ . '/_support';

if (!defined('TESTS_PATH')) {
    define('TESTS_PATH', __DIR__);
}

if (!defined('OPENDXP_TEST')) {
    define('OPENDXP_TEST', true);
}
