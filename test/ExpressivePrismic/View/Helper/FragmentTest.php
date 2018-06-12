<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\View\Helper;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\View\Helper\Fragment;

// Deps
use ExpressivePrismic\Service\CurrentDocument;
use Prismic\DocumentInterface;
use Prismic\Document\Fragment\FragmentInterface;

class FragmentTest extends TestCase
{

    private $docRegistry;
    private $doc;

    public function setUp()
    {
        $this->docRegistry = $this->prophesize(CurrentDocument::class);
        $this->doc = $this->prophesize(DocumentInterface::class);
    }

    private function getHelper()
    {
        return new Fragment(
            $this->docRegistry->reveal()
        );
    }

    public function testInvokeReturnsSelf()
    {
        $helper = $this->getHelper();
        $this->assertSame($helper, ($helper)());
    }

    /**
     * @expectedException \ExpressivePrismic\Exception\RuntimeException
     * @expectedExceptionMessage No prismic document has been set in the document registry
     */
    public function testExceptionThrownWhenNoDocumentIsAvailable()
    {
        $this->docRegistry->getDocument()->willReturn(null);
        $helper = $this->getHelper();
        $helper->get('foo');
    }

    private function setCurrentDocument()
    {
        $this->doc->getType()->willReturn('mytype');
        $this->docRegistry->getDocument()->willReturn(
            $this->doc->reveal()
        );
    }

    public function testGetReturnsFragmentWithUnqualifiedOrQualifiedName()
    {
        $frag = $this->prophesize(FragmentInterface::class);
        $frag = $frag->reveal();

        $this->doc->get('myfrag')->willReturn($frag);
        $this->setCurrentDocument();
        $helper = $this->getHelper();

        $this->assertSame($frag, $helper->get('myfrag'));
    }


    public function testAsTextReturnsExpectedValue()
    {
        $frag = $this->prophesize(FragmentInterface::class);
        $frag->asText()->willReturn('Example Text');
        $frag = $frag->reveal();

        $this->doc->get('myfrag')->willReturn($frag);
        $this->setCurrentDocument();
        $helper = $this->getHelper();

        $this->assertSame('Example Text', $helper->asText('myfrag'));
    }

    public function testAsHtmlReturnsExpectedValue()
    {
        $frag = $this->prophesize(FragmentInterface::class);
        $frag->asHtml()->willReturn('Example HTML');
        $frag = $frag->reveal();

        $this->doc->get('myfrag')->willReturn($frag);
        $this->setCurrentDocument();
        $helper = $this->getHelper();

        $this->assertSame('Example HTML', $helper->asHtml('myfrag'));
    }

    public function testAccessorsReturnsNullForUnknownFragmentName()
    {
        $this->doc->get('myfrag')->willReturn(null);
        $this->setCurrentDocument();
        $helper = $this->getHelper();

        $this->assertNull($helper->get('myfrag'));
        $this->assertNull($helper->asText('myfrag'));
        $this->assertNull($helper->asHtml('myfrag'));
    }
}
