<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Middleware\Request;

use Hengebytes\WebserviceCoreAsyncBundle\Middleware\RequestModifierInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Provider\ParamsProviderInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;

readonly class TimeoutRequestModifier implements RequestModifierInterface
{
    public function __construct(private ?ParamsProviderInterface $paramsProvider = null)
    {
    }

    public function modify(WSRequest $request): void
    {
        if (!$this->paramsProvider) {
            return;
        }

        $timeout = $this->paramsProvider->getTimeout($request);

        if ($timeout > 0) {
            $request->setTimeout($timeout);
        }
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