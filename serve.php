<?php

use RezT\Http\HttpHost;
use RezT\Net\MediaType;
use RezT\Resource\ResourceLoader;
use RezT\Utility\Loader;

// load autoloader
require_once __DIR__ . "/package/rezt/RezT/Utility/Loader.php";
(new Loader(__DIR__ . "/package/rezt"))->register();
(new Loader(__DIR__ . "/package/markdown"))->register();
(new Loader(__DIR__ . "/package/rezt-markdown"))->register();

// look for resource matching request URI
$resource = (new ResourceLoader())
    ->addResourcePath(__DIR__ . "/documentation")
    ->addExtensionHandler("md", "RezT\Markdown\MarkdownResource")
    ->fetch(HttpHost::current()->getRequest()->getResourcePath());

// if a matching resource was found, serve it
if ($resource) {
    if ($resource->getMediaType())
        MediaType::send($resource->getMediaType());
    echo $resource;
}

// otherwise, serve a 404
else {
    http_response_code(404);
    MediaType::send(MediaType::HTML);
    echo Michelf\Markdown::defaultTransform(file_get_contents("README.md"));
}
