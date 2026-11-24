<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Middleware\Response;

use Hengebytes\WebserviceCoreAsyncBundle\Callback\OnResponseReceivedCallback;
use Hengebytes\WebserviceCoreAsyncBundle\Exception\ResponseFailException;
use Hengebytes\WebserviceCoreAsyncBundle\Middleware\ResponseBodyParserInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Middleware\ResponseModificationInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Hengebytes\WebserviceCoreAsyncBundle\Response\AsyncResponse;
use Hengebytes\WebserviceCoreAsyncBundle\Response\CacheResponse;
use Hengebytes\WebserviceCoreAsyncBundle\Response\ParsedResponse;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class ResponseParserResponseModifier implements ResponseModificationInterface
{
    /** @param iterable<ResponseBodyParserInterface> $bodyParsers */
    public function __construct(
        #[AutowireIterator('webservice_core_async.response_body_parser')]
        private iterable $bodyParsers = [],
    ) {
    }

    public function modify(AsyncResponse $response): void
    {
        $response->addOnResponseReceivedCallback(new OnResponseReceivedCallback(
            function (ParsedResponse $parsedResponse) {
                $WSResponse = $parsedResponse->mainAsyncResponse->WSResponse;
                try {
                    $parsedResponse->statusCode = $WSResponse->getStatusCode();
                    $parsedResponse->headers = $WSResponse->getHeaders(false);
                    $parsedResponse->responseBody = $WSResponse->getContent(false);
                    // Cached data is already parsed
                    $bodyParser = $WSResponse instanceof CacheResponse
                        ? null
                        : $this->getBodyParser($parsedResponse->mainAsyncResponse->WSRequest);
                    if ($bodyParser === null) {
                        $parsedResponse->response = $parsedResponse->statusCode !== 204
                            ? $WSResponse->toArray(false)
                            : [];

                        return;
                    }
                } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|\Exception|TransportExceptionInterface $e) {
                    $parsedResponse->statusCode = 500;
                    $parsedResponse->exception = new ResponseFailException($e->getMessage());

                    return;
                }

                try {
                    $bodyParser->parse($parsedResponse);
                } catch (ResponseFailException $e) {
                    $parsedResponse->exception = $e;
                }
            }
        ));
    }

    private function getBodyParser(WSRequest $request): ?ResponseBodyParserInterface
    {
        foreach ($this->bodyParsers as $bodyParser) {
            if ($bodyParser->supports($request)) {
                return $bodyParser;
            }
        }

        return null;
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