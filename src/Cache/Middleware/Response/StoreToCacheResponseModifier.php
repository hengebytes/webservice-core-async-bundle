<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Cache\Middleware\Response;

use Hengebytes\WebserviceCoreAsyncBundle\Cache\CacheManager;
use Hengebytes\WebserviceCoreAsyncBundle\Callback\OnResponseReceivedCallback;
use Hengebytes\WebserviceCoreAsyncBundle\Middleware\ResponseModificationInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Response\AsyncResponse;
use Hengebytes\WebserviceCoreAsyncBundle\Response\ParsedResponse;
use Symfony\Component\HttpFoundation\Response;

readonly class StoreToCacheResponseModifier implements ResponseModificationInterface
{
    public function __construct(private CacheManager $cacheManager)
    {
    }

    public function modify(AsyncResponse $response): void
    {
        $response->addOnResponseReceivedCallback(new OnResponseReceivedCallback(
            function (ParsedResponse $parsedResponse) {
                if (
                    $parsedResponse->mainAsyncResponse->isCached
                    || $parsedResponse->statusCode !== Response::HTTP_OK
                    || (!$parsedResponse->response && !$parsedResponse->headers)
                    || !$parsedResponse->mainAsyncResponse->WSRequest->isCachable()
                ) {
                    return;
                }
                $this->cacheManager->storeWSResponseByWSRequest(
                    $parsedResponse->mainAsyncResponse->WSRequest,
                    $parsedResponse->response,
                    $parsedResponse->headers
                );
                $parsedResponse->mainAsyncResponse->isCached = true;
            }
        ));
    }

    public function supports(AsyncResponse $response): bool
    {
        return !$response->isCached;
    }

    public static function getPriority(): int
    {
        return -200;
    }
}