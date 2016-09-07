<?php
namespace ExpressivePrismic\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Prismic;
use Zend\View\HelperPluginManager;

use ExpressivePrismic\View\MetaDataExtractor;
use ExpressivePrismic\View\HeadTitleExtractor;
use ExpressivePrismic\View\TwitterCardExtractor;
use ExpressivePrismic\View\OpenGraphExtractor;

class MetaDataAutomator
{

    private $helpers;
    private $options;

    public function __construct(HelperPluginManager $helpers, array $options = [])
    {
        $this->helpers = $helpers;
        $this->options = $options;
    }

    public function __invoke(Request $request, Response $response, callable $next = null) : Response
    {
        if ($document = $request->getAttribute(Prismic\Document::class)) {
            $this->applyMeta($document);
        }

        if ($next) {
            return $next($request, $response);
        }
        return $response;
    }

    private function applyMeta(Prismic\Document $document)
    {
        $this->applyMetaTags($document);
        $this->applyHeadTitle($document);
        $this->applyTwitterCards($document);
        $this->applyOpenGraph($document);
    }

    private function applyMetaTags(Prismic\Document $document)
    {
        $map = isset($this->options['meta_data_map']) ? $this->options['meta_data_map'] : null;
        if (!is_array($map) || !count($map)) {
            return;
        }
        $extrator = new MetaDataExtractor($map);
        $data = $extrator->extract($document);
        $headMeta = $this->helpers->get('headMeta');
        foreach ($data as $name => $content) {
            $headMeta->setName($name, $content);
        }
    }

    private function applyHeadTitle(Prismic\Document $document)
    {
        $search = isset($this->options['title_search']) ? $this->options['title_search'] : null;
        if (!is_array($search) || !count($search)) {
            return;
        }
        $extrator = new HeadTitleExtractor($search);
        $data = $extrator->extract($document);
        $headTitle = $this->helpers->get('headTitle');
        if (isset($data['title'])) {
            $headTitle->set($data['title']);
        }
    }

    private function applyTwitterCards(Prismic\Document $document)
    {
        $map = isset($this->options['twitter_map']) ? $this->options['twitter_map'] : null;
        if (!is_array($map) || !count($map)) {
            return;
        }
        $extrator = new TwitterCardExtractor($map);
        $data = $extrator->extract($document);
        $headMeta = $this->helpers->get('headMeta');
        foreach ($data as $name => $content) {
            $headMeta->setName($name, $content);
        }
    }

    private function applyOpenGraph(Prismic\Document $document)
    {
        $map = isset($this->options['og_map']) ? $this->options['og_map'] : null;
        if (!is_array($map) || !count($map)) {
            return;
        }
        $extrator = new OpenGraphExtractor($map);
        $data = $extrator->extract($document);

        // HeadMeta won't allow <meta property> tags if the doctype is not Html 5 or rdfa.
        $doctype = $this->helpers->get('doctype');
        $dt = $doctype->getDoctype();
        $doctype($doctype::HTML5);

        $headMeta = $this->helpers->get('headMeta');
        foreach ($data as $name => $content) {
            $headMeta->setProperty($name, $content);
        }

        // Revert to doctype originally set
        $doctype($dt);
    }
}
