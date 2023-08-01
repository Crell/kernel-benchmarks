<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Listeners\PostRouting;

use Crell\KernelBench\Documents\User;
use Crell\KernelBench\EventsException\Events\ExceptionPostRouting;
use Crell\KernelBench\EventsException\Exceptions\PermissionDenied;
use Crell\KernelBench\Services\Authorization\UserAuthorizer;
use Crell\KernelBench\Services\Router\RouteResult;
use Crell\KernelBench\Services\Router\RouteSuccess;

readonly class AuthorizeRequest
{
    public function __construct(
        private UserAuthorizer $authorizer,
    ) {}

    public function __invoke(ExceptionPostRouting $event): void
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

        throw PermissionDenied::create($request, $user, $permission);
    }
}
