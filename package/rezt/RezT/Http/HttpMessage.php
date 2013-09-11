<?php

namespace RezT\Http;

/**
 * Base class for HTTP requests and responses.
 */
abstract class HttpMessage {

    protected $mediaType = null;
    protected $headers = [];
    protected $body = null;

    /**
     * Set the media type for the response.  Return this instance for chaining.
     * This value overrides the content-type header if set.
     *
     * @param   string                      $mediaType
     * @return  \RezT\Http\HttpResponse
     */
    public function setMediaType($mediaType) {
        $this->mediaType = (string)$mediaType;
        return $this;
    }

    /**
     * Return the media type for the response.
     *
     * @return  string
     */
    public function getMediaType() {
        return $this->mediaType ?: $this->getHeader("content-type");
    }

    /**
     * Clear all HTTP headers from this message.
     */
    public function clearHeaders() {
        $this->headers = [];
    }

    /**
     * Set an HTTP header.  The header name will be normalized to lowercase.  If
     * the header already exists, overwrite it.
     *
     * @param   string  $name
     * @param   string  $value
     */
    public function setHeader($name, $value) {
        $this->headers[strtolower($name)] = $value;
    }

    /**
     * Set multiple HTTP headers.  The headers names will be normalized to
     * lowercase.  If any of the headers already exist, overwrite them.  Any
     * other headers should be kept.
     *
     * @param   array   $headers
     */
    public function setHeaders(array $headers) {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }

    /**
     * Add an HTTP header.  The header name will be normalized to lowercase.  If
     * the header already exists, append a new value.
     *
     * @param   string  $name
     * @param   string  $value
     */
    public function addHeader($name, $value) {
        $normalizedName = strtolower($name);

        if (isset($this->headers[$normalizedName])) {
            $this->headers[$normalizedName] .= ", $value";
        } else {
            $this->headers[$normalizedName] = $value;
        }
    }

    /**
     * Return the value of the specified header.  Return null if the header is
     * not set.
     *
     * @param   string  $name
     * @return  string
     */
    public function getHeader($name) {
        return @$this->headers[strtolower($name)];
    }

    /**
     * Return the headers for this message.
     *
     * @return  array
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * Set the message body.
     *
     * @param   string  $body
     */
    public function setBody($body) {
        $this->body = (string)$body;
    }

    /**
     * Return the message body.
     *
     * @return  string
     */
    public function getBody() {
        return $this->body;
    }

}