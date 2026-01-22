<?php

declare(strict_types=1);

namespace Tests\Helpers;

use DI\ContainerBuilder;
use Slim\App;
use Slim\Factory\AppFactory;

final class TestAppFactory
{
    public static function create(): App
    {
        $containerBuilder = new ContainerBuilder();

        $settings = require __DIR__ . '/../../app/settings.php';
        $settings($containerBuilder);

        $dependencies = require __DIR__ . '/../../app/dependencies.php';
        $dependencies($containerBuilder);

        $repositories = require __DIR__ . '/../../app/repositories.php';
        $repositories($containerBuilder);

        $container = $containerBuilder->build();

        $app = AppFactory::createFromContainer($container);

        $middleware = require __DIR__ . '/../../app/middleware.php';
        $middleware($app);

        $routes = require __DIR__ . '/../../app/routes.php';
        $routes($app);

        $app->addRoutingMiddleware();
        $app->addBodyParsingMiddleware();

        return $app;
    }
}
