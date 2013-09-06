<?php

namespace RezT\Net;

/**
 * Internet media type constants and static methods for working with them.
 */
class MediaType {

    const BINARY = "application/octet-stream";
    const CSS = "text/css";
    const FORM = "application/x-www-form-urlencoded";
    const HTML = "text/html";
    const JS = "text/javascript";
    const JSON = "application/json";
    const MIXED = "multipart/mixed";
    const TEXT = "text/plain";
    const MP4 = "audio/mp4";

    /**
     * Send a content-type header to the client for the media type.
     *
     * @param   string  $mediaType
     */
    public static function send($mediaType) {
        header("Content-Type: $mediaType");
    }

}