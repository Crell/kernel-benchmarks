<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes\Response;

use Crell\KernelBench\Errors\Error;
use Crell\KernelBench\Monad\Pipes\ResponsePipe;
use Crell\KernelBench\Services\RequestCache;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CacheRecordPipe implements ResponsePipe
{
    public function __construct(
        private RequestCache $cache,
    ) {}

    public function __invoke(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface|Error
    {
        $this->cache->setResponseFor($request, $response);
        return $response;
    }

}
