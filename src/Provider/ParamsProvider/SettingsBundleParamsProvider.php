<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Provider\ParamsProvider;

use Hengebytes\SettingBundle\Interfaces\SettingHandlerInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Provider\ParamsProviderInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;

readonly class SettingsBundleParamsProvider implements ParamsProviderInterface
{
    public function __construct(private SettingHandlerInterface $settingHandler)
    {
    }

    public function getCacheTTL(WSRequest $request): ?int
    {
        if ($request->subService) {
            $ttl = (int)$this->settingHandler->get(
                'cache/' . $request->webService . '/' . $request->subService . '/' . $request->getCustomAction() . '/ttl', '0'
            );
        }

        $ttl ??= (int)$this->settingHandler->get(
            'cache/' . $request->webService . '/' . $request->getCustomAction() . '/ttl', '0'
        );

        return $ttl;
    }

    public function getBaseURL(WSRequest $request): ?string
    {
        if ($request->subService) {
            $uri = $this->settingHandler->get($request->webService . '/' . $request->subService . '/base_url');
        }

        $uri ??= $this->settingHandler->get($request->webService . '/base_url');

        return $uri;
    }

    public function getTimeout(WSRequest $request): float
    {
        if ($request->subService) {
            $timeout = $this->settingHandler->get(
                'timeout/' . $request->webService . '/' . $request->subService . '/' . $request->getCustomAction()
            );
        }

        $timeout ??= $this->settingHandler->get(
            'timeout/' . $request->webService . '/' . $request->getCustomAction()
        );

        return (float)$timeout;
    }

    public function getLogParameterValue(string $name, ?string $defaultValue = null): ?string
    {
        return $this->settingHandler->get('logs/' . $name, $defaultValue);
    }
}