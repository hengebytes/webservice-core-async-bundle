## Logs

The bundle provides a logging feature that allows you to log messages from the web service. The logs storing can be
configured with `monolog`.
____

### Configuration

Create new channel in `monolog.yaml` file and set the channel name to `webservice`. The logs will be stored in the
`webservice.log` file.

```yaml
# config/packages/monolog.yaml
monolog:
    channels:
        - webservice
    handlers:
        app:
            level: info
            type: stream
            path: '%kernel.logs_dir%/webservice.log'
            channels: [ webservice ]
```

Add the following configuration to your `hb_webservice_core_async.yaml` file to enable logging:

```yaml
hb_webservice_core_async:
    #...#
    logs:
        # default is false if no parent is set
        enabled: true
        # configures the channel for the logs from monolog.yaml
        channel: webservice 
```

#### To use the Logs feature, you need to install the `symfony/monolog-bundle` package.

You can do this by running the following command:

```bash
composer require symfony/monolog-bundle
```
