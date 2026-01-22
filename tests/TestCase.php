<?php

declare(strict_types=1);

namespace Tests;

use DI\ContainerBuilder;
use Exception;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;

class TestCase extends PHPUnit_TestCase
{
    use ProphecyTrait;

    /**
     * @throws Exception
     */
    protected function getAppInstance(): App
    {
        $containerBuilder = new ContainerBuilder();

        $settings = require __DIR__ . '/../app/settings.php';
        $settings($containerBuilder);

        $dependencies = require __DIR__ . '/../app/dependencies.php';
        $dependencies($containerBuilder);

        $repositories = require __DIR__ . '/../app/repositories.php';
        $repositories($containerBuilder);

        $container = $containerBuilder->build();

        AppFactory::setContainer($container);
        $app = AppFactory::create();

        $middleware = require __DIR__ . '/../app/middleware.php';
        $middleware($app);

        $routes = require __DIR__ . '/../app/routes.php';
        $routes($app);

        return $app;
    }

    /**
     * Create a PSR-7 request with optional JSON body.
     *
     * @param string $method
     * @param string $path
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $jsonBody
     */
    protected function createRequest(
        string $method,
        string $path,
        array $headers = [],
        ?array $jsonBody = null
    ): Request {
        if ($path === '') {
            $path = '/';
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        $method = strtoupper($method);

        $uri = new Uri('http', 'localhost', null, $path);

        $streamFactory = new StreamFactory();
        $payload = '';

        $h = new Headers();

        $h->addHeader('Accept', 'application/json');

        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        if ($jsonBody !== null) {
            $encoded = json_encode($jsonBody, JSON_UNESCAPED_SLASHES);
            if ($encoded === false) {
                throw new Exception('Failed to encode JSON body');
            }

            $payload = $encoded;

            if (isset($headers['Content-Type']) === false) {
                $h->addHeader('Content-Type', 'application/json');
            }
        }

        $stream = $streamFactory->createStream($payload);

        $serverParams = [
            'REQUEST_METHOD' => $method,
            'REQUEST_URI' => $path,
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '80',
            'HTTP_HOST' => 'localhost',
        ];

        foreach ($headers as $name => $value) {
            $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
            $serverParams[$key] = $value;
        }

        $cookies = [];
        $uploadedFiles = [];

        return new SlimRequest(
            $method,
            $uri,
            $h,
            $cookies,
            $serverParams,
            $stream,
            $uploadedFiles
        );
    }

    /**
     * Helper to run request through app.
     */
    protected function runApp(App $app, Request $request): ResponseInterface
    {
        return $app->handle($request);
    }

    /**
     * Default user header helper.
     */
    protected function withUser(string $username): array
    {
        return [
            'X-User-Name' => $username
        ];
    }
    protected function request(string $method, string $path, ?array $jsonBody = null, array $headers = []): array
{
    $app = $this->getAppInstance();

    $req = $this->createRequest($method, $path, $headers, $jsonBody);
    $res = $this->runApp($app, $req);

    $raw = (string) $res->getBody();

    $decoded = null;
    $arr = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $decoded = $arr;
    }

    return [
        'status' => $res->getStatusCode(),
        'body' => $raw,
        'json' => $decoded,
        'headers' => $res->getHeaders(),
    ];
}

}
