### Custom Request Handlers
You can create your own request handler by extending the `Hengebytes\WebserviceCoreAsyncBundle\Request\BaseRequestHandler` interface. This allows you to customize the way the bundle made requests.
___

```php
// src/Handler/MyCustomRequestHandler.php
namespace App\Handler;

use Hengebytes\WebserviceCoreAsyncBundle\Enum\RequestMethodEnum;
use Hengebytes\WebserviceCoreAsyncBundle\Handler\BaseRequestHandler;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MyCustomRequestHandler extends BaseRequestHandler
{
    /** @throws TransportExceptionInterface */
    protected function performRequest(WSRequest $request): ResponseInterface;
    {
        $options = $request->getOptions();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $options['headers']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($request->requestMethod === RequestMethodEnum::POST) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($options['json'], JSON_UNESCAPED_SLASHES));
        } else {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request->requestMethod->name);
        }
        if (isset($options['query'])) {
            $action .= '?' . http_build_query($options['query']);
        }
        curl_setopt($curl, CURLOPT_URL, $options['base_uri'] . $action);
        
        $response = curl_exec($curl);
        curl_close($curl);

        return new JsonMockResponse($response);
    }
}
```