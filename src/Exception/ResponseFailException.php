<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Exception;

class ResponseFailException extends \Exception
{

    public function __sleep()
    {
        return ['message', 'code', 'line', 'file'];
    }
}