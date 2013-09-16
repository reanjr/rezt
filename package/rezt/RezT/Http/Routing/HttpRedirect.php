<?php

namespace RezT\Http\Routing;

use RezT\Http\HttpResponse;
use RezT\Http\HttpStatus;

/**
 * Static helpers for creating route redirects.
 */
class HttpRedirect {

    /**
     * Create and return a route handler that redirects the client for a
     * permanent redirect.
     *
     * @param   string      $uri
     * @return  callable
     */
    public static function permanent($uri) {
        return function($req, HttpResponse $rsp) use($uri) {
            $rsp->setStatus(HttpStatus::MOVED_PERMANENTLY);
            $rsp->setHeader("Location", $uri);
            $rsp->send();
        };
    }

    /**
     * Create and return a route handler that redirects the client for a
     * temporary redirect.
     *
     * @param   string      $uri
     * @return  callable
     */
    public static function temporary($uri) {
        return function($req, HttpResponse $rsp) use($uri) {
            $rsp->setStatus(HttpStatus::TEMPORARY_REDIRECT);
            $rsp->setHeader("Location", $uri);
            $rsp->send();
        };
    }

}