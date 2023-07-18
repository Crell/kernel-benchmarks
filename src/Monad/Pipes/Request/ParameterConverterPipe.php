<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes;

use Crell\KernelBench\Errors\Error;
use Crell\KernelBench\Services\Router\RouteResult;
use Crell\KernelBench\Services\Router\RouteSuccess;
use Crell\KernelBench\Services\ParamConverter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class ParameterConverterPipe implements RequestPipe
{
    public function __construct(
        private ParamConverter $converter,
    ) {}

    public function __invoke(ServerRequestInterface $request): ServerRequestInterface|ResponseInterface|Error
    {
        /** @var RouteSuccess $result */
        $result = $request->getAttribute(RouteResult::class);

        $newVars = $this->converter->convert($result->vars, $result->parameters);

        return $request->withAttribute(RouteResult::class, $result->withAddedVars($newVars));
    }
}
