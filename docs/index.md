## Installation

**a)** Download the bundle

In the project directory:

```bash
composer require hengebytes/webservice-core-async-bundle
```

**b)** Enable the bundle

```php
// in config/bundles.php
return [
    // ...
    Hengebytes\WebserviceCoreAsyncBundle\HBWebserviceCoreAsyncBundle::class => ['all' => true],
];
```

**c)** Create configuration file. OPTIONAL

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
    # default is false if no parent is set
    enabled: false
  logs:
    # default is false if no parent is set
    enabled: false
```

Optional features dependencies
------------

- To use the Settings Bundle, you need to install the `hengebytes/setting-bundle` package. You can do this by running
  the following command:

```bash
composer require hengebytes/setting-bundle
```

- To use the Cache feature, you need to install the `symfony/cache` package. You can do this by running
  the following command:

```bash
composer require symfony/cache
```

- To use the Logs feature, you need to install the `symfony/monolog-bundle` package. You can do this by running
  the following command:

```bash
composer require symfony/monolog-bundle
```

- To use response filtering or modifying, you need to install the `hengebytes/response-filter-bundle` package. You can do this by running
  the following command:

```bash
composer require hengebytes/response-filter-bundle
```