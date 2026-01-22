<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class MessagesTest extends TestCase
{
    public function test_send_and_list_messages(): void
    {
        $headers = [
            'X-User-Name' => 'mert'
        ];

        $groupName = 'chat_' . bin2hex(random_bytes(4));

        $g = $this->request('POST', '/groups', ['name' => $groupName], $headers);
        $this->assertSame(201, $g['status'], $g['body']);
        $this->assertIsArray($g['json'], $g['body']);
        $this->assertArrayHasKey('id', $g['json'], $g['body']);

        $gid = (int) $g['json']['id'];

        $join = $this->request('POST', '/groups/' . $gid . '/join', [], $headers);

        $this->assertTrue(
            in_array($join['status'], [200, 201, 204, 409], true),
            'Unexpected join status: ' . $join['status'] . ' body: ' . $join['body']
        );

        $msg = $this->request('POST', '/groups/' . $gid . '/messages', [
            'body' => 'hello'
        ], $headers);

        $this->assertSame(201, $msg['status'], $msg['body']);
        $this->assertIsArray($msg['json'], $msg['body']);
        $this->assertSame('hello', $msg['json']['body'] ?? null, $msg['body']);

        $list = $this->request('GET', '/groups/' . $gid . '/messages', null, $headers);
        $this->assertSame(200, $list['status'], $list['body']);
        $this->assertIsArray($list['json'], $list['body']);

        $items = $list['json']['items'] ?? null;
        $this->assertIsArray($items, $list['body']);
        $this->assertGreaterThanOrEqual(1, count($items));
    }

    public function test_cannot_send_message_if_not_member(): void
    {
        $headers = [
            'X-User-Name' => 'outsider'
        ];

        $groupName = 'private_' . bin2hex(random_bytes(4));

        $g = $this->request('POST', '/groups', ['name' => $groupName], $headers);
        $this->assertSame(201, $g['status'], $g['body']);
        $this->assertIsArray($g['json'], $g['body']);
        $this->assertArrayHasKey('id', $g['json'], $g['body']);

        $gid = (int) $g['json']['id'];

        $res = $this->request('POST', '/groups/' . $gid . '/messages', [
            'body' => 'should fail'
        ], $headers);

        $this->assertSame(403, $res['status'], $res['body']);
    }
}
