<?php

declare(strict_types=1);

namespace Crell\KernelBench\Psr15;

use Crell\KernelBench\Services\ParamConverter;
use Crell\KernelBench\Services\Routing\RouteResult;
use Crell\KernelBench\Services\Routing\RouteSuccess;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class ParamConverterMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ParamConverter $converter,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteSuccess $result */
        $result = $request->getAttribute(RouteResult::class);

        $newVars = $this->converter->convert($result->vars, $result->parameters);

        if ($newVars) {
            $request = $request->withAttribute(RouteResult::class, $result->withAddedVars($newVars));
        }

        return $handler->handle($request);
    }
}
