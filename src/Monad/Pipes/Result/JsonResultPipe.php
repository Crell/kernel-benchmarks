<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes;

use Crell\KernelBench\Services\ResponseBuilder;
use Psr\Http\Message\ServerRequestInterface;

readonly class JsonResultPipe implements ResultPipe
{
    public function __construct(
        private ResponseBuilder $responseBuilder,
    ) {}

    public function __invoke(object $subject, ServerRequestInterface $request): object
    {
        $body = json_encode($subject, JSON_THROW_ON_ERROR);
        return $this->responseBuilder->ok($body, 'application/json');
    }
}
