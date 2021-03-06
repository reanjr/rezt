<?php

namespace RezT\Http;

use RezT\Net\MediaType;
use RezT\Resource\Resource;

/**
 * HTTP response object.
 */
class HttpResponse extends HttpMessage {

    protected $status = HttpStatus::OK;

    /**
     * Send the HTTP response to the client.
     */
    public function send() {
        $this->sendStatus();
        $this->sendHeaders();
        $this->sendMediaType();
        echo $this->getBody();
    }

    /**
     * Send the HTTP status to the client.
     */
    public function sendStatus() {
        HttpStatus::send($this->getStatus());
    }

    /**
     * Send the HTTP headers to the client.
     */
    public function sendHeaders() {
        foreach ($this->getHeaders() as $name => $value) {
            header("$name: $value");
        }
    }

    /**
     * Send the media type to the client.
     */
    public function sendMediaType() {
        if ($this->getMediaType())
            MediaType::send($this->getMediaType());
    }

    /**
     * Send a resource to the client.  Provide the client request to enable
     * cache control.
     *
     * @param   \RezT\Resource\Resource $resource
     * @param   \RezT\Http\HttpRequest  $request
     */
    public function sendResource(Resource $resource,
        HttpRequest $request = null) {

        // if the resource has a tag, set a header for it
        $resourceTag = $resource->getIdentifier();
        if ($resourceTag)
            $this->setHeader("ETag", $resourceTag);

        // if there's a request, see if client has up to date version already
        if (!empty($request)) {
            $clientTag = $request->getHeader("If-None-Match");

            // if tags match, set status to NOT MODIFIED
            if ($clientTag == $resourceTag) {
                $this->setStatus(HttpStatus::NOT_MODIFIED);
            }
        }

        // send the resource media type if available
        if ($resource->getMediaType()) {
            MediaType::send($resource->getMediaType());
        }

        // evaluate resource body first for chance to set media type, et al
        $body = (string)$resource;

        // send headers
        $this->sendStatus();
        $this->sendHeaders();
        $this->sendMediaType();

        // send body unless resource was not modified
        if ($this->getStatus() != HttpStatus::NOT_MODIFIED)
            echo $body;
    }

    /**
     * Set the HTTP status for the response.
     *
     * @param   int     $status
     */
    public function setStatus($status) {
        $this->status = (int)$status;
    }

    /**
     * Return the HTTP status for the response.
     *
     * @return  int
     */
    public function getStatus() {
        return $this->status;
    }

}