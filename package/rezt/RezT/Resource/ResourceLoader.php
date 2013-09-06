<?php

namespace RezT\Resource;

/**
 * Load resources from the file system.
 */
class ResourceLoader {
    protected $extensionTypes = [];
    protected $extensionHandlers = [];
    protected $resourcePaths = [];

    /**
     * Find the specified resource and return a resource object.  Return null
     * if the resource could not be found.
     *
     * @param   string                  $resourceUri
     * @return  \RezT\Resource\Resource
     */
    public function fetch($resourceUri) {
        $extension = "";        // this gets set in the evaluate call
        $resourcePath = $this->evaluateResourceUri($resourceUri, $extension);
        $resourceFile = $resourcePath . ($extension?".$extension":"");

        if (!file_exists($resourceFile))
            return null;

        $resourceClass = "RezT\Resource\Resource";
        if (!empty($extension)) {
            $type = $this->getExtensionType($extension);
            $resourceClass = $this->getExtensionHandler($extension)
                ?: $resourceClass;
        }

        $resource = new $resourceClass($resourceFile);
        $resource->setLoader($this);
        if ($type) $resource->setMediaType($type);
        return $resource;
    }

    /**
     * Evaluate the resource URI to a path.  Each resource path will be searched
     * in order.  Return the first path found to contain a matching resource.
     * When matching, check each extension in the extension map.  Do not return
     * the extension in the result.  If there is a provided extension variable,
     * copy the extension to it.  If no matching resource is found, return the
     * resource URI for the first resource path.  If there are no resource
     * paths, return null.
     *
     * @param   string  $resourceUri
     * @param   string  $extension
     * @return  string
     */
    public function evaluateResourceUri($resourceUri, &$extension) {
        // trim off a leading slash
        $resourceUri = ltrim($resourceUri, "/");

        // look for existing resources
        foreach ($this->getResousourcePaths() as $resourcePath) {
            // try with mapped extensions
            foreach ($this->getRegisteredExtensions() as $extension) {
                // if the resource ends in extension, strip it
                $extensionStart = -strlen($extension) - 1;
                $uri = $resourceUri;
                if (substr($uri, $extensionStart) == ".$extension") {
                    $uri = substr($uri, 0, $extensionStart);
                }

                $path = "$resourcePath/$uri.$extension";
                if (file_exists($path)) {
                    return "$resourcePath/$uri";
                }
            }

            // try without extension
            $path = "$resourcePath/$resourceUri";
            if (file_exists($path)) {
                return $path;
            }
        }

        // just use the first resource path as a fallback
        $resourcePaths = $this->getResousourcePaths();
        if (!empty($resourcePaths)) {
            $resourcePath = array_shift($resourcePaths);
            $extension = "";
            return "$resourcePath/$resourceUri";
        }

        // we could not find a path; reset the extension
        $extension = null;
    }

    /**
     * Add a map between file extension and media type.  Return this instance
     * for chaining.
     *
     * @param   string  $extension
     * @param   string  $mediaType
     * @return  \RezT\Resource\ResourceLoader
     */
    public function addExtensionType($extension, $mediaType) {
        $this->extensionTypes[$extension] = $mediaType;
        return $this;
    }

    /**
     * Setup the extension type map.  Clear the existing map and replace it with
     * the provided map.  Return this instance for chaining.
     *
     * @param   array   $extensionTypeMap
     * @return  \RezT\Resource\ResourceLoader
     */
    public function setExtensionTypeMap($extensionTypeMap) {
        $this->clearExtensionTypeMap();
        foreach ($extensionTypeMap as $extension => $mediaType) {
            $this->addExtensionType($extension, $mediaType);
        }
        return $this;
    }

    /**
     * Remove an extension type map.  Return this instance for chaining.
     *
     * @param   string  $extension
     * @return  \RezT\Resource\ResourceLoader
     */
    public function removeExtensionType($extension) {
        unset($this->extensionTypes[$extension]);
        return $this;
    }

    /**
     * Clear the extension type map.  Return this instance for chaining.
     *
     * @return  \RezT\Resource\ResourceLoader
     */
    public function clearExtensionTypeMap() {
        $this->extensionTypes = [];
        return $this;
    }

    /**
     * Return the media type for the provided extensions.
     *
     * @param   string  $extension
     * @return  string
     */
    public function getExtensionType($extension) {
        return $this->extensionTypes[$extension];
    }

    /**
     * Return the extension type map as a key-value pair of extensions and media
     * types.
     *
     * @return  array
     */
    public function getExtensionTypes() {
        return $this->extensionTypes;
    }

    /**
     * Add a file extension handler.  Return this instance for chaining.
     *
     * @param   string  $extension
     * @param   string  $handlerClassName
     * @return  \RezT\Resource\ResourceLoader
     */
    public function addExtensionHandler($extension, $handlerClassName) {
        $this->extensionHandlers[$extension] = $handlerClassName;
        return $this;
    }

    /**
     * Remove an extension handler.  Return this instance for chaining.
     *
     * @param   string  $extension
     * @return  \RezT\Resource\ResourceLoader
     */
    public function removeExtensionHandler($extension) {
        unset($this->extensionHandlers[$extension]);
        return $this;
    }

    /**
     * Clear the extension handlers.  Return this instance for chaining.
     *
     * @return  \RezT\Resource\ResourceLoader
     */
    public function clearExtensionHandlers() {
        $this->extensionHandlers = [];
        return $this;
    }

    /**
     * Return the class name of the handler for the provided extension.
     *
     * @param   string  $extension
     * @return  string
     */
    public function getExtensionHandler($extension) {
        return $this->extensionHandlers[$extension];
    }

    /**
     * Return the extension handlers as a key-value pair of extensions and
     * handlers.
     *
     * @return  array
     */
    public function getExtensionHandlers() {
        return $this->extensionHandlers;
    }

    /**
     * Return all registered extensions, including extensions with a mapped
     * type and extensions with a handler.
     *
     * @return  array
     */
    public function getRegisteredExtensions() {
        $typeExtensions = array_keys($this->getExtensionTypes());
        $handlerExtensions = array_keys($this->getExtensionHandlers());
        return array_unique(array_merge($typeExtensions, $handlerExtensions));
    }

    /**
     * Add a resource path.  Return this instance for chaining.
     *
     * @param   string  $path
     * @return  \RezT\Resource\ResourceLoader
     */
    public function addResourcePath($path) {
        $this->resourcePaths[$path] = $path;
        return $this;
    }

    /**
     * Remove a resource path.  Return this instance for chaining.
     *
     * @param   string  $path
     * @return  \RezT\Resource\ResourceLoader
     */
    public function removeResourcePath($path) {
        unset($this->resourcePaths[$path]);
        return $this;
    }

    /**
     * Return the resource paths.
     *
     * @return  array
     */
    public function getResousourcePaths() {
        return array_values($this->resourcePaths);
    }

}