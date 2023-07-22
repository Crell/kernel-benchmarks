<?php

declare(strict_types=1);

namespace Crell\KernelBench\Psr15\Middleware;

use Crell\KernelBench\Documents\User;
use Crell\KernelBench\Errors\PermissionDenied;
use Crell\KernelBench\Events\Events\HandleError;
use Crell\KernelBench\Services\Authorization\UserAuthorizer;
use Crell\KernelBench\Services\ResponseBuilder;
use Crell\KernelBench\Services\Router\RequestFormat;
use Crell\KernelBench\Services\Router\RouteResult;
use Crell\KernelBench\Services\Router\RouteSuccess;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class AuthorizationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UserAuthorizer $authorizer,
        private ResponseBuilder $responseBuilder,
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteSuccess $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        /** @var User $user */
        $user = $request->getAttribute(User::class);

        $permission = $routeResult?->permission;

        // If there's no permission requirement, fail open. Bad, but meh.
        if (!$permission) {
            return $handler->handle($request);
        }

        // Success just goes through the stack.
        if ($this->authorizer->userMay($user, $permission)) {
            return $handler->handle($request);
        }

        // 403 error
        /** @var RequestFormat $format */
        $format = $request->getAttribute(RequestFormat::class);

        // To make this extensible to arbitrary formats, we need a registration
        // mechanism here.  We don't have an error hook/event/pipe to use.
        // For simplicity, just piggy-back on the HandleError event from EventKernel.

        /** @var HandleError $event */
        $event = $this->dispatcher->dispatch(new HandleError(new PermissionDenied($request, $user, $permission), $request));

        return $event->getResponse() ?? $this->responseBuilder->forbidden('Forbidden', 'text/plain');
    }
}
