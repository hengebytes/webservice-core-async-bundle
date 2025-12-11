<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Response;

use Hengebytes\WebserviceCoreAsyncBundle\Exception\ResponseFailException;

class ParsedResponse
{
    public ?ResponseFailException $exception = null;
    public array $response = [];
    public array $headers = [];
    public string $responseBody = '';
    public int $statusCode = 0;

    public function __construct(public readonly AsyncResponse $mainAsyncResponse)
    {
    }

    public function getLogString(bool $withHeaders = false): string
    {
        $responseInfo = $this->statusCode;
        if ($withHeaders && $this->headers) {
            try {
                $responseInfo .= " - "
                    . json_encode(
                        $this->headers,
                        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                    );
            } catch (\JsonException $e) {
                $responseInfo .= " - " . $e->getMessage();
            }
        }

        return $responseInfo . "\n\n" . $this->responseBody;
    }
}
