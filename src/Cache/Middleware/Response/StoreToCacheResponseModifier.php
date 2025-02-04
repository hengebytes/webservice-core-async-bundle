<?php

namespace WebserviceCoreAsyncBundle\Cache\Middleware\Response;

use WebserviceCoreAsyncBundle\Cache\CacheManager;
use WebserviceCoreAsyncBundle\Callback\OnResponseReceivedCallback;
use WebserviceCoreAsyncBundle\Middleware\ResponseModificationInterface;
use WebserviceCoreAsyncBundle\Response\AsyncResponse;
use WebserviceCoreAsyncBundle\Response\ParsedResponse;
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