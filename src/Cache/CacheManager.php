<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Cache;

use Exception;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Hengebytes\WebserviceCoreAsyncBundle\Response\CacheResponse;
use Hengebytes\WebserviceCoreAsyncBundle\Tagger\CacheTagProvider;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\Response;

class CacheManager
{
    private const string LOCK_CACHE_KEY = 'as_ws_lp_';
    private static array $reservedSymbols = ['@', '{', '}', ')', '(', '/', ':'];
    private bool $readFromCache = true;

    public function __construct(
        private readonly CacheTagProvider $cacheTagProvider,
        private readonly ?TagAwareAdapterInterface $runtimeCache = null,
        private readonly ?TagAwareAdapterInterface $persistentCache = null,
    ) {
    }

    public function get(string $cacheId): mixed
    {
        if (!$this->readFromCache) {
            return null;
        }

        $response = null;
        $cacheId = $this->sanitizeString($cacheId);
        // read from runtime cache
        if ($this->runtimeCache) {
            $response = $this->innerGet($this->runtimeCache, $cacheId);
        }

        // read from persistent cache
        if (!$response && $this->persistentCache) {
            $response = $this->innerGet($this->persistentCache, $cacheId);
        }

        return $response;
    }

    public function getByWSRequest(WSRequest $request): CacheResponse
    {
        if (!$this->readFromCache || !$request->isCachable()) {
            return new CacheResponse(null, Response::HTTP_NOT_FOUND);
        }

        $cacheId = $this->buildCacheId($request);
        $response = $this->get($cacheId);
        if ($response) {
            return new CacheResponse($response);
        }

        if ($this->isLocked($cacheId)) {
            return new CacheResponse(
                ['headers' => [], 'content' => ['cacheId' => $cacheId]],
                Response::HTTP_LOCKED
            );
        }

        $this->lock($cacheId);

        return new CacheResponse(null, Response::HTTP_NOT_FOUND);
    }

    public function storeWSResponseByWSRequest(WSRequest $request, array $content, array $headers): void
    {
        $cacheId = $this->buildCacheId($request);
        $ttl = $request->getCacheTTL($content, $headers);
        if (!$ttl || $ttl <= 0) {
            $this->unlock($cacheId);

            return;
        }

        $cacheTags = $this->cacheTagProvider->getStoreTags($request, $content);

        $this->store($cacheId, ['content' => $content/*, 'headers' => $headers*/], $ttl, $cacheTags);

        $this->unlock($cacheId); // response has stored - unlock requests
    }

    public function unlockWSRequest(WSRequest $request): void
    {
        $this->unlock($this->buildCacheId($request)); // request has finished - unlock requests
    }

    public function store(string $id, mixed $val, int $ttl, array $tags = []): void
    {
        $id = $this->sanitizeString($id);
        if ($tags) {
            $tags = $this->sanitizeArray($tags);
        }
        // store to runtime
        if ($this->runtimeCache) {
            try {
                $this->innerStore($this->runtimeCache, $id, $val, $ttl, $tags);
            } catch (Exception|InvalidArgumentException|CacheException $exception) {
                // ignore cache unavailable
            }
        }

        // store to persistent
        if ($this->persistentCache) {
            try {
                $this->innerStore($this->persistentCache, $id, $val, $ttl, $tags);
            } catch (Exception|InvalidArgumentException|CacheException $exception) {
                // ignore cache unavailable
            }
        }
    }

    public function invalidateCacheByTags(array $tags): void
    {
        if (!$tags) {
            return;
        }
        $tags = $this->sanitizeArray($tags);

        if ($this->runtimeCache) {
            try {
                $this->runtimeCache->invalidateTags($tags);
            } catch (InvalidArgumentException) {
                // ignore
            }

        }
        if ($this->persistentCache) {
            try {
                $this->persistentCache->invalidateTags($tags);
            } catch (InvalidArgumentException) {
                // ignore
            }
        }
    }

    public function invalidateCacheById(string $id): void
    {
        $id = $this->sanitizeString($id);
        if ($this->runtimeCache) {
            try {
                $this->runtimeCache->deleteItem($id);
            } catch (InvalidArgumentException) {
                // ignore
            }

        }
        if ($this->persistentCache) {
            try {
                $this->persistentCache->deleteItem($id);
            } catch (InvalidArgumentException) {
                // ignore
            }
        }
    }

    public function invalidateCacheByWSRequest(WSRequest $request): void
    {
        $this->invalidateCacheById($this->buildCacheId($request));

        $tags = $this->cacheTagProvider->getInvalidateTags($request);
        if (!$tags) {
            return;
        }

        $this->invalidateCacheByTags($tags);
    }

    private function buildCacheId(WSRequest $request): string
    {
        return $request->webService . '-' . $request->subService . '-' . $request->action
            . md5(
                serialize($request->getRequestParams())
            );
    }

    private function sanitizeArray(array $array): array
    {
        return array_map(function ($el) {
            return $this->sanitizeString($el);
        }, $array);
    }

    private function sanitizeString(string $str): string
    {
        return str_replace(self::$reservedSymbols, '', $str);
    }

    private function innerGet(TagAwareAdapterInterface $cache, string $cacheId): mixed
    {
        try {
            if (!$cache->hasItem($cacheId)) {
                return null;
            }
        } catch (InvalidArgumentException) {
        }
        try {
            $response = $cache->getItem($cacheId)->get();
        } catch (InvalidArgumentException) {
            $response = null;
        }

        return $response;
    }

    /**
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    private function innerStore(
        TagAwareAdapterInterface $cache,
        string $id,
        mixed $val,
        int $ttl,
        array $tags = []
    ): void {
        $cacheItem = $cache->getItem($id);
        $cacheItem->expiresAfter($ttl);
        $cacheItem->set($val);
        if ($tags) {
            $cacheItem->tag($tags);
        }
        $cache->save($cacheItem);
    }

    private function lock(string $action): void
    {
        $this->store(self::LOCK_CACHE_KEY . $action, 1, 60);
    }

    private function unlock(string $action): void
    {
        $this->invalidateCacheById(self::LOCK_CACHE_KEY . $action);
    }

    private function isLocked(string $action): bool
    {
        $response = null;
        $cacheId = self::LOCK_CACHE_KEY . $action;
        $cacheId = $this->sanitizeString($cacheId);

        // read from persistent cache we should not use runtime cache for locking
        if (!$response && $this->persistentCache) {
            $response = $this->innerGet($this->persistentCache, $cacheId);
        }

        return $response === 1;
    }

    public function setReadFromCache(bool $readFromCache): void
    {
        $this->readFromCache = $readFromCache;
    }

    public function awaitUnlock(string $cacheId): void
    {
        // wait to unlock (it is not the first request)
        while ($this->isLocked($cacheId)) {
            sleep(1);
        }
    }
}