<?php
declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {

    $config_file_path = __DIR__. '/../src/Config/config.php';
    if (!file_exists($config_file_path)) {
        throw  new Exception('Cannot find '.$config_file_path);
    }
    $config = require($config_file_path);
    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () use ($config) {
            return new Settings($config);
        }
    ]);
};
