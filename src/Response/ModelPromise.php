<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Response;

use Hengebytes\WebserviceCoreAsyncBundle\Exception\NotSupportedModelException;
use Hengebytes\WebserviceCoreAsyncBundle\Exception\ResponseFailException;
use Hengebytes\WebserviceCoreAsyncBundle\Provider\ModelProvider;

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
        private AsyncResponse $response,
        private string $className,
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

            $this->model = $this->modelProvider->getModel($this->className, $data, $this->response->WSRequest);
            unset($this->response, $this->className);
        }

        return $this->model;
    }
}