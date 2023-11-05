<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Listeners\ResolveRequest;

use Crell\KernelBench\Documents\User;
use Crell\KernelBench\Errors\PermissionDenied;
use Crell\KernelBench\Events\Events\PostRouting;
use Crell\KernelBench\EventsUnified\Events\ResolveRequest;
use Crell\KernelBench\Services\Authorization\UserAuthorizer;
use Crell\KernelBench\Services\Router\RouteResult;
use Crell\KernelBench\Services\Router\RouteSuccess;
use Crell\Tukio\ListenerAfter;
use Crell\Tukio\ListenerBefore;
use Psr\Log\LoggerInterface;

readonly class AuthorizeRequest
{
    public function __construct(
        private UserAuthorizer $authorizer,
    ) {}

    #[ListenerAfter(Routing::class)]
    #[ListenerBefore(ExecuteAction::class)]
    public function __invoke(ResolveRequest $event): void
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
