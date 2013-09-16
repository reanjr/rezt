<?php

use RezT\Http\HttpHost;
use RezT\Http\HttpRequest;
use RezT\Http\HttpResponse;
use RezT\Http\HttpStatus;
use RezT\Http\Routing\HttpRouter;
use RezT\Http\Routing\HttpRoute;
use RezT\Http\Routing\HttpRedirect;
use RezT\Net\MediaType;
use RezT\Resource\ResourceLoader;
use RezT\Utility\Loader;

// load autoloader
require_once __DIR__ . "/package/rezt/RezT/Utility/Loader.php";
(new Loader(__DIR__ . "/package/rezt"))->register();
(new Loader(__DIR__ . "/package/markdown"))->register();
(new Loader(__DIR__ . "/package/rezt-markdown"))->register();

(new HttpRouter())
    // redirect the homepage to the documentation
    ->addRoute("GET", HttpRedirect::temporary("/doc/welcome"))

    // serve documentation at /doc
    ->addRoute("GET /doc/", function(HttpRequest $req, HttpResponse $rsp, HttpRoute $route, HttpRouter $router) {
        $path = $route[0];
        $res = (new ResourceLoader())
            ->addResourcePath(__DIR__ . "/documentation")
            ->addExtensionHandler("md", "RezT\Markdown\MarkdownResource")
            ->fetch($path);

        if (empty($res))
            return $router->error($req, $rsp, HttpStatus::NOT_FOUND);

        $rsp->sendResource($res);
    })

    // handle 404 errors
    ->addRoute("error 404", function(HttpRequest $req, HttpResponse $rsp) {
        // fetch README
        $body = Michelf\Markdown::defaultTransform(file_get_contents("README.md"));

        // send response with appropriate status
        $rsp->setStatus(HttpStatus::NOT_FOUND);
        $rsp->setMediaType(MediaType::HTML);
        $rsp->setBody($body);
        $rsp->send();
    })

    // handle 500 errors
    ->addRoute("error 500", function(HttpRequest $req, HttpResponse $rsp) {
        $rsp->setStatus(HttpStatus::INTERNAL_SERVER_ERROR);
        $rsp->setMediaType(MediaType::TEXT);
        $rsp->setBody(HttpStatus::getMessage(HttpStatus::INTERNAL_SERVER_ERROR));
        $rsp->send();
    })

    // send 404 for all unmatched routes
    ->addRoute("* /", function(HttpRequest $req, HttpResponse $rsp, HttpRoute $route, HttpRouter $router) {
        $router->error($req, $rsp, HttpStatus::NOT_FOUND);
    })

    // route the host request
    ->route(HttpHost::current()->getRequest());