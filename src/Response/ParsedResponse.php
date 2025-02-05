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

}