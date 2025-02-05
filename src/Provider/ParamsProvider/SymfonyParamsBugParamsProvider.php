<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Provider\ParamsProvider;

use Hengebytes\WebserviceCoreAsyncBundle\Provider\ParamsProviderInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly class SymfonyParamsBugParamsProvider implements ParamsProviderInterface
{
    public function __construct(private ParameterBagInterface $parameterBag)
    {
    }

    public function getCacheTTL(WSRequest $request): ?int
    {
        $baseParamName = 'cache_ttl.' . $request->webService;

        return $this->getParameterValueForRequest($request, $baseParamName);
    }

    public function getBaseURL(WSRequest $request): ?string
    {
        $baseParamName = 'base_url.' . $request->webService;

        return $this->getParameterValueForRequest($request, $baseParamName);
    }

    public function getTimeout(WSRequest $request): float
    {
        $baseParamName = 'timeout.' . $request->webService;

        $timeout = $this->getParameterValueForRequest($request, $baseParamName);

        return $timeout ?: 0;
    }

    public function getLogParameterValue(string $name, ?string $defaultValue = null): ?string
    {
        $paramName = 'hb_webservice_core_async.logs.' . $name;

        $val = $this->parameterBag->has($paramName) ? $this->parameterBag->get($paramName) : null;

        return $val ?: $defaultValue;
    }

    private function getParameterValueForRequest(
        WSRequest $request, string $namePrefix
    ): array|bool|string|int|float|\UnitEnum|null {
        $baseName = 'hb_webservice_core_async.' . $namePrefix;
        if (
            $request->subService
            && $this->parameterBag->has($baseName . $request->subService)
        ) {
            return $this->parameterBag->get($baseName . $request->subService);
        }

        if ($this->parameterBag->has($baseName)) {
            return $this->parameterBag->get($baseName);
        }

        return null;
    }

}