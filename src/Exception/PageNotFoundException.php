<?php

namespace ExpressivePrismic\Exception;

use \RuntimeException;

class PageNotFoundException extends RuntimeException implements ExceptionInterface
{

    public static function throw404()
    {
        $e          = new self;
        $e->code    = 404;
        $e->message = 'Page Not Found';
        throw $e;
    }

}
