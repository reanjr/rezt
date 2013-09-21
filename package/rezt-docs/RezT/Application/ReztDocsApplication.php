<?php

namespace RezT\Application;

use RezT\Http\HttpMethod;
use RezT\Http\HttpStatus;
use RezT\Http\HttpRequest;
use RezT\Http\HttpResponse;
use RezT\Http\Routing\HttpApplication;
use RezT\Http\Routing\HttpRoute;
use RezT\Http\Routing\HttpRouter;
use RezT\Net\MediaType;
use RezT\Resource\Resource;
use RezT\Resource\ResourceApplication;
use RezT\Resource\ResourceLoader;

/**
 * RezT documentation application.  Mount this application to host the RezT
 * documentation on the site.
 */
class ReztDocsApplication extends HttpApplication {

    protected $documentationPath = null;
    private $packagePath = null;

    /**
     * Set the documentation resource path.
     *
     * @param   string  $documentationPath
     */
    public function __construct($documentationPath) {
        parent::__construct();
        $this->setDocumentationPath($documentationPath);
        $this->setAcceptableMethods([HttpMethod::GET]);
        $this->packagePath = dirname(dirname(__DIR__));
    }

    /**
     * Serve a documentation resource or trigger a NOT_FOUND error.
     *
     * @param   \RezT\Http\HttpRequest  $request
     * @param   \RezT\Http\HttpResponse $response
     * @param   \RezT\Http\HttpRoute    $route
     * @param   \RezT\Http\HttpRouter   $router
     */
    protected function handleRequest(HttpRequest $request,
        HttpResponse $response, HttpRoute $route, HttpRouter $router ) {

        $appRouter = new HttpRouter($router);

        // serve static assets from /static
        $staticLoader = (new ResourceLoader())
            ->addResourcePath($this->packagePath . "/static")
            ->addExtensionType("css", MediaType::CSS);
        $staticApp = new ResourceApplication($staticLoader);
        $appRouter->addRoute("GET /static/", $staticApp);

        // load markdown assets from docs and wrap in layout template
        $dynLoader = (new ResourceLoader())
            ->addResourcePath($this->getDocumentationPath())
            ->addExtensionHandler("md", "RezT\Markdown\MarkdownResource")
            ->addTransform(function(Resource $resource) {
                $wrapper = (new ResourceLoader())
                    ->addResourcePath($this->packagePath . "/template")
                    ->addExtensionType("phtml", MediaType::HTML)
                    ->addExtensionHandler("phtml", "RezT\Template\Template")
                    ->fetch("docpage");
                $wrapper($resource);
                return $wrapper;
            });
        $dynApp = new ResourceApplication($dynLoader);
        $appRouter->addRoute("GET /", $dynApp);

        // route the request
        $appRouter->route($request, $response);
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