## Configuration with custom service

 You can create your own service that implements the `Hengebytes\WebserviceCoreAsyncBundle\Provider\ParamsProviderInterface` interface. This allows you to customize the way the bundle retrieves the configuration parameters.
 You can change this by setting the `params_provider` option in the configuration file.
___

### Example of custom service

```php
// src/Service/ParamsProvider.php

namespace App\Service;

use Hengebytes\WebserviceCoreAsyncBundle\Provider\ParamsProviderInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use App\Reposytory\SettingsRepository;

class ParamsProvider implements ParamsProviderInterface {

    public function __construct(private SettingsRepository $repository)
    {
    }

    public function getCacheTTL(WSRequest $request): ?int
    {
        $entity = $this->repository->findOneBy([
            'type' => 'cache',
            'action' => $request->getCustomAction(),
            'service' => $request->webService,
            'subService' => $request->subService,
        ]);

        return $entity?->getTtl();
    }

    public function getBaseURL(WSRequest $request): ?string
    {
        $entity = $this->repository->findOneBy([
            'type' => 'base_url',
            'action' => $request->getCustomAction(),
            'service' => $request->webService,
            'subService' => $request->subService,
        ]);

        return $entity?->getBaseUrl();
    }

    public function getTimeout(WSRequest $request): float
    {
        $entity = $this->repository->findOneBy([
            'type' => 'timeout',
            'action' => $request->getCustomAction(),
            'service' => $request->webService,
            'subService' => $request->subService,
        ]);

        return (float)$entity?->getTimeout();
    }

    public function getLogParameterValue(string $name, ?string $defaultValue = null): ?string
    {
        $entity = $this->repository->findOneBy([
            'type' => 'logs',
        ]);

        return $entity?->get($name) ?? $defaultValue;
    }
}


```
### Configuration with custom service

```yaml
# config/packages/hb_webservice_core_async.yaml
hb_webservice_core_async:
    params_provider: App\Service\ParamsProvider
```
