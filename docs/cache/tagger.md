## Cache Tagger
The bundle provides a caching feature that allows you to tag the web service responses. This is useful for 
invalidating the cache when the data changes. The bundle uses the `TagAwareAdapter` to tag the cache items.
___

### Example
```php

<?php

namespace Hengebytes\OhipBundle\Tagger;

use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Hengebytes\WebserviceCoreAsyncBundle\Tagger\TaggerInterface;

class CRMTagger implements TaggerInterface
{

    public function getStoreTags(WSRequest $request, mixed $response = null): array
    {
        return match ($request->getCustomAction()) {
            'fetchItem' => $this->getFetchItemTags($response),
            'searchItems' => $this->getSearchItemsTags($request, $response),
            default => [],
        };
    }

    public function getInvalidateTags(WSRequest $request, mixed $response = null): array
    {
        return match ($request->getCustomAction()) {
            'updateItem' => $this->getUpdateItemTags($request),
            'removeItem' => $this->getRemoveItemTags($request),
            default => [],
        };
    }

    public function supports(WSRequest $request): bool
    {
        return $request->webService === 'ohip' && $request->subService === 'crm';
    }

    private function getFetchItemTags(mixed $response): array
    {
        $tags = [];
        
        if (isset($response['id'])) {
            $tags[] = 'itemID' . $response['id'];
        }

        foreach ($response['groups'] ?? [] as $group) {
            $tags[] = 'grID' . $group['id'];
        }
        foreach ($response['details'] ?? [] as $detail) {
            $tags[] = 'detID' . $detail['id'];
        }

        return $tags;
    }

    private function getSearchItemsTags(WSRequest $request, mixed $response): array
    {
        $tags = [];
        $requestParams = $request->getRequestParams();
        if (isset($requestParams['name'])) {
            $tags[] = 'searchName' . $requestParams['name'];
        }
        if (isset($requestParams['groupId'])) {
            $tags[] = 'searchGroupID' . $requestParams['groupId'];
        }

        return $tags;
    }

    private function getUpdateItemTags(WSRequest $request): array
    {
        $tags = [];
        $requestParams = $request->getRequestParams();
        if (isset($requestParams['name'])) {
            $tags[] = 'searchName' . $requestParams['name'];
        }
        if (isset($requestParams['groupId'])) {
            $tags[] = 'searchGroupID' . $requestParams['groupId'];
            $tags[] = 'grID' . $requestParams['groupId'];
        }
        if (isset($requestParams['detID'])) {
            $tags[] = 'detID' . $requestParams['detID'];
        }

        return $tags;
    }

    private function getRemoveItemTagsTags(WSRequest $request)
    {
        $requestParams = $request->getRequestParams();
        if (isset($requestParams['id'])) {
            return ['itemID' . $requestParams['id']];
        }
    }
}
```