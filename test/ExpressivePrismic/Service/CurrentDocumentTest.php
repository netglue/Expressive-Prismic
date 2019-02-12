<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Service;

use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismicTest\TestCase;
use Prismic\Document;

class CurrentDocumentTest extends TestCase
{
    private $doc;

    public function setUp() : void
    {
        $this->doc = $this->prophesize(Document::class)->reveal();
    }

    public function testBasic() : void
    {
        $current = new CurrentDocument;

        $this->assertNull($current->getDocument());
        $this->assertFalse($current->hasDocument());

        $current->setDocument($this->doc);
        $this->assertSame($this->doc, $current->getDocument());
        $this->assertTrue($current->hasDocument());
    }
}
