<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes\Request;

use Crell\KernelBench\Documents\User;
use Crell\KernelBench\Errors\Error;
use Crell\KernelBench\Errors\PermissionDenied;
use Crell\KernelBench\Monad\Pipes\RequestPipe;
use Crell\KernelBench\Services\Authorization\UserAuthorizer;
use Crell\KernelBench\Services\Router\RouteResult;
use Crell\KernelBench\Services\Router\RouteSuccess;
use Psr\Http\Message\ServerRequestInterface;

readonly class AuthorizeRequestPipe implements RequestPipe
{
    public function __construct(
        private UserAuthorizer $authorizer,
    ) {}

    public function __invoke(ServerRequestInterface $request): ServerRequestInterface|Error
    {
        /** @var RouteSuccess $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        /** @var User $user */
        $user = $request->getAttribute(User::class);

        $permission = $routeResult?->permission;

        // If there's no permission requirement, fail open. Bad, but meh.
        if (!$permission) {
            return $request;
        }

        // Success does nothing.
        if ($this->authorizer->userMay($user, $permission)) {
            return $request;
        }

        // Just mark the error.  It's now someone else's job.
        return new PermissionDenied($request, $user, $permission);
    }
}
