<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\View\Helper;

use ExpressivePrismic\Exception\RuntimeException;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismic\View\Helper\Fragment;
use ExpressivePrismicTest\TestCase;
use Prismic\Document\Fragment\FragmentInterface;
use Prismic\DocumentInterface;

class FragmentTest extends TestCase
{

    private $docRegistry;
    private $doc;

    public function setUp() : void
    {
        $this->docRegistry = $this->prophesize(CurrentDocument::class);
        $this->doc = $this->prophesize(DocumentInterface::class);
    }

    private function getHelper() : Fragment
    {
        return new Fragment(
            $this->docRegistry->reveal()
        );
    }

    public function testInvokeReturnsSelf() : void
    {
        $helper = $this->getHelper();
        $this->assertSame($helper, ($helper)());
    }

    public function testExceptionThrownWhenNoDocumentIsAvailable() : void
    {
        $this->docRegistry->getDocument()->willReturn(null);
        $helper = $this->getHelper();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No prismic document has been set in the document registry');
        $helper->get('foo');
    }

    private function setCurrentDocument() : void
    {
        $this->doc->getType()->willReturn('mytype');
        $this->docRegistry->getDocument()->willReturn(
            $this->doc->reveal()
        );
    }

    public function testGetReturnsFragmentWithUnqualifiedOrQualifiedName() : void
    {
        $frag = $this->prophesize(FragmentInterface::class);
        $frag = $frag->reveal();

        $this->doc->get('myfrag')->willReturn($frag);
        $this->setCurrentDocument();
        $helper = $this->getHelper();

        $this->assertSame($frag, $helper->get('myfrag'));
    }


    public function testAsTextReturnsExpectedValue() : void
    {
        $frag = $this->prophesize(FragmentInterface::class);
        $frag->asText()->willReturn('Example Text');
        $frag = $frag->reveal();

        $this->doc->get('myfrag')->willReturn($frag);
        $this->setCurrentDocument();
        $helper = $this->getHelper();

        $this->assertSame('Example Text', $helper->asText('myfrag'));
    }

    public function testAsHtmlReturnsExpectedValue() : void
    {
        $frag = $this->prophesize(FragmentInterface::class);
        $frag->asHtml()->willReturn('Example HTML');
        $frag = $frag->reveal();

        $this->doc->get('myfrag')->willReturn($frag);
        $this->setCurrentDocument();
        $helper = $this->getHelper();

        $this->assertSame('Example HTML', $helper->asHtml('myfrag'));
    }

    public function testAccessorsReturnsNullForUnknownFragmentName() : void
    {
        $this->doc->get('myfrag')->willReturn(null);
        $this->setCurrentDocument();
        $helper = $this->getHelper();

        $this->assertNull($helper->get('myfrag'));
        $this->assertNull($helper->asText('myfrag'));
        $this->assertNull($helper->asHtml('myfrag'));
    }
}
