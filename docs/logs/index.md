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

### Masking

With `logs/mask_sensitive_data` on, the request and the response are masked before they are logged: credentials,
card data and, with `logs/mask_sensitive_member_pii`, the member's personal data. A token carrying base64 JSON (a JWT,
a JWE, a signed request) is masked whole, as its payload can't be masked field by field.

To keep what such a token carries readable, log it as request log params. They are appended to the logged request
and masked like it, but neither sent nor part of the cache key:

```php
$request->setJson(['payload' => $jwt])->setLogParams($claims);
// logged: {"payload":"*"} followed by the claims, masked field by field
```

### Request attributes

A response modifier that needs to know more about the call than its web service and action (which account's key
decodes the answer) reads it from the request's attributes, which are neither sent, logged nor part of the cache key:

```php
$request->setAttribute('hotelCode', $hotelCode);
// in the modifier
$hotelCode = $parsedResponse->mainAsyncResponse->WSRequest->getAttribute('hotelCode');
```
