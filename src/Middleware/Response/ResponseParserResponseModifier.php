<?php

namespace WebserviceCoreAsyncBundle\Middleware\Response;

use WebserviceCoreAsyncBundle\Callback\OnResponseReceivedCallback;
use WebserviceCoreAsyncBundle\Exception\ResponseFailException;
use WebserviceCoreAsyncBundle\Middleware\ResponseModificationInterface;
use WebserviceCoreAsyncBundle\Response\AsyncResponse;
use WebserviceCoreAsyncBundle\Response\ParsedResponse;
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