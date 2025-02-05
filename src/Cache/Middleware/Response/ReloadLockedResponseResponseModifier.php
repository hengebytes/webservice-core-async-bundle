<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Cache\Middleware\Response;

use Hengebytes\WebserviceCoreAsyncBundle\Callback\OnResponseReceivedCallback;
use Hengebytes\WebserviceCoreAsyncBundle\Handler\AsyncRequestHandler;
use Hengebytes\WebserviceCoreAsyncBundle\Middleware\ResponseModificationInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Response\AsyncResponse;
use Hengebytes\WebserviceCoreAsyncBundle\Response\ParsedResponse;
use Symfony\Component\HttpFoundation\Response;

readonly class ReloadLockedResponseResponseModifier implements ResponseModificationInterface
{
    public function __construct(private AsyncRequestHandler $requestHandler)
    {
    }

    public function modify(AsyncResponse $response): void
    {
        $response->addOnResponseReceivedCallback(new OnResponseReceivedCallback(
            function (ParsedResponse $parsedResponse) {
                if (
                    $parsedResponse->response
                    || $parsedResponse->headers
                    || $parsedResponse->mainAsyncResponse->WSResponse->getStatusCode() !== Response::HTTP_LOCKED
                ) {
                    return;
                }
                $parsedResponse->mainAsyncResponse->WSResponse = $this->requestHandler->request($parsedResponse->mainAsyncResponse->WSRequest)->WSResponse;
            }
        ));
    }

    public function supports(AsyncResponse $response): bool
    {
        return $response->isCached && !$response->WSRequest->isSkipReadCache();
    }

    public static function getPriority(): int
    {
        return 240;
    }
}