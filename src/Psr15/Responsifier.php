<?php

declare(strict_types=1);

namespace Crell\KernelBench\Psr15;

use Crell\ApiProblem\ApiProblem;
use Crell\KernelBench\Errors\MethodNotAllowed;
use Crell\KernelBench\Errors\NotFound;
use Crell\KernelBench\Services\ResponseBuilder;
use Crell\KernelBench\Services\Router\RequestFormat;
use Crell\KernelBench\Services\Template;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class Responsifier
{
    public function __construct(
        private Template $template,
        private ResponseBuilder $responseBuilder,
    ) {}

    public function makeResponse(mixed $result, ServerRequestInterface $request): ResponseInterface
    {
        /** @var RequestFormat $format */
        $format = $request->getAttribute(RequestFormat::class);

        $type = $format?->accept ?? 'unknown';


        $res = [$result::class, $type];

        $body = match ($res) {
            [MethodNotAllowed::class, 'html'] => $this->template->render('method_not_allowed', []),
            [NotFound::class, 'html'] => $this->template->render('not_found', []),
        };


    }
}
