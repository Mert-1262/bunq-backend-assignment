<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class GroupsTest extends TestCase
{
    public function test_create_group_then_list_contains_it(): void
    {
        $headers = [
            'X-User-Name' => 'shahin'
        ];

        $name = 'general_' . bin2hex(random_bytes(4));

        $create = $this->request('POST', '/groups', [
            'name' => $name
        ], $headers);

        $this->assertSame(201, $create['status'], $create['body']);
        $this->assertIsArray($create['json'], $create['body']);
        $this->assertSame($name, $create['json']['name'] ?? null, $create['body']);

        $list = $this->request('GET', '/groups', null, $headers);

        $this->assertSame(200, $list['status'], $list['body']);
        $this->assertIsArray($list['json'], $list['body']);

        // Your API returns a raw array, not { items: [...] }
        $names = [];
        foreach ($list['json'] as $g) {
            if (is_array($g) && array_key_exists('name', $g)) {
                $names[] = $g['name'];
            }
        }

        $this->assertContains($name, $names, $list['body']);
    }

    public function test_duplicate_group_returns_409(): void
    {
        $headers = [
            'X-User-Name' => 'shahin'
        ];

        $name = 'dup_' . bin2hex(random_bytes(4));

        $first = $this->request('POST', '/groups', ['name' => $name], $headers);
        $this->assertSame(201, $first['status'], $first['body']);

        $second = $this->request('POST', '/groups', ['name' => $name], $headers);

        $this->assertSame(409, $second['status'], $second['body']);
    }

    public function test_join_twice_returns_409_or_200(): void
    {
        $headers = [
            'X-User-Name' => 'shahin'
        ];

        $name = 'joinme_' . bin2hex(random_bytes(4));

        $g = $this->request('POST', '/groups', ['name' => $name], $headers);
        $this->assertSame(201, $g['status'], $g['body']);
        $this->assertIsArray($g['json'], $g['body']);
        $this->assertArrayHasKey('id', $g['json'], $g['body']);

        $gid = (int) $g['json']['id'];

        // Send empty json body to be safe
        $first = $this->request('POST', '/groups/' . $gid . '/join', [], $headers);
        $this->assertTrue(
            in_array($first['status'], [200, 201, 204], true),
            'Unexpected join status: ' . $first['status'] . ' body: ' . $first['body']
        );

        $second = $this->request('POST', '/groups/' . $gid . '/join', [], $headers);
        $this->assertTrue(
            in_array($second['status'], [200, 204, 409], true),
            'Unexpected join status: ' . $second['status'] . ' body: ' . $second['body']
        );
    }
}
