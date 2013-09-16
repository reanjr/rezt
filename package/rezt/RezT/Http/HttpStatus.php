<?php

namespace RezT\Http;

use ReflectionClass;

/**
 * HTTP status constants and static methods for working with them.
 */
class HttpStatus {

    const CLASS_INFORMATIONAL = 100;
    const CLASS_SUCCESSFUL = 200;
    const CLASS_REDIRECTION = 300;
    const CLASS_CLIENT_ERROR = 400;
    const CLASS_SERVER_ERROR = 500;

    const CONTINUE_ = 100;
    const SWITCHING_PROTOCOLS = 101;

    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NON_AUTHORITATIVE_INFORMATION = 203;
    const NO_CONTENT = 204;
    const RESET_CONTENT = 205;
    const PARTIAL_CONTENT = 206;

    const MULTIPLE_CHOICES = 300;
    const MOVED_PERMANENTLY = 301;
    const FOUND = 302;
    const SEE_OTHER = 303;
    const NOT_MODIFIED = 304;
    const USE_PROXY = 305;
    const TEMPORARY_REDIRECT = 307;

    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const PROXY_AUTHENTICATION_REQUIRED = 407;
    const REQUEST_TIME_OUT = 408;
    const CONFLICT = 409;
    const GONE = 410;
    const LENGTH_REQUIRED = 411;
    const PRECONDITION_FAILED = 412;
    const PAYLOAD_TOO_LARGE = 413;
    const URI_TOO_LONG = 414;
    const UNSUPPORTED_MEDIA_TYPE = 415;
    const RANGE_NOT_SATISFIABLE = 415;
    const EXPECTATION_FAILED = 417;
    const UPGRADE_REQUIRED = 426;

    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;
    const SERVICE_UNAVAILABLE = 503;
    const GATEWAY_TIME_OUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * Send the HTTP status to the client.
     *
     * @param   int     $status
     */
    public static function send($status, $protocol = HttpProtocol::HTTP_1_1) {
        header("$protocol " . (int)$status . " " . self::getMessage($status));
    }

    /**
     * Return the HTTP status message for the provided status code.
     *
     * @param   int     $status
     * @return  string
     */
    public static function getMessage($status) {
        $ref = new ReflectionClass("RezT\Http\HttpStatus");
        $constants = $ref->getConstants();

        foreach ($constants as $name => $value) {
            if (!preg_match("@^CLASS_@", $name) && $status == $value)
                return ucwords(str_replace("_", " ", strtolower($name)));
        }
    }

}