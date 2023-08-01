<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Listeners\PreRouting;

use Crell\KernelBench\Documents\User;
use Crell\KernelBench\EventsException\Events\ExceptionPreRouting;
use Crell\KernelBench\Services\Authentication\UserAuthenticator;

readonly class AuthenticateRequest
{
    public function __construct(
        private UserAuthenticator $authenticator,
    ) {}

    public function __invoke(ExceptionPreRouting $event): void
    {
        $user = $this->authenticator->authenticate($event->request());

        $event->setRequest($event->request()->withAttribute(User::class, $user));
    }
}
