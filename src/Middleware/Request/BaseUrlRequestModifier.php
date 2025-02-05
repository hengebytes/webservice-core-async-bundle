<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Middleware\Request;

use Hengebytes\WebserviceCoreAsyncBundle\Middleware\RequestModifierInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Hengebytes\WebserviceCoreAsyncBundle\Provider\ParamsProviderInterface;
use RuntimeException;

readonly class BaseUrlRequestModifier implements RequestModifierInterface
{
    public function __construct(private ?ParamsProviderInterface $paramsProvider = null)
    {
    }

    public function modify(WSRequest $request): void
    {
        if ($request->isBaseUriSet()) {
            return;
        }
        if (!$this->paramsProvider) {
            throw new RuntimeException('Please set base URL in request or provide ParamsProvider in backend/config/packages/webservice_core_async.yaml');
        }

        $uri = $this->paramsProvider->getBaseURL($request);

        if ($uri === null) {
            throw new RuntimeException(
                'Base URL not found for ' . $request->webService
                . ($request->subService ? '/' . $request->subService : '')
            );
        }

        $request->setBaseUri($uri);
    }

    public function supports(WSRequest $request): bool
    {
        return true;
    }

    public static function getPriority(): int
    {
        return 0;
    }
}