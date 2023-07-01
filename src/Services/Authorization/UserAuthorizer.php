<?php

declare(strict_types=1);

namespace Crell\KernelBench\Services\Authorization;

use Crell\KernelBench\Documents\User;

class UserAuthorizer
{
    public function userMay(User $user, string $perm): bool
    {
        return in_array($perm, $user->permissions, strict: true);
    }
}
