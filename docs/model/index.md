## Models
Models are the data objects that are used to represent responses from the web service. The bundle provides a way to create models that can be used to represent the data returned from the web service.

## You should create model provider for the model promise

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
