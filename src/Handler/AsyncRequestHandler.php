<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Handler;

use Hengebytes\WebserviceCoreAsyncBundle\Cache\CacheManager;
use Hengebytes\WebserviceCoreAsyncBundle\Middleware\RequestModification;
use Hengebytes\WebserviceCoreAsyncBundle\Middleware\ResponseModification;
use Hengebytes\WebserviceCoreAsyncBundle\Provider\ModelProvider;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class AsyncRequestHandler extends BaseRequestHandler
{
    public function __construct(
        protected HttpClientInterface $client,
         RequestModification $requestModification,
         ResponseModification $responseModification,
         ModelProvider $modelProvider,
         ?CacheManager $cacheManager = null,
    ) {
        parent::__construct($requestModification, $responseModification, $modelProvider, $cacheManager);
    }

    protected function performRequest(WSRequest $request): ResponseInterface
    {
        return $this->client->request($request->requestMethod->name, $request->action, $request->getOptions());
    }
}