<?php

declare(strict_types=1);

namespace Crell\KernelBench\Services\Authentication;

use Crell\KernelBench\Documents\AnonymousUser;
use Crell\KernelBench\Documents\User;
use Psr\Http\Message\RequestInterface;

readonly class UserAuthenticator
{
    public function authenticate(RequestInterface $request): User
    {
        $auth = $request->getHeader('auth')[0] ?? null;

        return match ($auth) {
            'reader' => new User('reader', 'Reader', ['read']),
            'creator' => new User('creator', 'Creator', ['read', 'create']),
            default => new AnonymousUser(),
        };
    }
}
