<?php

namespace RezT\Markdown;

use RezT\Resource\Resource;
use RezT\Net\MediaType;
use Michelf;

/**
 * Read markdown resources.
 */
class MarkdownResource extends Resource {

    protected $mediaType = MediaType::HTML;

    /**
     * Return the resource content.
     *
     * @return  string
     */
    public function __toString() {
        if (!$this->content)
            $this->content = Michelf\Markdown::defaultTransform(
                file_get_contents($this->getResourceFile())
            );
        return $this->content;
    }

}