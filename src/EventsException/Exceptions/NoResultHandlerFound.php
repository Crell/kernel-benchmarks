<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Exceptions;

use Psr\Http\Message\ServerRequestInterface;

class NoResultHandlerFound extends \RuntimeException
{
    public readonly ServerRequestInterface $request;
    public readonly mixed $result;

    public static function create(ServerRequestInterface $request, mixed $result): self
    {
        $new = new self();
        $new->request = $request;
        $new->result = $result;

        return $new;
    }
}
