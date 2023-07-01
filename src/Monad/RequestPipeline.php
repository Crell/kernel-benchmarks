<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad;

use Crell\KernelBench\Monad\Errors\Error;
use Crell\KernelBench\Monad\Pipes\ActionPipe;
use Crell\KernelBench\Monad\Pipes\ErrorPipe;
use Crell\KernelBench\Monad\Pipes\RequestPipe;
use Crell\KernelBench\Monad\Pipes\ResponsePipe;
use Crell\KernelBench\Monad\Pipes\ResultPipe;
use Crell\KernelBench\Monad\Router\RequestFormat;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class RequestPipeline
{
    private ServerRequestInterface $prevRequest;

    public function __construct(public object $val) {}

    public function request(RequestPipe $c): self
    {
        if (! $this->val instanceof ServerRequestInterface) {
            return $this;
        }

        $ret = new self($c($this->val));
        $ret->prevRequest = $this->val;
        return $ret;
    }

    public function response(ResponsePipe $c): self
    {
        if (! $this->val instanceof ResponseInterface) {
            return $this;
        }

        $ret = new self($c($this->val, $this->prevRequest));
        $ret->prevRequest = $this->prevRequest;
        return $ret;
    }

    public function action(ActionPipe $c): self
    {
        if (!$this->val instanceof ServerRequestInterface) {
            return $this;
        }

        $ret = new self($c($this->val));
        $ret->prevRequest = $this->prevRequest;
        return $ret;
    }

    public function result(string $format, ResultPipe $c, ?string $type = null): self
    {
        if ($this->val instanceof ResponseInterface) {
            return $this;
        }

        if ($this->val instanceof Error) {
            return $this;
        }

        if ($type && ! $this->val instanceof $type) {
            return $this;
        }

        $requestFormat = $this->prevRequest->getAttribute(RequestFormat::class);
        if ($requestFormat->accept !== $format) {
            return $this;
        }

        $ret = new self($c($this->val, $this->prevRequest));
        $ret->prevRequest = $this->prevRequest;
        return $ret;
    }

    /**
     * @param class-string $type
     */
    public function error(string $type, ErrorPipe $c): self
    {
        // The extra type check for Error is mostly for static analysis.
        if ($this->val instanceof Error && $this->val instanceof $type) {
            $ret = new self($c($this->val, $this->prevRequest));
            $ret->prevRequest = $this->prevRequest;
            return $ret;
        }
        return $this;
    }
}
