<?php
namespace ExpressivePrismic\View\Helper;

use ExpressivePrismic\Service;
use Zend\Expressive\Helper\UrlHelper;
use Bootstrap;
use Prismic;
use ExpressivePrismic\LinkResolver;

class FragmentTest extends \PHPUnit_Framework_TestCase
{

    private $helper;

    private $linkResolver;

    private $currentDocRegistry;

    public function setUp()
    {
        $bootstrap         = Bootstrap::getInstance();
        $urlHelper         = $bootstrap->container->get(UrlHelper::class);
        $api               = $this->createMock(Prismic\Api::class);
        $routeParams       = $bootstrap->container->get(Service\RouteParams::class);
        $app = $this->app  = $bootstrap->app;

        $this->linkResolver    = new LinkResolver($api, $routeParams, $urlHelper, $app);

        $this->currentDocRegistry = new Service\CurrentDocument;

        $this->helper = new Fragment($this->currentDocRegistry, $this->linkResolver);
    }

    public function testInvokeReturnsSelf()
    {
        $this->assertSame($this->helper, ($this->helper)());
    }

    public function setCurrentDocument()
    {
        $json = file_get_contents( __DIR__ . '/../../../fixtures/document.json');
        $document = Prismic\Document::parse(json_decode($json));
        $this->currentDocRegistry->setDocument($document);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage No prismic document has been set in the document registry
     */
    public function testGetThrowsExceptionWhenDocumentUnset()
    {
        $frag = $this->helper->get('plain_text_field');
    }

    public function testGetReturnsFragmentWithUnqualifiedName()
    {
        $this->setCurrentDocument();
        $frag = $this->helper->get('plain_text_field');
        $this->assertInstanceOf(Prismic\Fragment\FragmentInterface::class, $frag);
    }

    public function testGetReturnsFragmentWithQualifiedName()
    {
        $this->setCurrentDocument();
        $frag = $this->helper->get('article.plain_text_field');
        $this->assertInstanceOf(Prismic\Fragment\FragmentInterface::class, $frag);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Found a dot in the fragment name
     */
    public function testExceptionThrownAccessingIncorrectQualifiedFragmentName()
    {
        $this->setCurrentDocument();
        $frag = $this->helper->get('wrong.plain_text_field');
    }

    public function testAsTextReturnsExpectedValue()
    {
        $this->setCurrentDocument();
        $text = $this->helper->asText('plain_text_field');
        $this->assertSame('Plain Text Value', $text);
    }

    public function testAsHtmlReturnsExpectedValue()
    {
        $this->setCurrentDocument();
        $text = $this->helper->asHtml('plain_text_field');
        $this->assertSame('Plain Text Value', strip_tags($text));
    }

    public function testAccessorsReturnNullForUnknownFragmentName()
    {
        $this->setCurrentDocument();
        $this->assertNull($this->helper->get('unknown'));
        $this->assertNull($this->helper->asText('unknown'));
        $this->assertNull($this->helper->asHtml('unknown'));
    }

}
