<?php

namespace RezT\Http\Routing;

use RezT\Http\HttpHost;
use RezT\Http\HttpStatus;
use RezT\Http\HttpRequest;
use RezT\Http\HttpResponse;
use RezT\Http\Routing\HttpRoute;
use RezT\Http\Routing\HttpRouter;

/**
 * Base class for HTTP applications.
 */
abstract class HttpApplication extends HttpHost {

    protected $acceptableMethods = [];
    protected $baseUri = null;

    /**
     * Initialize with an empty request.
     */
    public function __construct() {
        parent::__construct(new HttpRequest());
    }

    /**
     * Application request handler.
     *
     * @param   \RezT\Http\HttpRequest          $request
     * @param   \RezT\Http\HttpResponse         $response
     * @param   \RezT\Http\Routing\HttpRoute    $route
     * @param   \RezT\Http\Routing\HttpRouter   $router
     * @param   mixed                           $context_1
     */
    abstract protected function handleRequest(HttpRequest $request,
        HttpResponse $response, HttpRoute $route, HttpRouter $router);

    /**
     * Execute the application handler for the provided request.
     *
     * @param   \RezT\Http\HttpRequest          $request
     * @param   \RezT\Http\HttpResponse         $response
     * @param   \RezT\Http\Routing\HttpRoute    $route
     * @param   \RezT\Http\Routing\HttpRouter   $router
     * @param   mixed                           $context_1
     */
    public function __invoke(HttpRequest $request, HttpResponse $response,
        HttpRoute $route, HttpRouter $router) {

        // verify request method or trigger METHOD NOT ALLOWED error
        if (!$this->verifyRequest($request)) {
            $status = HttpStatus::METHOD_NOT_ALLOWED;
            $methods = implode(",", $this->getAcceptableMethods());
            $response->setHeader("Accept", $methods);
            $router->error($request, $response, $status);
            return;
        }

        // setup this application as the new host execution environment
        $this->routeResourcePath($request, $route);
        $this->setRequest($request);
        $oldHost = HttpHost::activate($this);

        // handle the request and restore the execution environment
        $this->handleRequest($request, $response, $route, $router);
        $oldHost && HttpHost::activate($oldHost);
    }

    /**
     * Apply route transformation to the request.  If the request resource path
     * begins with the route path, this portion of the path will be removed from
     * the request resource path.  Return true if a transformation was made.
     *
     * @return  boolean
     */
    public function routeResourcePath(HttpRequest $request, HttpRoute $route) {
        $routePath = $route->getPathRule();
        $resourcePath = $request->getResourcePath();

        if (substr($resourcePath, 0, strlen($routePath)) == $routePath) {
            // ensure we have consistent slashes
            $routePath = rtrim($routePath, "/");
            $resourcePath = substr($resourcePath, strlen($routePath));
            $request->setResourcePath($resourcePath);
            return true;
        }

        return false;
    }

    /**
     * Set the HTTP methods this application accepts.  The default is an empty
     * array, which means all methods are allowed.
     *
     * @param   array   $methods
     */
    public function setAcceptableMethods($methods = []) {
        $this->acceptableMethods = (array)$methods;
    }

    /**
     * Return the HTTP methods this application accepts.  Return an empty array
     * if this application accepts any method.
     *
     * @return  array
     */
    public function getAcceptableMethods() {
        return $this->acceptableMethods;
    }

    /**
     * Return true if the request method is acceptable to this application.
     *
     * @param   \RezT\Http\HttpRequest  $request
     * @return  boolean
     */
    public function verifyRequest(HttpRequest $request) {
        $methods = array_map("strtoupper", $this->getAcceptableMethods());
        return (empty($methods))
            ? true
            : in_array(strtoupper($request->getMethod()), $methods);
    }

    /**
     * Set the base URI for this application.
     *
     * @param   string  $baseUri
     */
    public function setBaseUri($baseUri) {
        $this->baseUri = (string)$baseUri;
    }

    /**
     * Return the base URI for this application.
     *
     * @return  string
     */
    public function getBaseUri() {
        return $this->baseUri;
    }

}