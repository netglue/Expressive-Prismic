<?php
declare(strict_types=1);

namespace ExpressivePrismicTest;

use ExpressivePrismic\ConfigProvider;

class ConfigProviderTest extends TestCase
{
    public function testInvokeReturnsAnArray() : void
    {
        $config = new ConfigProvider();
        $this->assertIsArray($config());
    }
}
