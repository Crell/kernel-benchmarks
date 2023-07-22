<?php

declare(strict_types=1);

namespace Crell\KernelBench\Psr15;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class StackMiddlewareKernel implements RequestHandlerInterface
{
    private \SplStack $stack;

    public function __construct(
        RequestHandlerInterface $baseHandler,
    ) {
        $this->stack = new \SplStack();
        $this->stack->push($baseHandler);
    }

    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->stack->push(new PassthruHandler($middleware, $this->stack->top()));
        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->stack->top()->handle($request);

        /**
         * @todo Port this logic into a step in each kernel.
         *
         * This is to be in compliance with RFC 2616, Section 9.
         * If the incoming request method is HEAD, we need to ensure that the response body
         * is empty as the request may fall back on a GET route handler due to FastRoute's
         * routing logic which could potentially append content to the response body
         * https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
         */
        $method = strtoupper($request->getMethod());
        if ($method === 'HEAD') {
            $emptyBody = $this->responseFactory->createResponse()->getBody();
            return $response->withBody($emptyBody);
        }

        return $response;
    }
}
