<?php

namespace RezT\Application;

use RezT\Http\HttpMethod;
use RezT\Http\HttpStatus;
use RezT\Http\HttpRequest;
use RezT\Http\HttpResponse;
use RezT\Http\Routing\HttpApplication;
use RezT\Http\Routing\HttpRoute;
use RezT\Http\Routing\HttpRouter;
use RezT\Resource\ResourceLoader;

/**
 * RezT documentation application.  Mount this application to host the RezT
 * documentation on the site.
 */
class ReztDocsApplication extends HttpApplication {

    protected $documentationPath = null;

    /**
     * Set the documentation resource path.
     *
     * @param   string  $documentationPath
     */
    public function __construct($documentationPath) {
        parent::__construct();
        $this->setDocumentationPath($documentationPath);
        $this->setAcceptableMethods([HttpMethod::GET]);
    }

    /**
     * Serve a documentation resource or trigger a NOT_FOUND error.
     */
    protected function handleRequest(HttpRequest $request,
        HttpResponse $response, HttpRoute $route, HttpRouter $router ) {

        $resource = (new ResourceLoader())
            ->addResourcePath($this->getDocumentationPath())
            ->addExtensionHandler("md", "RezT\Markdown\MarkdownResource")
            ->fetch($request->getRelativeResourcePath());

        if (empty($resource)) {
            $router->error($request, $response, HttpStatus::NOT_FOUND);
        } else {
            $response->sendResource($resource);
        }
    }

    /**
     * Set the documentation resource path.
     *
     * @param   string  $documentationPath
     */
    public function setDocumentationPath($documentationPath) {
        $this->documentationPath = (string)$documentationPath;
    }

    /**
     * Return the documentation resource path.
     *
     * @return  string
     */
    public function getDocumentationPath() {
        return $this->documentationPath;
    }

}