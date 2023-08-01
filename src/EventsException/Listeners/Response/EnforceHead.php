<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Listeners\Response;

use Crell\KernelBench\EventsException\Events\ExceptionHandleResponse;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * This is to be in compliance with RFC 2616, Section 9.
 *
 * If the incoming request method is HEAD, we need to ensure that the response body
 * is empty as the request may fall back on a GET route handler due to FastRoute's
 * routing logic which could potentially append content to the response body
 * https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
 */
readonly class EnforceHead
{
    public function __construct(
        private StreamFactoryInterface $streamFactory,
    ) {}

    public function __invoke(ExceptionHandleResponse $event): void
    {
        if (strtoupper($event->request->getMethod()) === 'HEAD') {
            $emptyBody = $this->streamFactory->createStream();
            $event->setResponse($event->getResponse()->withBody($emptyBody));
        }
    }
}
