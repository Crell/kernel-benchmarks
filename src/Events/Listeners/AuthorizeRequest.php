<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Listeners;

use Crell\KernelBench\Documents\User;
use Crell\KernelBench\Errors\PermissionDenied;
use Crell\KernelBench\Events\Events\PostRouting;
use Crell\KernelBench\Services\Authorization\UserAuthorizer;
use Crell\KernelBench\Services\Routing\RouteResult;
use Crell\KernelBench\Services\Routing\RouteSuccess;

readonly class AuthorizeRequest
{
    public function __construct(
        private UserAuthorizer $authorizer,
    ) {}

    public function __invoke(PostRouting $event): void
    {
        $request = $event->request();

        /** @var RouteSuccess $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        /** @var User $user */
        $user = $request->getAttribute(User::class);

        $permission = $routeResult?->permission;

        // If there's no permission requirement, fail open. Bad, but meh.
        if (!$permission) {
            return;
        }

        // Success does nothing.
        if ($this->authorizer->userMay($user, $permission)) {
            return;
        }

        // Just mark the error.  It's now someone else's job.
        $event->setError(new PermissionDenied($request, $user, $permission));
    }
}
