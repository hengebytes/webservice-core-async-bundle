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

### Logging a body the masking can't read into

The request and the response are logged as sent and received, then masked (`logs/mask_sensitive_data`). A body that is
a JWT or an encrypted payload hides what it carries from the masking. Log what it carries instead:

```php
// The request: what the log shows instead of the query and body sent
$request->setJson(['payload' => $jwt])->setLogBody(['payload' => $claims]);
```

```php
// The response: in a response modifier that runs before the log's (priority above 210)
$parsedResponse->logBody = json_encode($decodedClaims);
```

A response modifier that needs to know more about the call than its web service and action (which account's key
decodes the answer) reads it from the request's attributes, which are neither sent, logged nor part of the cache key:

```php
$request->setAttribute('hotelCode', $hotelCode);
// in the modifier
$hotelCode = $parsedResponse->mainAsyncResponse->WSRequest->getAttribute('hotelCode');
```
