<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class UserContextMiddleware implements MiddlewareInterface
{
    public function __construct(private UserService $userService) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $path = $request->getUri()->getPath();

        // Root should be public (ActionTest expects middleware not to block it)
        if ($path === '' || $path === '/') {
            return $handler->handle($request);
        }

        // Public endpoints (support optional /api prefix)
        if (preg_match('#^/(api/)?health(/|$)#', $path) === 1) {
            return $handler->handle($request);
        }

        if (preg_match('#^/(api/)?users(/|$)#', $path) === 1) {
            return $handler->handle($request);
        }

        $username = trim((string) $request->getHeaderLine('X-User-Name'));

        if ($username === '') {
            $res = new Response(400);
            $res->getBody()->write((string) json_encode([
                'error' => 'missing_header',
                'message' => 'X-User-Name header is required'
            ]));
            return $res->withHeader('Content-Type', 'application/json');
        }

        $user = $this->userService->getOrCreate($username);
        $request = $request->withAttribute('user', $user);

        return $handler->handle($request);
    }
}
