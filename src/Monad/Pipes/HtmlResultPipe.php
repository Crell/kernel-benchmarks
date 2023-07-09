<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes;

use Crell\KernelBench\Services\ResponseBuilder;
use Crell\KernelBench\Services\Template;
use Psr\Http\Message\ServerRequestInterface;

readonly class HtmlResultPipe implements ResultPipe
{
    public function __construct(
        private Template $templates,
        private ResponseBuilder $responseBuilder,
    ) {}

    public function __invoke(object $subject, ServerRequestInterface $request): object
    {
        $page = $this->templates->render($this->templateName($subject));
        return $this->responseBuilder->ok($page, contentType: 'text/html');
    }

    private function templateName(object $subject): string
    {
        $parts = explode('\\', $subject::class);
        return strtolower($parts[array_key_last($parts)]);
    }
}
