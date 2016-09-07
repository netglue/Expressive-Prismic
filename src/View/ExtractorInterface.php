<?php

declare(strict_types=1);

namespace ExpressivePrismic\View;

use Prismic\WithFragments;

interface ExtractorInterface
{

    public function extract(WithFragments $document, string $type = null) : array;

}
