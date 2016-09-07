<?php

namespace ExpressivePrismic\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Prismic;

trait DocumentRequirementTrait
{

    protected function getDocument(Request $request)
    {
        return $request->getAttribute(Prismic\Document::class);
    }

    protected function assertDocumentSet(Request $request) : Prismic\Document
    {
        if (!$this->getDocument()) {
            throw new \RuntimeException('Expected a document to be available in the request and none could be found…');
        }

        return $this->getDocument();
    }

}
