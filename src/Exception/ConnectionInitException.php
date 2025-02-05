<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Exception;

class ConnectionInitException extends \Exception
{
    public function __sleep()
    {
        return ['message', 'code', 'line', 'file'];
    }
}