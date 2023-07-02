<?php

declare(strict_types=1);

namespace Crell\KernelBench\Errors;

use Psr\Http\Message\ServerRequestInterface;

readonly class MethodNotAllowed implements Error
{
    /**
     * @param string[] $allowedMethods
     */
    public function __construct(
        public ServerRequestInterface $request,
        public array $allowedMethods,
    ) {}
}
