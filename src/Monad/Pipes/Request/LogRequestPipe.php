<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes\Request;

use Crell\KernelBench\Monad\Pipes\RequestPipe;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

readonly class LogRequestPipe implements RequestPipe
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function __invoke(ServerRequestInterface $request): ServerRequestInterface
    {
        $this->logger->info('Request received to {path}', [
            'path' => $request->getUri()->getPath(),
        ]);

        return $request;
    }
}
