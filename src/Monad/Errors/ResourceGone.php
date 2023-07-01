<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Errors;

use Psr\Http\Message\ServerRequestInterface;

readonly class ResourceGone implements Error
{
    public function __construct(
        public ServerRequestInterface $request,
    ) {}
}
