<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Middleware\Response;

use Hengebytes\WebserviceCoreAsyncBundle\Callback\OnResponseReceivedCallback;
use Hengebytes\WebserviceCoreAsyncBundle\Exception\ResponseFailException;
use Hengebytes\WebserviceCoreAsyncBundle\Middleware\ResponseModificationInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Response\AsyncResponse;
use Hengebytes\WebserviceCoreAsyncBundle\Response\ParsedResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class ResponseParserResponseModifier implements ResponseModificationInterface
{

    public function modify(AsyncResponse $response): void
    {
        $response->addOnResponseReceivedCallback(new OnResponseReceivedCallback(
            function (ParsedResponse $parsedResponse) {
                try {
                    $parsedResponse->statusCode = $parsedResponse->mainAsyncResponse->WSResponse->getStatusCode();
                    $parsedResponse->headers = $parsedResponse->mainAsyncResponse->WSResponse->getHeaders(false);
                    $parsedResponse->responseBody = $parsedResponse->mainAsyncResponse->WSResponse->getContent(false);
                    $parsedResponse->response = $parsedResponse->statusCode !== 204
                        ? $parsedResponse->mainAsyncResponse->WSResponse->toArray(false)
                        : [];
                } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|\Exception|TransportExceptionInterface $e) {
                    $parsedResponse->statusCode = 500;
                    $parsedResponse->exception = new ResponseFailException($e->getMessage());
                }
            }
        ));
    }

    public function supports(AsyncResponse $response): bool
    {
        return true;
    }

    public static function getPriority(): int
    {
        return 220;
    }
}