<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\View\Helper;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\View\Helper\Fragment;

// Deps
use ExpressivePrismic\Service\CurrentDocument;
use Prismic\LinkResolver;
use Prismic\Document;
use Prismic\Fragment\Text;

class FragmentTest extends TestCase
{

    private $resolver;
    private $docRegistry;
    private $doc;

    public function setUp()
    {
        $this->resolver = $this->prophesize(LinkResolver::class);
        $this->docRegistry = $this->prophesize(CurrentDocument::class);
        $this->doc = $this->prophesize(Document::class);
    }

    private function getHelper()
    {
        return new Fragment(
            $this->docRegistry->reveal(),
            $this->resolver->reveal()
        );
    }

    public function testInvokeReturnsSelf()
    {
        $helper = $this->getHelper();
        $this->assertSame($helper, ($helper)());
    }

    /**
     * @expectedException ExpressivePrismic\Exception\RuntimeException
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
        $frag = $this->prophesize(Text::class);
        $frag = $frag->reveal();

        $this->doc->get('mytype.myfrag')->willReturn($frag);
        $this->setCurrentDocument();
        $helper = $this->getHelper();

        $this->assertSame($frag, $helper->get('myfrag'));
        $this->assertSame($frag, $helper->get('mytype.myfrag'));
    }

    /**
     * @expectedException ExpressivePrismic\Exception\RuntimeException
     * @expectedExceptionMessage Found a dot in the fragment name
     */
    public function testExceptionThrownAccessingIncorrectQualifiedFragmentName()
    {
        $this->setCurrentDocument();
        $helper = $this->getHelper();
        $helper->get('wrong.myfrag');
    }

    public function testAsTextReturnsExpectedValue()
    {
        $frag = $this->prophesize(Text::class);
        $frag->asText()->willReturn('Example Text');
        $frag = $frag->reveal();

        $this->doc->get('mytype.myfrag')->willReturn($frag);
        $this->setCurrentDocument();
        $helper = $this->getHelper();

        $this->assertSame('Example Text', $helper->asText('myfrag'));
    }

    public function testAsHtmlReturnsExpectedValue()
    {
        $frag = $this->prophesize(Text::class);
        $frag->asHtml($this->resolver->reveal())->willReturn('Example HTML');
        $frag = $frag->reveal();

        $this->doc->get('mytype.myfrag')->willReturn($frag);
        $this->setCurrentDocument();
        $helper = $this->getHelper();

        $this->assertSame('Example HTML', $helper->asHtml('myfrag'));
    }

    public function testAccessorsReturnNullForUnknownFragmentName()
    {
        $this->doc->get('mytype.myfrag')->willReturn(null);
        $this->setCurrentDocument();
        $helper = $this->getHelper();

        $this->assertNull($helper->get('myfrag'));
        $this->assertNull($helper->asText('myfrag'));
        $this->assertNull($helper->asHtml('myfrag'));
    }

}
