<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Cache\Middleware\Response;

use Hengebytes\WebserviceCoreAsyncBundle\Cache\CacheManager;
use Hengebytes\WebserviceCoreAsyncBundle\Callback\OnResponseReceivedCallback;
use Hengebytes\WebserviceCoreAsyncBundle\Logs\MonologLogHandler;
use Hengebytes\WebserviceCoreAsyncBundle\Middleware\ResponseModificationInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Response\AsyncResponse;
use Hengebytes\WebserviceCoreAsyncBundle\Response\CacheResponse;
use Hengebytes\WebserviceCoreAsyncBundle\Response\ParsedResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;

readonly class LockResponseLoaderResponseModifier implements ResponseModificationInterface
{
    public function __construct(
        private CacheManager $cacheManager,
        private ?MonologLogHandler $logHandler = null,
    ) {
    }

    public function modify(AsyncResponse $response): void
    {
        $response->addOnResponseReceivedCallback(new OnResponseReceivedCallback(
            function (ParsedResponse $parsedResponse) {
                if (
                    !$parsedResponse->mainAsyncResponse->isCached
                    || $parsedResponse->mainAsyncResponse->WSResponse->getStatusCode() !== Response::HTTP_LOCKED
                ) {
                    return;
                }
                try {
                    $cacheId = $parsedResponse->mainAsyncResponse->WSResponse->toArray(false)['cacheId'] ?? null;
                } catch (DecodingExceptionInterface) {
                    $cacheId = null;
                }
                if (!$cacheId) {
                    return;
                }
                $waited = $this->cacheManager->awaitUnlock($cacheId);
                $this->logHandler?->writeLockWaitLog($parsedResponse->mainAsyncResponse->WSRequest, $waited);
                $cachedContent = $this->cacheManager->get($cacheId);
                if ($cachedContent) {
                    $parsedResponse->mainAsyncResponse->WSResponse = new CacheResponse($cachedContent);
                    $parsedResponse->mainAsyncResponse->isCached = true;
                }
            }
        ));
    }

    public function supports(AsyncResponse $response): bool
    {
        return $response->isCached;
    }

    public static function getPriority(): int
    {
        return 250;
    }
}