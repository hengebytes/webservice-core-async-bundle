<?php

namespace WebserviceCoreAsyncBundle\Cache\Middleware\Response;

use WebserviceCoreAsyncBundle\Cache\CacheManager;
use WebserviceCoreAsyncBundle\Callback\OnResponseReceivedCallback;
use WebserviceCoreAsyncBundle\Middleware\ResponseModificationInterface;
use WebserviceCoreAsyncBundle\Response\AsyncResponse;
use WebserviceCoreAsyncBundle\Response\ParsedResponse;

readonly class RequestUnlockResponseModifier implements ResponseModificationInterface
{
    public function __construct(private CacheManager $cacheManager)
    {
    }

    public function modify(AsyncResponse $response): void
    {
        $response->addOnResponseReceivedCallback(new OnResponseReceivedCallback(
            function (ParsedResponse $parsedResponse) {
                $this->cacheManager->unlockWSRequest($parsedResponse->mainAsyncResponse->WSRequest);
            }
        ));
    }

    public function supports(AsyncResponse $response): bool
    {
        return !$response->isCached && $response->WSRequest->isCachable();
    }

    public static function getPriority(): int
    {
        return -210;
    }
}