<?php
declare(strict_types=1);

namespace ExpressivePrismic\Exception;

use \RuntimeException;

/**
 * PageNotFoundException
 *
 * @package ExpressivePrismic\Exception
 */
class PageNotFoundException extends RuntimeException implements ExceptionInterface
{

    /**
     * Throw a 404 Exception
     *
     */
    public static function throw404()
    {
        throw new self('Page Not Found', 404);
    }
}
