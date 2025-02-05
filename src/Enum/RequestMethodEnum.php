<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Enum;

enum RequestMethodEnum: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
}
