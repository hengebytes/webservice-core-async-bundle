<?php

namespace WebserviceCoreAsyncBundle\Response;

use WebserviceCoreAsyncBundle\Exception\NotSupportedModelException;
use WebserviceCoreAsyncBundle\Provider\ModelProvider;
use WebserviceCoreAsyncBundle\Exception\ResponseFailException;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;

/**
 * @template T
 */
class ModelPromise
{
    /**
     * @var T
     */
    private mixed $model = null;

    /**
     * @param AsyncResponse $response
     * @param class-string<T> $className
     * @param ModelProvider $modelProvider
     */
    public function __construct(
        readonly private AsyncResponse $response,
        readonly private string $className,
        readonly private ModelProvider $modelProvider
    ) {
    }

    /**
     * @return T
     * @throws ResponseFailException
     * @throws NotSupportedModelException
     */
    public function getModel()
    {
        if (!$this->model) {
            $data = $this->response->toArray();

            $this->model = $this->modelProvider->getModel($this->className, $data);
        }

        return $this->model;
    }
}