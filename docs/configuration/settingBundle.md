## Configuration parameters Settings Bundle
The bundle can also use the `hengebytes/settings-bundle` to store the configuration parameters. This means that the bundle will look for the parameters in the settings bundle. You can change this by setting the `params_provider` option in the configuration file.

```yaml
# config/packages/hb_webservice_core_async.yaml
hb_webservice_core_async:
    params_provider: settings_bundle
```



### Possible variants of configuration params in Settings Bundle

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
