<?php


namespace WebserviceCoreAsyncBundle\Response;

use WebserviceCoreAsyncBundle\Callback\OnResponseReceivedCallback;
use WebserviceCoreAsyncBundle\Exception\ResponseFailException;
use WebserviceCoreAsyncBundle\Request\WSRequest;
use Exception;
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
        if (!$this->parsedResponse) {
            $this->parsedResponse = new ParsedResponse($this);
        }

        if ($this->callbacksExecuted) {
            return $this->parsedResponse->statusCode;
        }
        try {
            foreach ($this->onResponseReceivedCallbacks as $callback) {
                $callback($this->parsedResponse);
            }
            $this->callbacksExecuted = true;
        } catch (Exception) {
            return $this->WSResponse->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            return Response::HTTP_SERVICE_UNAVAILABLE;
        }

        return $this->parsedResponse->statusCode;
    }

    /**
     * @throws ResponseFailException
     */
    public function getHeaders(): array
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
        if (!$this->parsedResponse) {
            $this->parsedResponse = new ParsedResponse($this);
        }
        if (!$this->callbacksExecuted) {
            foreach ($this->onResponseReceivedCallbacks as $callback) {
                $callback($this->parsedResponse);
            }
            $this->callbacksExecuted = true;
        }

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
}