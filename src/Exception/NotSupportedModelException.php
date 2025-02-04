<?php

namespace WebserviceCoreAsyncBundle\Exception;

use Exception;

class NotSupportedModelException extends Exception
{
    public function __construct($model)
    {
        parent::__construct(sprintf('Model %s is not supported', $model));
    }
}