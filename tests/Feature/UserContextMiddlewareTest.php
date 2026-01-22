<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class UserContextMiddlewareTest extends TestCase
{
    public function test_missing_header_returns_400(): void
    {
        $res = $this->request('GET', '/groups');

        $this->assertSame(400, $res['status']);
        $this->assertIsArray($res['json']);
        $this->assertSame('missing_header', $res['json']['error'] ?? null);
    }

    public function test_header_allows_request_to_reach_route(): void
    {
        $res = $this->request('GET', '/health', null, [
            'X-User-Name' => 'shahin'
        ]);

        $this->assertSame(200, $res['status']);
        $this->assertIsArray($res['json']);

        // Health endpoint'in dondugu yapina gore kontrol et
        if (array_key_exists('ok', $res['json'])) {
            $this->assertTrue((bool) $res['json']['ok']);
        } elseif (array_key_exists('status', $res['json'])) {
            $this->assertSame('ok', $res['json']['status']);
        } else {
            // En azindan JSON bos degil, middleware engellememis demektir
            $this->assertNotEmpty($res['json']);
        }
    }
}
