<?php

namespace RezT\Http;

use RezT\Net\MediaType;

/**
 * HTTP request object.
 */
class HttpRequest extends HttpMessage {

    protected $method = null;
    protected $uri = null;
    protected $path = null;
    protected $query = null;
    protected $resourcePath = null;
    protected $parsedQuery = null;
    protected $protocol = null;

    /**
     * Create a request from the PHP globals and return the request object.
     *
     * @return  \RezT\Http\HttpRequest
     */
    public static function fromGlobal() {
        $request = new HttpRequest();

        // assign headers
        $headers = self::getGlobalHeaders();
        $request->setHeaders($headers);

        // check for method override in headers and set method
        $method = $request->getHeader("x-http-method") ?: HttpMethod::fromGlobal();
        $request->setMethod($method);

        // set the URI, protocol, and raw body data
        $request->setUri(self::getGlobalRequestUri());
        $request->setProtocol(HttpProtocol::fromGlobal());
        $request->setBody(file_get_contents("php://input"));

        // return the request
        return $request;
    }

    /**
     * Return the global PHP headers.  Header names will be in lowercase.
     *
     * @return  array
     */
    public static function getGlobalHeaders() {
        $normalizedHeaders = [];

        if (function_exists("getallheaders")) {
            $headers = getallheaders();
        } else {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == "HTTP_") {
                    $headers[str_replace("_","-",substr($name, 5))] = $value;
                } elseif (in_array($name, ["CONTENT_TYPE","CONTENT_LENGTH"])) {
                    $headers[str_replace("_", "-", $name)] = $value;
                }
            }
        }

        foreach ($headers as $name => $value) {
            $normalizedHeaders[strtolower($name)] = $value;
        }
        return $normalizedHeaders;
    }

    /**
     * Return the request URI identified by the PHP globals.
     *
     * @return  string  Ex.: /path?query
     */
    public static function getGlobalRequestUri() {
        return $_SERVER["REQUEST_URI"];
    }

    /**
     * Return the protocol identified by the PHP globals.
     *
     * @return  string  Ex.: HTTP/1.0
     */
    public static function getGlobalProtocol() {
        return $_SERVER["SERVER_PROTOCOL"];
    }

    /**
     * Set the HTTP method for the request.
     *
     * @param   string  $method
     */
    public function setMethod($method) {
        $this->method = (string)$method;
    }

    /**
     * Return the HTTP method for the request.
     *
     * @return  string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Set the URI for the request.
     *
     * @param   string  $uri
     */
    public function setUri($uri) {
        $this->uri = (string)$uri;
        $this->path = parse_url($uri, PHP_URL_PATH);
        $this->query = parse_url($uri, PHP_URL_QUERY);
        $this->resourcePath = $this->path;
        $this->parsedQuery = null;
    }

    /**
     * Return the URI for the request.
     *
     * @return  string
     */
    public function getUri() {
        return $this->uri;
    }

    /**
     * Set the path for the request.
     *
     * @param   string  $path
     */
    public function setPath($path) {
        $this->path = (string)$path;
        $this->uri = $this->path;
        $this->resourcePath = $this->path;
        if (!empty($this->query))
            $this->uri .= "?" . $this->query;
    }

    /**
     * Return the path for the request.
     *
     * @return  string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Set the query for the request.
     *
     * @param   string  $query
     */
    public function setQuery($query) {
        $this->query = (string)$query;
        $this->uri = $this->path;
        if (!empty($this->query))
            $this->uri .= "?" . $this->query;
        $this->parsedQuery = null;
    }

    /**
     * Return the query for the request.
     *
     * @return  string
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * Parse the query according to standard PHP handling of GET strings and
     * return the results.  If a name is provided, return the value of that
     * query parameter.
     *
     * @param   string  $name
     * @return  mixed
     */
    public function parseQuery($name = null) {
        // parse and cache query data
        if (is_null($this->parsedQuery)) {
            $this->parsedQuery = [];
            $query = $this->getQuery();
            parse_str($query, $this->parsedQuery);
        }

        return empty($name)
            ? $this->parsedQuery
            : $this->parsedQuery[(string)$name];
    }

    /**
     * Set the resource path for the request.  This may not match the original
     * path returned by getPath.  The resource path may be rewritten during
     * routing.
     *
     * @param   string  $resourcePath
     */
    public function setResourcePath($resourcePath) {
        $this->resourcePath = (string)$resourcePath;
    }

    /**
     * Return the resource path for the request.
     *
     * @return  string
     */
    public function getResourcePath() {
        return $this->resourcePath;
    }

    /**
     * Return the resource path with the leading slash stripped.  This should be
     * used when a relative path is needed for constructing paths.
     *
     * @return  string
     */
    public function getRelativeResourcePath() {
        return ltrim($this->getResourcePath(), "/");
    }

    /**
     * Set the protocol for the request.
     *
     * @param   string  $protocol
     */
    public function setProtocol($protocol) {
        $this->protocol = (string)$protocol;
    }

    /**
     * Return the protocol for the request.
     *
     * @return  string
     */
    public function getProtocol() {
        return $this->protocol;
    }

    /**
     * Set request data.  If called with a single argument, all data is cleared
     * and overwritten with the new value.  If called with two arguments, the
     * first must be a string, and a new data field with that name will be set
     * to the value of the second argument.
     *
     * @param   string|mixed    $nameOrData
     * @param   mixed           $value
     */
    public function setData($nameOrData, $value) {
        // for one argument, just write directly to the body
        if (func_get_args() == 1) {
            $this->body = $nameOrData;
        }

        // otherwise, set a named value
        else {
            if (!is_array($this->body)) {
                $this->body = [];
            }
            $this->body[$nameOrData] = $value;
        }
    }

    /**
     * Return the body data in a format suitable for it's content-type.
     *
     * @return  mixed
     */
    public function getData() {
        switch ($this->getHeader("Content-Type")) {
            case MediaType::FORM:
                if (!is_array($this->body)) {
                    $data = null;
                    parse_str($this->body, $data);
                    $this->body = $data;
                }
                return $this->body;

            default:
                return $this->body;
        }
    }

}