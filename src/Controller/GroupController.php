<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\User\User;
use App\Service\GroupService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class GroupController
{
    public function __construct(private GroupService $groupService) {}

    public function list(Request $request, Response $response): Response
    {
        $groups = $this->groupService->listGroups();

        $data = [];
        foreach ($groups as $g) {
            $data[] = [
                'id' => $g->getId(),
                'name' => $g->getName(),
                'createdAt' => $g->getCreatedAt()
            ];
        }

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response): Response
    {
        $body = (array)$request->getParsedBody();
        $name = isset($body['name']) ? (string)$body['name'] : '';

        try {
            $g = $this->groupService->createGroup($name);
        } catch (\InvalidArgumentException $e) {
            return $this->jsonError($response, 400, 'bad_request', $e->getMessage());
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE')) {
                return $this->jsonError($response, 409, 'conflict', 'group name already exists');
            }
            return $this->jsonError($response, 500, 'server_error', 'database error');
        }

        $response->getBody()->write(json_encode([
            'id' => $g->getId(),
            'name' => $g->getName(),
            'createdAt' => $g->getCreatedAt()
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }

    public function join(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if (!$user instanceof User) {
            return $this->jsonError($response, 500, 'server_error', 'user context missing');
        }

        $groupId = (int)$args['id'];

        try {
            $this->groupService->joinGroup($groupId, $user->getId());
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'group_not_found') {
                return $this->jsonError($response, 404, 'not_found', 'group not found');
            }
            return $this->jsonError($response, 500, 'server_error', 'unexpected error');
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE')) {
                return $response->withStatus(204);
            }
            return $this->jsonError($response, 500, 'server_error', 'database error');
        }

        return $response->withStatus(204);
    }

    private function jsonError(Response $response, int $status, string $type, string $message): Response
    {
        $response->getBody()->write(json_encode([
            'statusCode' => $status,
            'error' => [
                'type' => $type,
                'description' => $message
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
