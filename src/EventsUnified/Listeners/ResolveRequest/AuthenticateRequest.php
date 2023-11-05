<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Listeners\ResolveRequest;

use Crell\KernelBench\Documents\User;
use Crell\KernelBench\Events\Events\PreRouting;
use Crell\KernelBench\EventsUnified\Events\ResolveRequest;
use Crell\KernelBench\Services\Authentication\UserAuthenticator;
use Crell\Tukio\ListenerBefore;
use Psr\Log\LoggerInterface;

readonly class AuthenticateRequest
{
    public function __construct(
        private UserAuthenticator $authenticator,
    ) {}

    #[ListenerBefore(Routing::class)]
    public function __invoke(ResolveRequest $event): void
    {
        $user = $this->authenticator->authenticate($event->request());

        $event->setAttribute(User::class, $user);
    }
}
