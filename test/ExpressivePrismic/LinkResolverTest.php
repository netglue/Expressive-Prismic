<?php
declare(strict_types=1);

namespace ExpressivePrismicTest;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\LinkResolver;

// Deps
use Prismic;
use Prismic\Fragment\Link\LinkInterface;
use Prismic\Fragment\Link\DocumentLink;
use Prismic\Fragment\Link\WebLink;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Router\Exception\ExceptionInterface as RouterException;
use ExpressivePrismic\Service\RouteParams;
use Zend\Expressive\Application;



class LinkResolverTest extends TestCase
{

    public function testNothing()
    {

    }



}
