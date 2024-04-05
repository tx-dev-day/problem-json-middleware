<?php declare(strict_types=1);

namespace Devday\ProblemJsonMiddleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ProblemJsonMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        try {
            return $handler->handle($request);
        } catch (Throwable) {
            return $this->generateResponse();
        }
    }

    private function generateResponse(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(500)
            ->withHeader('Content-Type', 'application/problem+json')
            ->withHeader('Content-Language', 'en');
        $response->getBody()->write('{}');

        return $response;
    }
}
