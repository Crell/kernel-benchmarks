<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Events;

use Crell\KernelBench\Events\Events\CarriesResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HandleResponse implements CarriesResponse
{
    public function __construct(
        private ResponseInterface $response,
        public readonly ServerRequestInterface $request,
    ) {}

    public function setResponse(ResponseInterface $response): static
    {
        $this->response = $response;
        return $this;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
