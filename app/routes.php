<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use App\Application\Settings\SettingsInterface;
use App\Controller\GroupController;
use App\Controller\MessageController;
use App\Db\Connection;
use App\Db\SchemaInitializer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->get('/health', function (Request $request, Response $response) {
        $settings = $this->get(SettingsInterface::class);
        $db = $settings->get('db');

        $connection = $this->get(Connection::class);

        SchemaInitializer::run($connection, $db['schema']);

        $response->getBody()->write(json_encode([
            'status' => 'ok'
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

    $app->get('/groups', [GroupController::class, 'list']);
    $app->post('/groups', [GroupController::class, 'create']);
    $app->post('/groups/{id}/join', [GroupController::class, 'join']);

    $app->post('/groups/{id}/messages', [MessageController::class, 'create']);
    $app->get('/groups/{id}/messages', [MessageController::class, 'list']);
};
