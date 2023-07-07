<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Events;

use Crell\KernelBench\Errors\Error;

interface CarriesError
{
    public function setError(Error $error): static;

    public function getError(): ?Error;
}
