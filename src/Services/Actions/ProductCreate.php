<?php

declare(strict_types=1);

namespace Crell\KernelBench\Services\Actions;

use Nyholm\Psr7\Response;
use Psr\Http\Message\RequestInterface;

readonly class ProductCreate
{
    public function __invoke(RequestInterface $request)
    {
        // Do some kind of creation stuff here.
        return new Response(201, ['location' => '/product/1']);
    }
}
