<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes;

use Crell\KernelBench\Errors\Error;
use Crell\KernelBench\Services\Router\RequestFormat;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeriveFormatPipe implements RequestPipe
{
    public function __invoke(ServerRequestInterface $request): ServerRequestInterface|ResponseInterface|Error
    {
        $contentFormat = $this->mapType($request->getHeader('content-type')[0] ?? '');

        $acceptFormat = $this->mapType($request->getHeader('accept')[0] ?? '');

        // If there is no Accept header but there is a content type, assume the agent
        // wants the same type back.
        if ($acceptFormat === 'unknown' && $contentFormat !== 'unknown') {
            $acceptFormat = $contentFormat;
        }

        return $request->withAttribute(RequestFormat::class, new RequestFormat(
            accept: $acceptFormat,
            content: $contentFormat,
        ));
    }

    /**
     * @todo This is clearly not the correct logic, but it's just a stub for now.
     */
    private function mapType(string $mimeType): string
    {
        if (str_contains($mimeType, 'application/json')) {
            return 'json';
        }
        if (str_contains($mimeType, 'text/html')) {
            return 'html';
        }
        return 'unknown';
    }
}
