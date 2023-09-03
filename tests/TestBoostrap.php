<?php declare(strict_types=1);

use Shopware\Core\TestBootstrapper;

$loader = (new TestBootstrapper())
    ->addCallingPlugin()
    ->addActivePlugins('SwPlayground')
    ->bootstrap()
    ->getClassLoader();

$loader->addPsr4('SwPlayground\\Tests\\', __DIR__);
