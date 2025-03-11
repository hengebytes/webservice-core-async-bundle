<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Response;

use Exception;
use Hengebytes\WebserviceCoreAsyncBundle\Callback\OnResponseReceivedCallback;
use Hengebytes\WebserviceCoreAsyncBundle\Exception\ResponseFailException;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AsyncResponse
{
    /** @var OnResponseReceivedCallback[] */
    private array $onResponseReceivedCallbacks = [];
    private bool $callbacksExecuted = false;
    private ?ParsedResponse $parsedResponse = null;

    public function __construct(
        public readonly WSRequest $WSRequest,
        public ResponseInterface $WSResponse,
        public bool $isCached = false
    ) {
    }

    public function getStatusCode(): int
    {
        try {
            $this->processCallbacks();
        } catch (Exception) {
            try {
                // original status code: $this->WSResponse->getStatusCode();
                return $this->WSResponse->getStatusCode();
            } catch (TransportExceptionInterface) {
                return Response::HTTP_SERVICE_UNAVAILABLE;
            }
        }

        return $this->parsedResponse->statusCode;
    }

    /**
     * @throws ResponseFailException
     */
    public function getHeaders(): array
    {
        $this->processCallbacks();

        if ($this->parsedResponse->exception) {
            /** @throws ResponseFailException */
            throw $this->parsedResponse->exception;
        }

        return $this->parsedResponse->headers;
    }

    /**
     * @throws ResponseFailException
     */
    public function toArray(): array
    {
        $this->processCallbacks();

        if ($this->parsedResponse->exception) {
            /** @throws ResponseFailException */
            throw $this->parsedResponse->exception;
        }

        return $this->parsedResponse->response;
    }

    public function addOnResponseReceivedCallback(OnResponseReceivedCallback $onResponseReceived): void
    {
        $this->onResponseReceivedCallbacks[] = $onResponseReceived;
    }

    protected function processCallbacks(): void
    {
        if (!$this->parsedResponse) {
            $this->parsedResponse = new ParsedResponse($this);
        }

        if (!$this->callbacksExecuted) {
            foreach ($this->onResponseReceivedCallbacks as $callback) {
                $callback($this->parsedResponse);
            }
            $this->callbacksExecuted = true;
        }
    }
}