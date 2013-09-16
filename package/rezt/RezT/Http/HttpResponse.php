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
     * Send a resource to the client.
     *
     * @param   \RezT\Resource\Resource $resource
     */
    public function sendResource(Resource $resource) {
        // send the resource media type if available
        if ($resource->getMediaType()) {
            MediaType::send($resource->getMediaType());
        }

        // evalute resource body first for chance to set media type, et al
        $body = (string)$resource;

        // send headers
        $this->sendStatus();
        $this->sendHeaders();
        $this->sendMediaType();

        // send body
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