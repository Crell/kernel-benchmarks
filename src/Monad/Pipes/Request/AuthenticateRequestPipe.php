<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes\Request;

use Crell\KernelBench\Documents\User;
use Crell\KernelBench\Monad\Pipes\RequestPipe;
use Crell\KernelBench\Services\Authentication\UserAuthenticator;
use Psr\Http\Message\ServerRequestInterface;

readonly class AuthenticateRequestPipe implements RequestPipe
{
    public function __construct(
        private UserAuthenticator $authenticator,
    ) {}

    public function __invoke(ServerRequestInterface $request): ServerRequestInterface
    {
        $user = $this->authenticator->authenticate($request);

        return $request->withAttribute(User::class, $user);
    }
}
