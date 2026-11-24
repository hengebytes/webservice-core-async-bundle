<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Middleware;

use Hengebytes\WebserviceCoreAsyncBundle\Exception\ResponseFailException;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Hengebytes\WebserviceCoreAsyncBundle\Response\ParsedResponse;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('webservice_core_async.response_body_parser')]
interface ResponseBodyParserInterface
{
    public function supports(WSRequest $request): bool;

    /** @throws ResponseFailException */
    public function parse(ParsedResponse $parsedResponse): void;
}
