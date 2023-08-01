<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Exceptions;

use Crell\KernelBench\Documents\User;
use Psr\Http\Message\ServerRequestInterface;

class PermissionDenied extends \RuntimeException
{
    public readonly ServerRequestInterface $request;
    public readonly User $user;
    public readonly string $permission;

    public static function create(ServerRequestInterface $request, User $user, string $permission): self
    {
        $new = new self();
        $new->request = $request;
        $new->user = $user;
        $new->permission = $permission;

        return $new;
    }
}
