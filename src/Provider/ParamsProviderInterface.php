<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Provider;

use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;

interface ParamsProviderInterface
{
    public function getCacheTTL(WSRequest $request): ?int;

    public function getBaseURL(WSRequest $request): ?string;

    public function getTimeout(WSRequest $request): float;

    public function getLogParameterValue(string $name, ?string $defaultValue = null): ?string;

}