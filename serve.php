<?php

use RezT\Application\ReztDocsApplication;
use RezT\Http\HttpHost;
use RezT\Http\HttpRequest;
use RezT\Http\HttpResponse;
use RezT\Http\HttpStatus;
use RezT\Http\Routing\HttpRouter;
use RezT\Http\Routing\HttpRoute;
use RezT\Http\Routing\HttpRedirect;
use RezT\Net\MediaType;
use RezT\Resource\ResourceApplication;
use RezT\Resource\ResourceLoader;
use RezT\Utility\Loader;

// load autoloader
require_once __DIR__ . "/package/rezt/RezT/Utility/Loader.php";
(new Loader(__DIR__ . "/package/rezt"))->register();
(new Loader(__DIR__ . "/package/markdown"))->register();
(new Loader(__DIR__ . "/package/rezt-markdown"))->register();
(new Loader(__DIR__ . "/package/rezt-template"))->register();
(new Loader(__DIR__ . "/package/rezt-docs"))->register();

// setup router
(new HttpRouter())
    // redirect the homepage to the documentation
    ->addRoute("GET", HttpRedirect::temporary("/doc/welcome"))

    // serve documentation at /doc
    ->addRoute("GET /doc/", new ReztDocsApplication(__DIR__ . "/documentation"))

    // use RezT assets until more permanent ones are available
    ->addRoute("GET /", new ResourceApplication((new ResourceLoader())
        ->addResourcePath(__DIR__ . "/package/rezt-asset/asset")
        ->addExtensionType("ico", MediaType::ICON)
        ->addExtensionType("txt", MediaType::TEXT)
    ))

    // send 404 for all unmatched routes
    ->addRoute("* /", function(HttpRequest $req, HttpResponse $rsp, HttpRoute $route, HttpRouter $router) {
        $router->error($req, $rsp, HttpStatus::NOT_FOUND);
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

    // route the host request
    ->route(HttpHost::current()->getRequest());