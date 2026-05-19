<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class ForceJsonResponseTest extends TestCase
{
    public function test_handle_sets_accept_header_to_application_json(): void
    {
        $request = Request::create('/test', 'GET');
        $middleware = new ForceJsonResponse();

        $middleware->handle($request, function (Request $req) use (&$capturedRequest) {
            $capturedRequest = $req;
            return new Response();
        });

        $this->assertSame('application/json', $capturedRequest->header('Accept'));
    }

    public function test_handle_overwrites_existing_accept_header(): void
    {
        $request = Request::create('/test', 'GET');
        $request->headers->set('Accept', 'text/html');

        $middleware = new ForceJsonResponse();
        $middleware->handle($request, function (Request $req) use (&$capturedRequest) {
            $capturedRequest = $req;
            return new Response();
        });

        $this->assertSame('application/json', $capturedRequest->header('Accept'));
    }

    public function test_handle_passes_request_to_next_middleware(): void
    {
        $request = Request::create('/test', 'GET');
        $middleware = new ForceJsonResponse();
        $nextCalled = false;

        $middleware->handle($request, function () use (&$nextCalled) {
            $nextCalled = true;
            return new Response();
        });

        $this->assertTrue($nextCalled);
    }

    public function test_handle_returns_response_from_next_middleware(): void
    {
        $request = Request::create('/test', 'GET');
        $middleware = new ForceJsonResponse();
        $expectedResponse = new Response('body', 200);

        $result = $middleware->handle($request, fn () => $expectedResponse);

        $this->assertSame($expectedResponse, $result);
    }
}
