<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\User\User;
use App\Service\MessageService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class MessageController
{
    public function __construct(private MessageService $messageService) {}

    public function create(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if (!$user instanceof User) {
            return $this->jsonError($response, 500, 'server_error', 'user context missing');
        }

        $groupId = (int)$args['id'];
        $bodyArr = (array)$request->getParsedBody();
        $text = isset($bodyArr['body']) ? (string)$bodyArr['body'] : '';

        try {
            $msg = $this->messageService->sendMessage($groupId, $user->getId(), $text);
        } catch (\InvalidArgumentException $e) {
            return $this->jsonError($response, 400, 'bad_request', $e->getMessage());
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'group_not_found') {
                return $this->jsonError($response, 404, 'not_found', 'group not found');
            }
            if ($e->getMessage() === 'not_a_member') {
                return $this->jsonError($response, 403, 'forbidden', 'not a group member');
            }
            return $this->jsonError($response, 500, 'server_error', 'unexpected error');
        }

        $response->getBody()->write(json_encode($msg->toArray()));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function list(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if (!$user instanceof User) {
            return $this->jsonError($response, 500, 'server_error', 'user context missing');
        }

        $groupId = (int)$args['id'];

        $query = $request->getQueryParams();
        $limit = isset($query['limit']) ? (int)$query['limit'] : 50;
        $beforeId = isset($query['beforeId']) ? (int)$query['beforeId'] : null;

        if ($beforeId !== null && $beforeId <= 0) {
            $beforeId = null;
        }

        try {
            $msgs = $this->messageService->listMessages($groupId, $user->getId(), $limit, $beforeId);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'group_not_found') {
                return $this->jsonError($response, 404, 'not_found', 'group not found');
            }
            if ($e->getMessage() === 'not_a_member') {
                return $this->jsonError($response, 403, 'forbidden', 'not a group member');
            }
            return $this->jsonError($response, 500, 'server_error', 'unexpected error');
        }

        $data = [];
        $nextBeforeId = null;

        foreach ($msgs as $m) {
            $arr = $m->toArray();
            $data[] = $arr;
            $nextBeforeId = $arr['id'];
        }

        $response->getBody()->write(json_encode([
            'items' => $data,
            'nextBeforeId' => $nextBeforeId
        ]));

        return $response->withHeader('Content-Type', 'application/json');
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
