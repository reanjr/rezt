<?php

namespace RezT\Resource;
use RezT\Net\MediaType;

/**
 * Static resource object.
 */
class Resource {

    /** @var \RezT\Resource\ResourceLoader $loader */
    protected $loader = null;

    protected $mediaType = MediaType::BINARY;
    protected $resourceFile = null;
    protected $content = null;

    /**
     * Set the resource file path.
     *
     * @param   string  $resourceFile
     */
    public function __construct($resourceFile) {
        $this->setResourceFile($resourceFile);
    }

    /**
     * Return the resource content.
     *
     * @return  string
     */
    public function __toString() {
        if (!$this->content)
            $this->content = file_get_contents($this->getResourceFile());
        return $this->content;
    }

    /**
     * Set the resource file path.  Return this instance for chaining.
     *
     * @param   string  $resourceFile
     * @return  \RezT\Resource\Resource
     */
    public function setResourceFile($resourceFile) {
        $this->resourceFile = (string)$resourceFile;
    }

    /**
     * Return the resource file path.
     */
    public function getResourceFile() {
        return $this->resourceFile;
    }

    /**
     * Set the media type for the resource.  Return this instance for chaining.
     *
     * @param   string  $mediaType
     * @return  \RezT\Resource\Resource
     */
    public function setMediaType($mediaType) {
        $this->mediaType = (string)$mediaType;
        return $this;
    }

    /**
     * Return the media type for the resource.
     *
     * @return  string
     */
    public function getMediaType() {
        return $this->mediaType;
    }

    /**
     * Set the resource loader that loaded this resource.  Return this instance
     * for chaining.
     *
     * @param   \RezT\Resource\ResourceLoader   $loader
     * @return  \RezT\Resource\Resource
     */
    public function setLoader(ResourceLoader $loader) {
        $this->loader = $loader;
        return $this;
    }

    /**
     * Return the resource loader that loaded this resource.
     *
     * @return  \RezT\Resource\ResourceLoader
     */
    public function getLoader() {
        return $this->loader;
    }

    /**
     * Return a value that can uniquely identify this resource, including its
     * current contents.  Return null if there is no way to identify the
     * resource in this manner.
     *
     * @return  string
     */
    public function getIdentifier() {
        $resourceFile = $this->getResourceFile();
        if (file_exists($resourceFile)) {
            $time = filemtime($resourceFile);
            $hash = md5_file($resourceFile);
            return sha1($resourceFile.$time.$hash);
        }
    }

}