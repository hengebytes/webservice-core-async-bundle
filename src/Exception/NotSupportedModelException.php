<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Exception;

use RuntimeException;

class NotSupportedModelException extends RuntimeException
{
    public function __construct($model)
    {
        parent::__construct(sprintf('Model %s is not supported', $model));
    }
}