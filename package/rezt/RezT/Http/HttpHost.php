<?php

namespace RezT\Http;

use RezT\Http\HttpRequest;
use RezT\Host;

/**
 * Application host for HTTP (web) environment.
 */
class HttpHost extends Host {

    protected $request = null;

    /**
     * Set the HTTP request.
     *
     * @param   \RezT\Http\HttpRequest
     */
    public function __construct(HttpRequest $request) {
        $this->setRequest($request);
    }

    /**
     * Return the current active HTTP host instance.
     *
     * @return  \RezT\HttpHost
     */
    public static function current() {
        $current = parent::current();
        if (!$current instanceof HttpHost)
            throw new RuntimeException("must execute in HTTP environment");
        return $current;
    }

    /**
     * Create and return a default HTTP host based on the global PHP
     * environment.
     *
     * @return  \RezT\HttpHost;
     */
    public static function fromGlobal() {
        return new HttpHost(HttpRequest::fromGlobal());
    }

    /**
     * Set the host request to be passed to the application.
     *
     * @param   \RezT\Http\HttpRequest  $request
     */
    public function setRequest($request) {
        $this->request = $request;
    }

    /**
     * Return the host request to be passed to the application.
     *
     * @return  \RezT\Http\HttpRequest
     */
    public function getRequest() {
        return $this->request;
    }

}