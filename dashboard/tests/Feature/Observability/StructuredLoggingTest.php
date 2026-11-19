<?php

namespace Tests\Feature\Observability;

use Tests\TestCase;

class StructuredLoggingTest extends TestCase
{
    public function test_valid_request_id_is_propagated_to_the_response(): void
    {
        $response = $this->getJson('/api/v1/addresses/countries', [
            'X-Request-ID' => 'trace-123',
        ]);

        $response->assertHeader('X-Request-ID', 'trace-123')
            ->assertHeader('X-Correlation-ID', 'trace-123');
    }

    public function test_invalid_request_id_is_replaced_with_a_safe_identifier(): void
    {
        $response = $this->getJson('/api/v1/addresses/countries', [
            'X-Request-ID' => str_repeat('x', 129),
        ]);

        $requestId = $response->headers->get('X-Request-ID');

        $this->assertNotNull($requestId);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $requestId);
        $this->assertSame($requestId, $response->headers->get('X-Correlation-ID'));
    }
}
