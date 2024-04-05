<?php

declare(strict_types=1);

namespace Devday\ProblemJsonMiddleware\Tests;

use Devday\ProblemJsonMiddleware\ProblemJsonMiddleware;
use Exception;
use JsonException;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProblemJsonMiddlewareTest extends TestCase
{
    private ResponseFactoryInterface&ServerRequestFactoryInterface $psr17Factory;
    private RequestHandlerInterface $handler;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->psr17Factory = new Psr17Factory();
        $this->handler = $this->createMock(RequestHandlerInterface::class);
    }

    /**
     * @test
     */
    public function processShouldReturnResponseWhenNoErrorIsThrown(): void
    {
        $middleware = new ProblemJsonMiddleware($this->psr17Factory);
        $response = $this->psr17Factory->createResponse();

        $this->handler->method('handle')->willReturn($response);

        $request = $this->createRequest();

        self::assertSame(
            $response,
            $middleware->process(request: $request, handler: $this->handler)
        );
    }

    /**
     * @test
     */
    public function processShouldReturnGenerateResponseWithContentTypeProblemJsonAndContentLanguage(): void
    {
        $middleware = new ProblemJsonMiddleware($this->psr17Factory);

        $this->handler->method('handle')->willThrowException(new Exception());

        $request = $this->createRequest();

        $response = $middleware->process(request: $request, handler: $this->handler);

        self::assertSame(
            500,
            $response->getStatusCode()
        );
        self::assertSame(
            ['application/problem+json'],
            $response->getHeader('Content-Type')
        );
        self::assertSame(
            ['en'],
            $response->getHeader('Content-Language')
        );
    }

    /**
     * @test
     * @throws JsonException
     */
    public function processShouldReturnGenerateResponseWithJSONBodyOnError(): void
    {
        $middleware = new ProblemJsonMiddleware($this->psr17Factory);

        $this->handler->method('handle')->willThrowException(new Exception());

        $request = $this->createRequest();

        $response = $middleware->process(request: $request, handler: $this->handler);

        self::assertSame(
            '{}',
            (string) $response->getBody()
        );
    }

    /**
     * @test
     * @throws JsonException
     */
    public function processShouldReturnGenerateResponseWithJSONProblemBodyOnError(): void
    {
        $middleware = new ProblemJsonMiddleware($this->psr17Factory);

        $this->handler->method('handle')->willThrowException(new Exception());

        $request = $this->createRequest();

        $response = $middleware->process(request: $request, handler: $this->handler);

        self::assertSame(
            [
                'type' => 'http://example.com/status/500',
                'title' => 'Internal error',
                'detail' => 'An internal error occurred. Please contact the admin',
                'instance' => 'http://app.example.com/foo',
            ],
            json_decode((string) $response->getBody(), true)
        );
    }

    private function createRequest(): ServerRequestInterface
    {
        return ($this->psr17Factory)->createServerRequest('GET', 'https://example.com');
    }
}
