<?php

namespace RezT\Http;

/**
 * HTTP protocol constants and static methods for working with them.
 */
class HttpProtocol {

    const HTTP_1 = "HTTP/1.0";
    const HTTP_1_1 = "HTTP/1.1";
    const HTTP_2 = "HTTP/2.0";

    /**
     * Return the HTTP protocol for the global PHP request.
     *
     * @return  string
     */
    public static function fromGlobal() {
        return strtoupper($_SERVER["SERVER_PROTOCOL"]);
    }

}