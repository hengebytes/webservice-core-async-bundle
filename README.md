# Webservice Asynchronous Bundle

This bundle provides a simple way to create asynchronous web services in Symfony.

## Installation

```bash
composer require hengebytes/webservice-core-async-bundle
```

## Configuration

```yaml
# config/packages/hb_webservice_core_async.yaml
hb_webservice_core_async:
    # Available options: 'symfony_params', 'settings_bundle', 'foo.bar.service_name'
    # If 'symfony_params' is used, the bundle will look for the parameters in the symfony param bag
    # If 'settings_bundle' is used, the bundle will look for the parameters in the hengebytes/settings-bundle
    # If 'foo.bar.service_name' is used, the bundle will look for the parameters in the service 'foo.bar.service_name' should implement Hengebytes\WebserviceCoreAsyncBundle\Provider\ParamsProviderInterface
    params_provider: ~ # default is null
    # by default the bundle will not use any cache
    cache:
        # second level cache adapter for persistent data default is null
        persistent_adapter: "app.cache.persistent"
        # first level cache adapter for runtime data default is null
        runtime_adapter: "app.cache.runtime"
    logs:
        # default is false if no parent is set
        enabled: true
        # configures the channel for the logs from monolog.yaml
        channel: webservice 
```

```yaml
# config/packages/monolog.yaml
monolog:
    channels:
        - webservice
    handlers:
        app:
            level: info
            type: stream
            path: '%kernel.logs_dir%/webservice.log'
            channels: [ webservice ]
```

## Add the bundle to your Kernel

```php
// config/bundles.php
return [
    // ...
    Hengebytes\WebserviceCoreAsyncBundle\HBWebserviceCoreAsyncBundle::class => ['all' => true],
];
```

## Usage

### Create a service

```php
// src/Service/MyService.php
namespace App\Service;

use Hengebytes\WebserviceCoreAsyncBundle\Handler\AsyncRequestHandler;
use Hengebytes\WebserviceCoreAsyncBundle\Response\AsyncResponse;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;use Hengebytes\WebserviceCoreAsyncBundle\Response\ModelPromise;

class MyService
{
    public function __construct(private readonly AsyncRequestHandler $rh) {
    }

    // sync example
    public function execute(array $data): array
    {
        $request = new WSRequest(
                'my_service',
                '/oauth/tokens',
                RequestMethodEnum::POST,
                'sub_service',
                fn(array $response) => $response['expires_in'] ?? 0
        );
        $request->setAuthBasic('username', 'password');
        $request->setHeaders([
            'Content-Type' => 'application/json',
        ]);
        $request->setBody(json_encode($data));

        $result = $this->rh->request($request);
        // $result is a promise that will be resolved toArray() when the request is completed
        // you can return promise and resolve it in the controller when needed
        $data = $result->toArray();
        
        return $data;
    }
    
    // async example
    public function executeAsync(array $data): AsyncResponse
    {
        $request = new WSRequest(
                'my_service',
                '/profile',
                RequestMethodEnum::POST,
                'sub_service',
        );
        $request->setAuthBasic('username', 'password');
        $request->setHeaders([
            'Content-Type' => 'application/json',
        ]);
        $request->setBody(json_encode($data));

        return $this->rh->request($request);
    }
    
    
    /**
    * async example with model
    * @param array $data
    * @return ModelPromise<SomeModel>
    * @throws \Hengebytes\WebserviceCoreAsyncBundle\Exception\ConnectionInitException
    */
    public function executeAsyncModel(array $data): ModelPromise
    {
        $request = new WSRequest(
                'my_service',
                '/profile',
                RequestMethodEnum::POST,
                'sub_service',
        );
        $request->setAuthBasic('username', 'password');
        $request->setHeaders([
            'Content-Type' => 'application/json',
        ]);
        $request->setBody(json_encode($data));

        return $this->rh->requestModel($request, SomeModel::class);
    }
}
```

### Create a controller

```php
// src/Controller/MyController.php
namespace App\Controller;

use App\Service\MyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MyController extends AbstractController
{
    public function __construct(private MyService $myService) {
    }

    public function index(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $result = $this->myService->execute($data);
        
        return $this->json(['result' => $result]);
    }
    
    public function async(Request $request): JsonResponse
    {
        $requestParams = $request->request->all();
        $requestParams['page'] = 1;
        $result1 = $this->myService->executeAsync($requestParams);
        
        $requestParams['page'] = 2;
        $result2 = $this->myService->executeAsync($requestParams);
        // do something else while the request is being processed
        
        $response1 = $result->toArray();
        $response2 = $result->toArray();
        
        return $this->json(['result' => array_merge($response1, $response2)]);
    }
    
    
    public function asyncWithModels(Request $request): JsonResponse
    {
        $requestParams = $request->request->all();
        $requestParams['page'] = 1;
        $result1 = $this->myService->executeAsyncModel($requestParams);
        
        $requestParams['page'] = 2;
        $result2 = $this->myService->executeAsyncModel($requestParams);
        // do something else while the request is being processed
        
        $model1 = $result->getModel();
        $model2 = $result->getModel();
        
        return $this->json([
            'page1' => $model1, 
            'page2' $model2
        ]);
    }
}
```

