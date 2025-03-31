## Configuration

The bundle requires a bunch of configuration parameters to be set.
___

You can select the configuration provider you want to use. The bundle supports the following providers:
- `symfony_params`: The bundle will look for the parameters in the Symfony param bag.
- `settings_bundle`: The bundle will look for the parameters in the `hengebytes/settings-bundle`.
- `foo.bar.service_name`: The bundle will look for the parameters in the service `foo.bar.service_name`. This service should implement `Hengebytes\WebserviceCoreAsyncBundle\Provider\ParamsProviderInterface`.

____
### Configuration parameters
- Configure with [Symfony parameters](symfonyParams.md)
- Configure with [Settings Bundle](settingBundle.md)
- Configure with [Service](service.md)

#