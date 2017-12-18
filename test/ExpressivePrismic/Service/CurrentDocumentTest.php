<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Service;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Service\CurrentDocument;

// Deps
use Prismic\Document;

class CurrentDocumentTest extends TestCase
{
    private $doc;

    public function setUp()
    {
        $this->doc = $this->prophesize(Document::class)->reveal();
    }

    public function testBasic()
    {
        $current = new CurrentDocument;

        $this->assertNull($current->getDocument());
        $this->assertFalse($current->hasDocument());

        $current->setDocument($this->doc);
        $this->assertSame($this->doc, $current->getDocument());
        $this->assertTrue($current->hasDocument());
    }
}
