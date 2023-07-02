<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Events;

use Psr\Http\Message\ResponseInterface;

interface CarriesResponse
{
    public function setResponse(ResponseInterface $response): static;

    public function getResponse(): ?ResponseInterface;
}
