<?php

namespace RezT\Http;

/**
 * HTTP method constants and static methods for working with them.
 */
class HttpMethod {

    const OPTIONS = "OPTIONS";
    const GET = "GET";
    const HEAD = "HEAD";
    const POST = "POST";
    const PUT = "PUT";
    const DELETE = "DELETE";
    const TRACE = "TRACE";
    const CONNECT = "CONNECT";

    /**
     * Return the HTTP method for the global PHP request.
     *
     * @return  string
     */
    public static function fromGlobal() {
        return strtoupper($_SERVER["REQUEST_METHOD"]);
    }

}