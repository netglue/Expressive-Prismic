<?php
declare(strict_types=1);

namespace ExpressivePrismic\Exception;

/**
 * PageNotFoundException
 */
class DocumentNotFoundException extends RuntimeException
{

    /**
     * Throw a 404 Exception
     */
    public static function throw404()
    {
        throw new self('Page Not Found', 404);
    }
}
