<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes;

use Crell\KernelBench\Services\ActionInvoker;
use Crell\KernelBench\Services\ResponseBuilder;
use Psr\Http\Message\ServerRequestInterface;

readonly class HandleActionPipe implements ActionPipe
{
    public function __construct(
        private ResponseBuilder $responseBuilder,
        private ActionInvoker $invoker,
    ) {}

    public function __invoke(ServerRequestInterface $request): object
    {
        $actionResult = $this->invoker->invokeAction($request);

        // Assume a string response means HTML.
        // This may not be an ideal assumption, but it's probably right.
        // The pipeline is really only setup to handle objects, which is why we cannot
        // just return the string here.
        if (is_string($actionResult)) {
            return $this->responseBuilder->ok(body: $actionResult, contentType: 'text/html');
        }

        return $actionResult;
    }
}
