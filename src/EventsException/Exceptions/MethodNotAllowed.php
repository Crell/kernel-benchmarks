<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Exceptions;

use Psr\Http\Message\ServerRequestInterface;

class MethodNotAllowed extends \RuntimeException
{
    public readonly ServerRequestInterface $request;
    public readonly array $allowedMethods;

    /**
     * @param string[] $allowedMethods
     */
    public static function create(ServerRequestInterface $request, array $allowedMethods): self
    {
        $new = new self();
        $new->request = $request;
        $new->allowedMethods = $allowedMethods;

        return $new;
    }
}
