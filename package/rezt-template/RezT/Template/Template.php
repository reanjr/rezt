<?php

namespace RezT\Template;

use RezT\Resource\Resource;
use RezT\Http\HttpHost;
use RezT\Http\Routing\HttpApplication;
use RezT\Utility\ArrayTrait;
use RezT\Utility\SingletonTrait;
use IteratorAggregate;
use ArrayAccess;
use RuntimeException;

/**
 * Template object model.
 *
 * @method  \RezT\Template\Template  current()
 */
class Template extends Resource implements IteratorAggregate, ArrayAccess {
    use ArrayTrait;
    use SingletonTrait;

    protected $baseUri = null;
    protected $data = [];
    protected $bufferLevel = 0;

    /**
     * Set the path to the template resource file.
     *
     * @param   string  $resourceFile
     */
    public function __construct($resourceFile = "") {
        parent::__construct($resourceFile);
    }

    /**
     * Execute the template and return the result.
     *
     * @param   mixed   $arg
     * @return  string
     */
    public function __invoke($arg = null) {
        $__tfile = $this->getResourceFile();
        if (!$__tfile) {
            $name = $this->getName();
            throw new RuntimeException("could not find template $name");
        }

        $__targ = $arg;
        $__tdata = $this->data;
        $__tpl = $this;

        extract($__tdata);

        $__tprev = self::swap($this);

        ob_start();
        try {
            require_once __DIR__ . "/template_functions.php";
            require $__tfile;
        }
        catch (Exception $e) {
            $this->content = ob_get_clean();
            throw $e;
        }
        $this->content = ob_get_clean();

        $__tprev && self::swap($__tprev);
        return $this->content;
    }

    /**
     * Begin buffering output.
     */
    public function buf() {
        $this->bufferLevel++;
        ob_start();
    }

    /**
     * Close the last buffer, wrap the buffered content in a wrapper and send
     * the result.
     *
     * @param   string  $wrapper
     * @param   array   $params
     */
    public function wrap($wrapper, $params = []) {
        if ($this->bufferLevel == 0)
            throw new RuntimeException("no buffer available to wrap");

        $wrapperTemplate = $this->getLoader()->fetch($wrapper);

        foreach ($this as $name => $value)
            $wrapperTemplate[$name] = $value;
        foreach ($params as $name => $value)
            $wrapperTemplate[$name] = $value;

        echo $wrapperTemplate(ob_get_clean());
        $this->bufferLevel--;
    }

    /**
     * Inject the named template.  If the second argument is an array or object,
     * the array values or public object properties will also be imported as
     * top-level paramters.
     *
     * @param   string  $templateName
     * @param   mixed   $argument
     * @param   array   $params
     */
    public function inject($templateName, $argument = null, $params = []) {
        $template = $this->getLoader()->fetch($templateName);

        foreach ($this as $name => $value)
            $template[$name] = $value;
        if (is_array($argument) || is_object($argument))
            foreach ($argument as $name => $value)
                $template[$name] = $value;
        foreach ($params as $name => $value)
            $template[$name] = $value;

        echo $template($argument);
    }

    /**
     * Inject the template once for each argument.
     *
     * @param   string  $templateName
     * @param   array   $arguments
     * @param   array   $params
     */
    public function loop($templateName, $arguments = [], $params = []) {
        foreach ($arguments as $argument)
            $this->inject($templateName, $argument, $params);
    }

    /**
     * Generate a full URI from the provided relative URI.
     *
     * @param   string  $relativeUri
     * @return  string
     */
    public function href($relativeUri) {
        return $this->getBaseUri() . "/$relativeUri";
    }

    /**
     * Set the base URI to use when generating URLs with the href method/
     *
     * @param   string  $baseUri
     */
    public function setBaseUri($baseUri) {
        $this->baseUri = (string)$baseUri;
    }

    /**
     * Return the base URI.  If one is not set, attempt to get one from the
     * current HTTP host.
     *
     * @return  string
     */
    public function getBaseUri() {
        if (empty($this->baseUri)) {
            // grab the current HTTP host
            $host = HttpHost::current();

            // if host has a getBaseUri method, try that first
            if (method_exists($host, "getBaseUri")) {
                if ($host->getBaseUri()) return $host->getBaseUri();
            }

            // otherwise, try to strip app-specific path from request path
            $request = HttpHost::current()->getRequest();
            $fullPath = $request->getPath();
            $routedPath = $request->getResourcePath();

            // see if routed path ends fullpath
            if (substr($fullPath, -strlen($routedPath)) == $routedPath) {
                return substr($fullPath, 0, -strlen($routedPath));
            }
        }

        return $this->baseUri;
    }

}
