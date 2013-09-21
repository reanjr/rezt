<?php

namespace RezT\Http\Routing;

use RezT\Http\HttpHost;
use RezT\Http\HttpRequest;
use RezT\Http\HttpResponse;
use RezT\Http\HttpStatus;
use RuntimeException;

/**
 * HTTP request handler which routes HTTP requests to other handlers.
 */
class HttpRouter {

    protected $fallbackRouter = null;
    protected $routes = [];

    private $pathOnlyMatch = false;
    private $next = 0;

    /**
     * Set the fallback router to use if this router cannot match a route.
     *
     * @param   \RezT\Http\Routing\HttpRouter   $fallbackRouter
     */
    public function __construct(HttpRouter $fallbackRouter = null) {
        if ($fallbackRouter)
            $this->setFallbackRouter($fallbackRouter);
    }

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
     * Set the fallback router to use if this router cannot match a route.
     *
     * @param   \RezT\Http\Routing\HttpRouter   $fallbackRouter
     */
    public function setFallbackRouter(HttpRouter $fallbackRouter) {
        $this->fallbackRouter = $fallbackRouter;
    }

    /**
     * Return the fallback router to use if this router cannot match a route.
     *
     * @return  \RezT\Http\Routing\HttpRouter
     */
    public function getFallbackRouter() {
        return $this->fallbackRouter;
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
     * Route and handle the request.  Throw an exception if no matching route
     * was found.  If no response is provided, an empty response will be
     * created. If no request is provided, the global PHP request will be used.
     *
     * @param   \RezT\Http\HttpRequest  $request
     * @param   \RezT\Http\HttpResponse $response
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
            // this will be flagged if route has matching path but not method
            $this->pathOnlyMatch = false;

            // check for matching request handler
            while ($route = $this->nextRoute()) {
                $result = 0;
                if ($route->matches($request, $result)) {
                    $handler = $route->getHandler();
                    break;
                } else {
                    $pathMatch = ($result == HttpRoute::RESULT_PATH_MATCH);
                    $this->pathOnlyMatch = $this->pathOnlyMatch || $pathMatch;
                }
            }

            // execute handler if one was found
            if ($handler) {
                $handler($request, $response, $route, $this);
            }

            // use fallback router if available
            elseif ($this->getFallbackRouter()) {
                $this->getFallbackRouter()->route($request, $response);
            }

            // no request handler; trigger a NOT FOUND
            else {
                $this->error($request, $response, HttpStatus::NOT_FOUND);
            }
        } catch (Exception $e) {
            // check for matching exception handler
            $handler = null;
            foreach ($this->routes as $route) {
                if ($route->matches($e)) {
                    $handler = $route->getHandler();
                    break;
                }
            }

            // execute handler if one was found
            if ($handler) {
                $handler($request, $response, $e);
            }

            // use fallback router if available
            elseif ($this->getFallbackRouter()) {
                $this->getFallbackRouter()->exception($request, $response, $e);
            }

            // no exception handler; re-throw
            else {
                throw $e;
            }
        }
    }

    /**
     * Trigger an error handler.  Throw an exception if there is no appropriate
     * handler.
     *
     * @param   \RezT\Http\HttpRequest  $request
     * @param   \RezT\Http\HttpResponse $response
     * @param   int                     $httpStatus
     */
    public function error(HttpRequest $request, HttpResponse $response,
        $httpStatus) {

        // try additional routes if this is a NOT FOUND
        $routesExhausted = ($this->next == 0);
        if ($httpStatus == HttpStatus::NOT_FOUND && !$routesExhausted) {
            $this->route($request, $response);
            return;
        }

        // try to find a matching error route handler
        foreach ($this->routes as $route) {
            if ($route->matches($httpStatus)) {
                $handler = $route->getHandler();
                $handler($request, $response, $httpStatus);
                return;
            }
        }

        // no handler, throw an exception
        $status = HttpStatus::getMessage($httpStatus) ?: $httpStatus;
        throw new RuntimeException("no handler for error $status");
    }

    /**
     * Trigger an exception handler.  Throw the exception if there is no
     * appropriate handler.
     *
     * @param   \RezT\Http\HttpRequest  $request
     * @param   \RezT\Http\HttpResponse $response
     * @param   Exception               $e
     */
    public function exception(HttpRequest $request, HttpResponse $response,
        Exception $e) {

        // try to find a matching route handler
        foreach ($this->routes as $route) {
            if ($route->matches($e)) {
                $handler = $route->getHandler();
                return $handler($request, $response, $e);
            }
        }

        // no handler; throw the exception
        throw $e;
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

    /**
     * Return the next route, and advance index to the following route.
     *
     * @return  \RezT\Http\Routing\HttpRoute
     */
    protected function nextRoute() {
        if ($this->next >= count($this->routes)) {
            $this->next = 0;
            return null;
        }

        $route = $this->routes[$this->next];
        $this->next++;
        return $route;
    }

}