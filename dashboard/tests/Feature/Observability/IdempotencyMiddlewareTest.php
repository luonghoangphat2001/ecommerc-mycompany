<?php

namespace Tests\Feature\Observability;

use App\Http\Middleware\HandleIdempotency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class IdempotencyMiddlewareTest extends TestCase
{
    public function test_same_key_and_payload_replays_the_first_successful_response(): void
    {
        Cache::flush();
        $calls = 0;
        $middleware = new HandleIdempotency();

        $first = $middleware->handle($this->request(['amount' => 100]), function () use (&$calls) {
            $calls++;
            return new JsonResponse(['order_id' => 10], 201);
        });
        $second = $middleware->handle($this->request(['amount' => 100]), function () use (&$calls) {
            $calls++;
            return new JsonResponse(['order_id' => 11], 201);
        });

        $this->assertSame(1, $calls);
        $this->assertSame(201, $second->getStatusCode());
        $this->assertSame('true', $second->headers->get('Idempotency-Replayed'));
        $this->assertSame(['order_id' => 10], $second->getData(true));
    }

    public function test_reusing_a_key_with_different_payload_is_rejected(): void
    {
        Cache::flush();
        $middleware = new HandleIdempotency();

        $middleware->handle($this->request(['amount' => 100]), fn () => new JsonResponse(['ok' => true], 201));
        $response = $middleware->handle($this->request(['amount' => 200]), fn () => new JsonResponse(['ok' => true], 201));

        $this->assertSame(422, $response->getStatusCode());
    }

    private function request(array $payload): Request
    {
        $request = Request::create('/api/v1/orders', 'POST', $payload, [], [], [], json_encode($payload));
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('Idempotency-Key', 'order-key-1');
        $request->setUserResolver(fn () => new class {
            public function getAuthIdentifier(): int
            {
                return 42;
            }
        });

        return $request;
    }
}
