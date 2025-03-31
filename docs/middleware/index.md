
### Default Bundle Request Modifier Priorities

Higher priority will be executed first

| Key                       | Value |
|---------------------------|-------|
| `BaseUrlRequestModifier`  | 0     |
| `CacheTTLRequestModifier` | 0     |

### Default Bundle Response Modifier Priorities

Higher priority will be executed first

| Key                                    | Value | Condition                                                             | Could be disabled |
|----------------------------------------|-------|-----------------------------------------------------------------------|-------------------|
| `LockResponseLoaderResponseModifier`   | 250   | `$response->isCached`                                                 | With Cache        |
| `ReloadLockedResponseResponseModifier` | 240   | `$response->isCached`                                                 | With Cache        |
| `ResponseParserResponseModifier`       | 220   | Always                                                                | -                 |
| `LogResponseModifier`                  | 210   | `!$response->isCached`                                                | With Logs         |
| `StoreToCacheResponseModifier`         | -200  | `!$response->isCached`                                                | With Cache        |
| `RequestUnlockResponseModifier`        | -210  | `!$response->isCached && $response->WSRequest->isCachable()`          | With Cache        |
| `InvalidateCacheResponseModifier`      | -220  | `!$response->isCached && !$response->WSRequest->isGETRequestMethod()` | With Cache        |
