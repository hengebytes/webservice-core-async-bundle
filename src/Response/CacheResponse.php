<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
readonly class CacheResponse implements ResponseInterface
{
    public function __construct(private ?array $data, private int $statusCode = Response::HTTP_OK)
    {
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(bool $throw = true): array
    {
        return $this->data['headers'] ?? [];
    }

    public function toArray(bool $throw = true): array
    {
        return $this->data['content'] ?? [];
    }

    public function cancel(): void
    {
    }

    public function getInfo(string $type = null): mixed
    {
        return [];
    }

    public function getContent(bool $throw = true): string
    {
        try {
            return json_encode($this->data['content'] ?? [], JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
        }
    }
}
