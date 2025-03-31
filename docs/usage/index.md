
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
