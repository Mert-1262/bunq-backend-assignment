<?php

declare(strict_types=1);

use App\Application\Middleware\UserContextMiddleware;
use App\Application\Settings\SettingsInterface;
use App\Db\Connection;
use App\Repository\GroupMemberRepository;
use App\Repository\GroupRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\GroupService;
use App\Service\MessageService;
use App\Service\UserService;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([

        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },

        Connection::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);
            $db = $settings->get('db');

            return new Connection($db['path']);
        },

        UserRepository::class => function (ContainerInterface $c) {
            return new UserRepository($c->get(Connection::class));
        },

        UserService::class => function (ContainerInterface $c) {
            return new UserService($c->get(UserRepository::class));
        },

        UserContextMiddleware::class => function (ContainerInterface $c) {
            return new UserContextMiddleware($c->get(UserService::class));
        },

        GroupRepository::class => function (ContainerInterface $c) {
            return new GroupRepository($c->get(Connection::class));
        },

        GroupMemberRepository::class => function (ContainerInterface $c) {
            return new GroupMemberRepository($c->get(Connection::class));
        },

        MessageRepository::class => function (ContainerInterface $c) {
            return new MessageRepository($c->get(Connection::class));
        },

        GroupService::class => function (ContainerInterface $c) {
            return new GroupService(
                $c->get(GroupRepository::class),
                $c->get(GroupMemberRepository::class)
            );
        },

        MessageService::class => function (ContainerInterface $c) {
            return new MessageService(
                $c->get(GroupRepository::class),
                $c->get(GroupMemberRepository::class),
                $c->get(MessageRepository::class)
            );
        },

        \App\Controller\GroupController::class => function (ContainerInterface $c) {
            return new \App\Controller\GroupController(
                $c->get(GroupService::class)
            );
        },

        \App\Controller\MessageController::class => function (ContainerInterface $c) {
            return new \App\Controller\MessageController(
                $c->get(MessageService::class)
            );
        },

    ]);
};
