<?php

namespace RezT\Utility;

/**
 * Class autoloader.  Implements PSR-0 style loading.
 */
class Loader {

    protected $path = null;

    /**
     * Set the path from which to load class files.
     *
     * @param   string  $path
     */
    public function __construct($path) {
        $this->setPath($path);
    }

    /**
     * Find the specified class file.  If found, load it.
     *
     * @param   string  $className
     */
    public function __invoke($className) {
        $classFile = $this->getClassFile($className);
        if (file_exists($classFile)) {
            require_once $classFile;
        }
    }

    /**
     * Register this loader to load classes.  Make loader the highest priority
     * loader.  If there is already a loader for a matching path, unregister the
     * existing loader before registering this instance.
     */
    public function register() {
        // unregister matching loaders
        $loaders = spl_autoload_functions();
        if ($loaders) {
            $path = $this->getCanonicalPath();
            foreach ($loaders as $loader) {
                if ($loader instanceof Loader) {
                    if ($loader->getCanonicalPath() == $path) {
                        $loader->unregister();
                    }
                }
            }
        }

        // register this loader
        spl_autoload_register($this, true, true);   // throw and prepend
    }

    /**
     * Unregister this loader so that it no longer loads classes.
     */
    public function unregister() {
        spl_autoload_unregister($this);
    }

    /**
     * Return the path to the class file for the specified class.
     *
     * @param   string  $className
     * @return  string
     */
    public function getClassFile($className) {
        return $this->getPath()."/".str_replace("\\","/",$className).".php";
    }

    /**
     * Set the path from which to load class files.
     *
     * @param   string  $path
     */
    public function setPath($path) {
        $this->path = (string)$path;
    }

    /**
     * Return the path from which to load class files.
     *
     * @return  string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Return the canonical path for this instance.
     *
     * @return  string
     */
    public function getCanonicalPath() {
        return realpath($this->getPath());
    }

}