### Possible variants of configuration params in Settings when using `settings_bundle`

| Key                                                       | Value                 |
|-----------------------------------------------------------|-----------------------|
| `my_service/base_url`                                     | `http://example.com`  |
| OVERRIDE`my_service/my_subService/base_url`               | `http://example2.com` |
| `cache/my_service/customAction/ttl`                       | 600                   |
| IF NO CUSTOM ACTION`cache/my_service/action/ttl`          | 600                   |
| OVERRIDE`cache/my_service/my_subService/customAction/ttl` | 300                   |
| `timeout/my_service/customAction`                         | 15                    |
| OVERRIDE`timeout/my_service/my_subService/customAction`   | 25                    |
| `logs/store`                                              | 1                     |
| OVERRIDE`logs/store/customAction`                         | 0                     |
| `logs/mask_sensitive_data`                                | 1                     |
| `logs/mask_sensitive_member_pii`                          | 1                     |
| `logs/max_length`                                         | 900000                |

### Possible variants of configuration params in symfony configuration when using `symfony_params`

```yaml
parameters:
    hb_webservice_core_async.base_url.my_service: 'http://example.com'
    hb_webservice_core_async.base_url.my_service.my_subService: 'http://example2.com'
    hb_webservice_core_async.cache_ttl.my_service.customAction: 600
    hb_webservice_core_async.cache_ttl.my_service.action: 600
    hb_webservice_core_async.cache_ttl.my_service.my_subService.customAction: 300
    hb_webservice_core_async.timeout.my_service.customAction: 15
    hb_webservice_core_async.timeout.my_service.my_subService.customAction: 25
    hb_webservice_core_async.logs.store: 1
    hb_webservice_core_async.logs.store.customAction: 0
    hb_webservice_core_async.logs.mask_sensitive_data: 1
    hb_webservice_core_async.logs.mask_sensitive_member_pii: 1
    hb_webservice_core_async.logs.max_length: 900000
```

### Validate Response

To be used in parsing response and validate it to throw exception if needed

```php
// src/Middleware/MyResponseValidatorResponseModifier.php
namespace App\Middleware;

use Hengebytes\WebserviceCoreAsyncBundle\Middleware\ResponseModificationInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Hengebytes\WebserviceCoreAsyncBundle\Response\ParsedResponse;
// MyServiceResponseFailException should extend Hengebytes\WebserviceCoreAsyncBundle\Exception\ResponseFailException
use App\Exceptions\MyServiceResponseFailException;

class MyResponseValidatorResponseModifier implements ResponseModificationInterface
{
    public function modify(WSRequest $request, AsyncResponse $response): AsyncResponse
    {
        $response->addOnResponseReceivedCallback(new OnResponseReceivedCallback(
            function (ParsedResponse $parsedResponse) {
                if (isset($parsedResponse->response['errorKey'])) {
                    // this exception will be thrown when the response is received
                    $parsedResponse->exception = new MyServiceResponseFailException($parsedResponse->response['errorKey']);
                }
            }
        ));
    }

    public function supports(WSRequest $webService): bool
    {
        return $response->WSRequest->webService === 'my_service' 
        && $response->WSRequest->subService === 'my_subService';
    }
    
    public function getPriority(): int
    {
        return 0;
    }
}
```

### Current Request Modifier Priorities

Higher priority will be executed first

| Key                       | Value |
|---------------------------|-------|
| `BaseUrlRequestModifier`  | 0     |
| `CacheTTLRequestModifier` | 0     |

### Current Response Modifier Priorities

Higher priority will be executed first

| Key                                    | Value | Condition                                                             | Could be disabled |
|----------------------------------------|-------|-----------------------------------------------------------------------|-------------------|
| `LockResponseLoaderResponseModifier`   | 250   | `$response->isCached`                                                 | With Cache        |
| `ReloadLockedResponseResponseModifier` | 240   | `$response->isCached`                                                 | With Cache        |
| `ResponseParserResponseModifier`       | 220   | Always                                                                | -                 |
| `LogResponseModifier`                  | 210   | `!$response->isCached`                                                | With Logs         |
| `StoreToCacheResponseModifier`         | -200  | `!$response->isCached`                                                | With Cache        |
| `RequestUnlockResponseModifier`        | -210  | `!$response->isCached && $response->WSRequest->isCachable()`          | With Cache        |
| `InvalidateCacheResponseModifier`      | -220  | `!$response->isCached && !$response->WSRequest->isGETRequestMethod()` | With Cache        |

### You should create model provider for the model promise

it will be automatically registered based on interface implementation
and will be automatically called when the promise is resolved

```php
// src/Provider/MyModelProvider.php
namespace App\Provider;

use Hengebytes\WebserviceCoreAsyncBundle\Provider\ModelProviderInterface;
use App\Model\SomeModel;
use App\Model\SomeOtherModel;

class MyModelProvider implements ModelProviderInterface
{
    public function getModel(mixed $data, ModelProvider $modelProvider): object
    {
        $data = $data['data'] ?? [];
        $someOtherModel = $modelProvider->getModel(SomeOtherModel::class, $data['someOtherModel'] ?? []);
        $someModel = new SomeModel($data)
        $someModel->setSomeOtherModel($someOtherModel);

        return $someModel;
    }
}
```
