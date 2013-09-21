<?php

namespace RezT\Resource;

use RezT\Http\Routing\HttpApplication;
use RezT\Http\HttpRequest;
use RezT\Http\HttpResponse;
use RezT\Http\HttpStatus;
use RezT\Http\Routing\HttpRoute;
use RezT\Http\Routing\HttpRouter;

/**
 * Serve static resource and assets.
 */
class ResourceApplication extends HttpApplication {

    protected $resourceLoader = null;

    /**
     * Set the resource loader used to load resources for the application.
     *
     * @param   \RezT\Resource\ResourceLoader   $resourceLoader
     */
    public function __construct(ResourceLoader $resourceLoader) {
        parent::__construct();
        $this->setResourceLoader($resourceLoader);
    }

    /**
     * Serve the resource specified or trigger a NOT_FOUND error.
     *
     * @param   \RezT\Http\HttpRequest          $request
     * @param   \RezT\Http\HttpResponse         $response
     * @param   \RezT\Http\Routing\HttpRoute    $route
     * @param   \RezT\Http\Routing\HttpRouter   $router
     */
    protected function handleRequest(HttpRequest $request,
        HttpResponse $response, HttpRoute $route, HttpRouter $router) {

        $path = $request->getResourcePath();
        $resource = $this->getResourceLoader()->fetch($path);

        if (empty($resource))
            return $router->error($request, $response, HttpStatus::NOT_FOUND);

        $response->sendResource($resource, $request);
    }

    /**
     * Set the resource loader used to load resources for the application.
     *
     * @param   \RezT\Resource\ResourceLoader   $resourceLoader
     */
    public function setResourceLoader(ResourceLoader $resourceLoader) {
        $this->resourceLoader = $resourceLoader;
    }

    /**
     * Return the resource loader used to load resources for the application.
     *
     * @return  \RezT\Resource\ResourceLoader
     */
    public function getResourceLoader() {
        return $this->resourceLoader;
    }

}