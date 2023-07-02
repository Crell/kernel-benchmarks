<?php

declare(strict_types=1);

namespace Crell\KernelBench\Psr15;

use Crell\KernelBench\Documents\User;
use Crell\KernelBench\Services\Authorization\UserAuthorizer;
use Crell\KernelBench\Services\Routing\RouteResult;
use Crell\KernelBench\Services\Routing\RouteSuccess;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class AuthorizationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UserAuthorizer $authorizer,
    ) {}


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteSuccess $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        /** @var User $user */
        $user = $request->getAttribute(User::class);

        $permission = $routeResult?->permission;

        // Success just goes through the stack.
        if ($this->authorizer->userMay($user, $permission)) {
            return $handler->handle($request);
        }

        // Failure needs handling.


    }

}
