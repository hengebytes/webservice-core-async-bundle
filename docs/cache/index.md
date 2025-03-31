## Cache Configuration
The bundle provides a caching feature that allows you to cache the web service responses.
___
### Framework Configuration 
```yaml
# config/packages/cache.yaml
framework:
    cache:
        # Redis
        app: cache.adapter.redis
```

### Service Configuration

```yaml
# config/services.yaml

    app.cache.persistent:
        class: Symfony\Component\Cache\Adapter\TagAwareAdapter
        arguments: [ '@cache.app' ]
    app.cache.runtime:
        class: Symfony\Component\Cache\Adapter\TagAwareAdapter
        arguments: [ '@cache.adapter.filesystem' ]

```

### Bundle Configuration

```yaml
# config/packages/hb_webservice_core_async.yaml
hb_webservice_core_async:
    # ... #
    cache:
        # second level cache adapter for persistent data default is null
        persistent_adapter: "app.cache.persistent"
        # first level cache adapter for runtime data default is null
        runtime_adapter: "app.cache.runtime"
```
___
## Additional configuration
- [Cache Tagger Configuration](tagger.md) 