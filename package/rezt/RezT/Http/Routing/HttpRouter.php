<?php

namespace RezT\Http\Routing;

use RezT\Http\HttpHost;
use RezT\Http\HttpRequest;
use RezT\Http\HttpResponse;
use RuntimeException;

/**
 * HTTP request handler which routes HTTP requests to other handlers.
 */
class HttpRouter {

    protected $routes = [];

    private $pathOnlyMatch = false;

    /**
     * Route the request and call the matching handler.  Throw an exception if
     * no matching handler is found.
     *
     * @param   \RezT\Http\HttpRequest  $request
     * @param   \RezT\Http\HttpResponse $response
     */
    public function __invoke(HttpRequest $request, HttpResponse $response) {
        if (!$this->route($request, $response))
            throw new RuntimeException("no matching route");
    }

    /**
     * Add a route.  If the first argument is a string, use the second argument
     * with it to create a new route object.  Return this instance for chaining.
     *
     * @param   string|\RezT\Http\Routing\HttpRoute $route
     * @param   callable                            $handler
     * @return  \RezT\Http\Routing\HttpRouter
     */
    public function addRoute($route, callable $handler = null) {
        if (!$route instanceof HttpRoute) {
            $route = new HttpRoute($route, $handler);
        }

        $this->routes[] = $route;
        return $this;
    }

    /**
     * Route and handle the request.  Return false if no matching route was
     * found.  If no response is provided, an empty response will be created.
     * If no request is provided, the global PHP request will be used.
     *
     * @param   \RezT\Http\HttpRequest  $request
     * @param   \RezT\Http\HttpResponse $response
     * @return  boolean
     */
    public function route(HttpRequest $request = null,
        HttpResponse $response = null) {

        if (is_null($request)) {
            $request = HttpHost::current()->getRequest();
        }

        if (is_null($response)) {
            $response = new HttpResponse();
        }

        try {
            $this->pathOnlyMatch = false;
            foreach ($this->routes as $route) {
                $result = 0;
                if ($route->matches($request, $result)) {
                    $handler = $route->getHandler();

                    if (!is_callable($handler))
                        return false;

                    $handler($request, $response, $route, $this);
                    return true;
                }

                else {
                    $pathMatch = ($result == HttpRoute::RESULT_PATH_MATCH);
                    $this->pathOnlyMatch = $this->pathOnlyMatch || $pathMatch;
                }
            }
        } catch (Exception $e) {
            foreach ($this->routes as $route) {
                if ($route->matches($e)) {
                    $handler = $route->getHandler();

                    if (!is_callable($handler)) {
                        throw $e;
                    }

                    $handler($request, $response, $e);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Route an error response to the appropriate handler.  Throw an exception
     * if there is no appropriate handler.
     *
     * @param   \RezT\Http\HttpRequest  $request
     * @param   \RezT\Http\HttpResponse $response
     * @param   int                     $httpStatus
     */
    public function error(HttpRequest $request, HttpResponse $response,
        $httpStatus) {
        
        foreach ($this->routes as $route) {
            if ($route->matches($httpStatus)) {
                $handler = $route->getHandler();
                break;
            }
        }

        if (!is_callable($handler)) {
            throw new Exception("no handler for status $httpStatus");
        }

        $handler($request, $response, $httpStatus);
        return true;
    }

    /**
     * Return true if this router skipped a path-only match while routing.  This
     * should be used in fallbacks to determine if the response should be a 404
     * Not Found or a 405 Method Not Allowed.
     *
     * @return  boolean
     */
    public function skippedPathOnlyMatch() {
        return $this->pathOnlyMatch;
    }

}