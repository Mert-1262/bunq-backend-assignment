<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class HealthTest extends TestCase
{
    public function test_health_returns_ok(): void
    {
        $res = $this->request('GET', '/health', null, [
            'X-User-Name' => 'tester'
        ]);

        $this->assertSame(200, $res['status']);
        $this->assertIsArray($res['json']);
        $this->assertSame('ok', $res['json']['status'] ?? null);
    }
}
