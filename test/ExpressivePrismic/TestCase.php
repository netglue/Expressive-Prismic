<?php
declare(strict_types=1);

namespace ExpressivePrismicTest;

use PHPUnit\Framework\TestCase as PHPUnit;

class TestCase extends PHPUnit
{
    protected function tearDown() : void
    {
        $refl = new \ReflectionObject($this);
        foreach ($refl->getProperties() as $prop) {
            if (! $prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit')) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }
    }
}
