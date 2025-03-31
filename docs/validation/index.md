## Validate Response

This bundle is model based build so you have to validate the response. Actions that are not return model should be validated in response middleware.
___

### You should create response middleware for the response validation

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
