<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Handler;

use Hengebytes\WebserviceCoreAsyncBundle\Cache\CacheManager;
use Hengebytes\WebserviceCoreAsyncBundle\Exception\ConnectionInitException;
use Hengebytes\WebserviceCoreAsyncBundle\Middleware\RequestModification;
use Hengebytes\WebserviceCoreAsyncBundle\Middleware\ResponseModification;
use Hengebytes\WebserviceCoreAsyncBundle\Provider\ModelProvider;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Hengebytes\WebserviceCoreAsyncBundle\Response\AsyncResponse;
use Hengebytes\WebserviceCoreAsyncBundle\Response\ModelPromise;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @template T
 */
readonly class AsyncRequestHandler
{
    public function __construct(
        protected HttpClientInterface $client,
        protected RequestModification $requestModification,
        protected ResponseModification $responseModification,
        protected ?CacheManager $cacheManager = null,
        protected ModelProvider $modelProvider,
    ) {
    }

    /**
     * @throws ConnectionInitException
     */
    public function request(WSRequest $request): AsyncResponse
    {
        $this->requestModification->modifyRequest($request);

        $isCachableRequest = $request->isCachable() && $this->cacheManager !== null;
        if ($isCachableRequest && !$request->isSkipReadCache()) {
            $cacheResponse = $this->cacheManager->getByWSRequest($request);
            if (in_array($cacheResponse->getStatusCode(), [Response::HTTP_OK, Response::HTTP_LOCKED], true)) {
                $asyncResponse = new AsyncResponse($request, $cacheResponse, true);
                $this->responseModification->modifyResponse($asyncResponse);

                return $asyncResponse;
            }
        }

        try {
            $response = $this->client->request(
                $request->requestMethod->name, $request->action, $request->getOptions()
            );

            $asyncResponse = new AsyncResponse($request, $response);

            $this->responseModification->modifyResponse($asyncResponse);

            return $asyncResponse;
        } catch (TransportExceptionInterface $e) {
            if ($isCachableRequest) {
                $this->cacheManager->unlockWSRequest($request);
            }
            throw new ConnectionInitException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param WSRequest $request
     * @param class-string<T> $modelClass
     * @return ModelPromise<T>
     * @throws ConnectionInitException
     */
    public function requestModel(WSRequest $request, string $modelClass): ModelPromise
    {
        return new ModelPromise($this->request($request), $modelClass, $this->modelProvider);
    }

}