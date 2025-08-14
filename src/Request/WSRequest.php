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
        $query = $this->options['query'] ?? [];
        $body = $this->options['body'] ?? [];
        $json = $this->options['json'] ?? [];
        ksort($query);
        ksort($body);
        ksort($json);

        $requestParams = [$query, $body, $json];
        $varyingParams = $this->cacheVaryingParams;
        ksort($varyingParams);

        return array_merge($requestParams, $varyingParams);
    }

    public function getLogString(): string
    {
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
