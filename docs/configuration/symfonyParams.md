## Configuration with `symfony_params`

 The bundle can also use the `symfony_params` to store the configuration parameters. This means that the bundle will look for the parameters in the symfony param bag. You can change this by setting the `params_provider` option in the configuration file.

```yaml
# config/packages/hb_webservice_core_async.yaml
hb_webservice_core_async:
    params_provider: symfony_params
```

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
