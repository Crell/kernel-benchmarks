<?php

declare(strict_types=1);

namespace Crell\KernelBench\Documents;

class AnonymousUser extends User
{
    public function __construct()
    {
        parent::__construct('anonymous', 'Anonymous');
    }
}
