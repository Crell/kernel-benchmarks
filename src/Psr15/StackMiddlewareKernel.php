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
    }
}
