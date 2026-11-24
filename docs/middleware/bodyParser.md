## Responses that are not JSON

`ResponseParserResponseModifier` decodes every answer as JSON. A body that is not JSON (XML, a JWT, a signed or
encrypted payload, plain text) is a failure there: the caller gets a `ResponseFailException` and status `500`.

For a web service that answers in another format, implement `ResponseBodyParserInterface`. The parser that supports a
request reads its answer instead of the JSON decoding, so the real status is kept and there is no failure to undo in a
later response modifier.

```php
// src/Middleware/MyServiceResponseBodyParser.php
namespace App\Middleware;

use Hengebytes\WebserviceCoreAsyncBundle\Exception\ResponseFailException;
use Hengebytes\WebserviceCoreAsyncBundle\Middleware\ResponseBodyParserInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Hengebytes\WebserviceCoreAsyncBundle\Response\ParsedResponse;

class MyServiceResponseBodyParser implements ResponseBodyParserInterface
{
    public function supports(WSRequest $request): bool
    {
        return $request->webService === 'my_service';
    }

    public function parse(ParsedResponse $parsedResponse): void
    {
        // statusCode, headers and responseBody are filled as received
        if ($parsedResponse->statusCode !== 200) {
            throw new ResponseFailException('My service answered ' . $parsedResponse->statusCode);
        }

        $xml = @simplexml_load_string($parsedResponse->responseBody);
        if ($xml === false) {
            throw new ResponseFailException('My service answered with malformed XML');
        }

        // what AsyncResponse::toArray() returns and the model providers receive
        $parsedResponse->response = ['id' => (string)$xml->id];
    }
}
```

The interface is autoconfigured (tag `webservice_core_async.response_body_parser`): with `autoconfigure: true` the
class above needs no service definition.

### Rules

- `supports()` gets only the request (the answer may not have arrived yet): match on web service, action or a
  [request attribute](../logs/index.md#request-attributes). The first parser that supports a request is used; requests
  no parser supports are decoded as JSON.
- `parse()` is called for every answer, whatever its status and even with an empty body, with `statusCode`, `headers`
  and `responseBody` filled.
- A `ResponseFailException` thrown from `parse()` becomes `$parsedResponse->exception`, the real status kept. Other
  exceptions are not caught.
- `parse()` is not called when there is no answer (timeout, connection failure: `500` as before) or for an answer
  served from the cache, which holds the parsed data.
- It runs inside `ResponseParserResponseModifier` (220): before the log (210) and the validators.
