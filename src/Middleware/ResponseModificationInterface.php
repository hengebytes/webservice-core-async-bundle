<?php

namespace WebserviceCoreAsyncBundle\Middleware;

use WebserviceCoreAsyncBundle\Response\AsyncResponse;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('webservice_core_async.middleware.response')]
interface ResponseModificationInterface
{
    public function modify(AsyncResponse $response): void;

    public function supports(AsyncResponse $response): bool;

    public static function getPriority(): int;
}