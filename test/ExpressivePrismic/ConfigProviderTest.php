<?php
declare(strict_types=1);

namespace ExpressivePrismicTest;

use ExpressivePrismic\ConfigProvider;

class ConfigProviderTest extends TestCase
{
    public function testInvokeReturnsAnArray()
    {
        $config = new ConfigProvider();
        $this->assertInternalType('array', $config());
    }
}
