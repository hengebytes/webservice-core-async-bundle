<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Request;

use Hengebytes\WebserviceCoreAsyncBundle\Enum\RequestMethodEnum;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WSRequest
{
    private ?string $customAction = null;
    private ?int $cacheTTL = null;
    private ?bool $skipReadCache = false;
    /**
     * @see HttpClientInterface for a description of each options.
     */
    private array $options = [];

    private array $cacheVaryingParams = [];

    private ?string $logBody = null;

    /** @var array<string, mixed> */
    private array $attributes = [];

    public function __construct(
        public readonly string $webService,
        public readonly string $action,
        public readonly RequestMethodEnum $requestMethod = RequestMethodEnum::GET,
        public readonly ?string $subService = null,
        public readonly ?\Closure $cashTTLCallback = null,
    ) {
    }

    public function getRequestParams(): array
    {
        return $this->options['query'] ?? $this->options['body'] ?? $this->options['json'] ?? [];
    }

    public function isCachable(): bool
    {
        return $this->cacheTTL > 0 || $this->cashTTLCallback !== null;
    }

    public function isCacheTTLSet(): bool
    {
        return $this->cacheTTL !== null || $this->cashTTLCallback !== null;
    }

    public function getCacheTTL(array $content = [], array $headers = []): ?int
    {
        return $this->cacheTTL ?? ($this->cashTTLCallback ? ($this->cashTTLCallback)($content, $headers) : null);
    }

    public function isGETRequestMethod(): bool
    {
        return $this->requestMethod === RequestMethodEnum::GET;
    }

    public function setCacheTTL(?int $cacheTTL): void
    {
        $this->cacheTTL = $cacheTTL;
    }

    public function getCustomAction(): string
    {
        return $this->customAction ?? $this->action;
    }

    public function setCustomAction(string $customAction): static
    {
        $this->customAction = $customAction;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setAuthBasic(string $user, #[\SensitiveParameter] string $password = ''): static
    {
        $this->options['auth_basic'] = $user;

        if ('' !== $password) {
            $this->options['auth_basic'] .= ':' . $password;
        }

        return $this;
    }

    public function setAuthBearer(#[\SensitiveParameter] string $token): static
    {
        $this->options['auth_bearer'] = $token;

        return $this;
    }

    public function setQuery(array $query): static
    {
        $this->options['query'] = $query;

        return $this;
    }

    public function setHeaders(iterable $headers): static
    {
        $this->options['headers'] = $headers;

        return $this;
    }

    /**
     * @param array|string|resource|\Traversable|\Closure $body
     */
    public function setBody(mixed $body): static
    {
        $this->options['body'] = $body;

        return $this;
    }

    public function setJson(mixed $json): static
    {
        $this->options['json'] = $json;

        return $this;
    }

    public function getJson(): ?array
    {
        return $this->options['json'] ?? null;
    }

    public function setBaseUri(string $uri): static
    {
        $this->options['base_uri'] = $uri;

        return $this;
    }

    public function isBaseUriSet(): bool
    {
        return isset($this->options['base_uri']);
    }

    public function isSkipReadCache(): ?bool
    {
        return $this->skipReadCache;
    }

    public function setSkipReadCache(?bool $skipReadCache): self
    {
        $this->skipReadCache = $skipReadCache;

        return $this;
    }

    public function setTimeout(float $timeout): static
    {
        $this->options['timeout'] = $timeout;

        return $this;
    }

    public function setCacheVaryingParams(array $params): static
    {
        $this->cacheVaryingParams = $params;

        return $this;
    }

    public function getCacheParams(): array
    {
        $requestParams = array_map(
            static function (mixed $params): mixed {
                if (is_array($params)) {
                    ksort($params);

                    return $params;
                }
                // A raw body (an XML or JSON string) is part of the key as it is; a stream or a generator can be
                // neither serialized nor read twice, its type stands for it
                if ($params instanceof \Closure || $params instanceof \Generator || is_resource($params)) {
                    return get_debug_type($params);
                }

                return $params;
            },
            [$this->options['query'] ?? [], $this->options['body'] ?? [], $this->options['json'] ?? []]
        );

        $varyingParams = $this->cacheVaryingParams;
        ksort($varyingParams);

        return array_merge($requestParams, $varyingParams);
    }

    /**
     * What the log shows instead of the query and body sent: for a body the log's masking can't read into
     * (a JWT, an encrypted payload), what it carries. Masked as any other request's
     */
    public function setLogBody(array|string|null $logBody): static
    {
        if (is_array($logBody)) {
            $logBody = (string)json_encode(
                $logBody,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }
        $this->logBody = $logBody;

        return $this;
    }

    /**
     * Context for the request's middleware (a response modifier reading which account a call was made for).
     * Neither sent, logged nor part of the cache key
     */
    public function setAttribute(string $name, mixed $value): static
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function getLogString(): string
    {
        if ($this->logBody !== null) {
            return $this->logBody;
        }

        $requestStringParts = [];
        $requestOptions = $this->getOptions();
        foreach (['query', 'json', 'body'] as $field) {
            if (!empty($requestOptions[$field])) {
                $value = $requestOptions[$field];
                try {
                    $requestStringParts[] = is_string($value)
                        ? $value
                        : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    $requestStringParts[] = $e->getMessage();
                }
            }
        }

        return implode("\n\n", $requestStringParts);
    }
}
