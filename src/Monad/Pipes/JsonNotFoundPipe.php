<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes;

use Crell\ApiProblem\ApiProblem;
use Crell\ApiProblem\HttpConverter;
use Crell\KernelBench\Errors\Error;
use Crell\KernelBench\Errors\NotFound;
use Crell\KernelBench\Services\Router\RequestFormat;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class JsonNotFoundPipe implements ErrorPipe
{
    public function __construct(
        private HttpConverter $converter,
    ) {}

    /**
     * @param NotFound $error
     */
    public function __invoke(Error $error, ServerRequestInterface $request): ResponseInterface|Error
    {
        if ($request->getAttribute(RequestFormat::class)->accept === 'json') {
            $problem = (new ApiProblem('Not Found'))
                ->setStatus(404);
            return $this->converter->toJsonResponse($problem);
        }

        // Let someone else deal with it.
        return $error;
    }
}
