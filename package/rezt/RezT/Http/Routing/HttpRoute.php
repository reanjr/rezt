<?php

namespace RezT\Http\Routing;

use RezT\Http\HttpRequest;
use RezT\Utility\ArrayTrait;
use IteratorAggregate;
use ArrayAccess;
use Exception;

/**
 * HTTP route rule.
 */
class HttpRoute implements ArrayAccess, IteratorAggregate  {
    use ArrayTrait;

    const RESULT_NO_MATCH = 0x0;
    const RESULT_METHOD_MATCH = 0x1;
    const RESULT_PATH_MATCH = 0x2;
    const RESULT_MATCH = 0x3;

    protected $methodRule = null;
    protected $pathRule = null;
    protected $exceptRule = null;
    protected $errorRule = null;
    protected $testMethodFunction = null;
    protected $testPathFunction = null;
    protected $testExceptionFunction = null;
    protected $testErrorFunction = null;
    protected $handler = null;
    protected $forwarder = null;
    protected $data = [];

    /**
     * Set the route rule and handler.
     *
     * Process rule in the following manner:
     *  - empty rule matches the path "/"
     *  - (:all) in terminal position matches one or more path segments
     *  - terminal "/" wildcard matches anything or nothing
     *  - segmented match before (:all) or terminal "/"
     *  - segments beginning in "." do not match
     *  - (:num) matches single integer segment
     *  - (:any) matches single segment
     *
     * @param   string  $rule
     * @param   callabl $handler
     */
    public function __construct($rule, callable $handler) {
        $this->setRule($rule);
        $this->setHandler($handler);
    }

    /**
     * Create a root route.  This route will match requests to "/".
     *
     * @param   callable                $handler
     * @return  \RezT\Http\Routing\HttpRoute
     */
    public static function createRootRoute(callable $handler) {
        return new HttpRoute("*", $handler);
    }

    /**
     * Create a homepage route.  This route will match GET requests to "/".
     *
     * @param   callable                $handler
     * @return  \RezT\Http\Routing\HttpRoute
     */
    public static function createHomepageRoute(callable $handler) {
        return new HttpRoute("GET", $handler);
    }

    /**
     * Create a fallback route.  This route always matches.
     *
     * @param   callable                $handler
     * @return  \RezT\Http\Routing\HttpRoute
     */
    public static function createFallbackRoute(callable $handler) {
        return new HttpRoute("* /", $handler);
    }

    /**
     * Return true if the request matches this route.  If result flags is
     * provided, it will be set to indicate which parts of the match were
     * successful.
     *
     * @param   \RezT\Http\HttpRequest|Exception|int    $request
     * @param   int                                     $resultFlag
     * @return  boolean
     */
    public function matches($request, &$resultFlag = null) {
        if ($request instanceof HttpRequest) {
            $methodTest = $this->testMethodFunction;
            $pathTest = $this->testPathFunction;
            if (!is_callable($methodTest) || !is_callable($pathTest))
                return false;

            $this->data = [];
            $resultFlag = 0;
            if ($methodTest($request)) $resultFlag |= self::RESULT_METHOD_MATCH;
            if ($pathTest($request)) $resultFlag |= self::RESULT_PATH_MATCH;
            return $resultFlag == self::RESULT_MATCH;
        }

        elseif ($request instanceof Exception) {
            $exceptionTest = $this->testExceptionFunction;
            if (!is_callable($exceptionTest))
                return false;

            $this->data = [];
            $resultFlag = 0;
            return $exceptionTest($request);
        }

        elseif (is_int($request)) {
            $errorTest = $this->testErrorFunction;
            if (!is_callable($errorTest))
                return false;

            $this->data = [];
            $resultFlag = 0;
            return $errorTest($request);
        }
    }

    /**
     * Set the route rule.  The first token in the rule is the method rule, the
     * second is the path rule.  Any additional tokens are comments.
     *
     * @param   string  $rule
     */
    public function setRule($rule) {
        $rule = trim(preg_replace("@\s+@", " ", $rule));
        $ruleParts = explode(" ", $rule);

        $methodRule = array_shift($ruleParts);
        if ($methodRule == "except") {
            $this->setExceptRule(array_shift($ruleParts));
        } elseif ($methodRule == "error") {
            $this->setErrorRule(array_shift($ruleParts));
        } else {
            $this->setMethodRule($methodRule);
            $this->setPathRule(array_shift($ruleParts));
        }
    }

    /**
     * Set the method rule.  The rule may be a wildcard "*", a specific method
     * such as "GET" or a pipe-delimited list of methods like "GET|POST".  If
     * the rule does not match one of these forms, set it to "*".
     *
     * @param   string  $methodRule
     */
    public function setMethodRule($methodRule) {
        // check for wildcard rule
        if ($methodRule == "*") {
            $this->methodRule = "*";
            $this->testMethodFunction = function(HttpRequest $request) {
                return true;
            };
        }

        // check for single method rule
        elseif (preg_match("@^[A-Z]+$@", $methodRule)) {
            $method = $methodRule;
            $this->methodRule = $methodRule;
            $this->testMethodFunction = function(HttpRequest $request) use($method) {
                return $request->getMethod() == $method;
            };
        }

        // check for method choice rule
        elseif (preg_match("@^[A-Z]+(\|[A-Z]+)*$@", $methodRule)) {
            $methods = array_unique(explode("|", $methodRule));
            $this->methodRule = implode("|", $methods);
            $this->testMethodFunction = function(HttpRequest $request) use($methods) {
                return in_array($request->getMethod(), $methods);
            };
        }

        // as a fallback, just set to *
        else {
            $this->setMethodRule("*");
        }
    }

    /**
     * Return the method rule.
     *
     * @return  string
     */
    public function getMethodRule() {
        return $this->methodRule;
    }

    /**
     * Set the path rule.
     *
     * @param   string  $pathRule
     */
    public function setPathRule($pathRule) {
        $this->pathRule = $pathRule;

        // this for closure binding
        $route = $this;

        // empty path rule matches the root path "/"
        if ($pathRule == "") {
            $this->testPathFunction = function(HttpRequest $request) use($route) {
                return $request->getResourcePath() == "/";
            };
            return;
        }

        // initialize regex patterns list to match path
        $patterns = [];

        // check for terminal (:all) segment
        if (preg_match("@(^|/)\(:all\)$@", $pathRule, $matches)) {
            $patterns[] = "([^/].*)";
            $pathRule = substr($pathRule, 0, -strlen($matches[0]));
        }

        // check for terminal slash
        elseif (substr($pathRule, -1) == "/") {
            $patterns[] = "(.*)";
            $pathRule = substr($pathRule, 0, -1);
        }

        // process the rest of the rule in segments
        $segmentRules = explode("/", $pathRule);
        while ($segmentRule = array_pop($segmentRules)) {
            switch ($segmentRule) {
                case "(:num)":
                    array_unshift($patterns, "(\d+)");
                    break;

                case "(:any)":
                    array_unshift($patterns, "([^/]+)");
                    break;

                default:
                    array_unshift($patterns, preg_quote($segmentRule, "@"));
            }
        }

        // build full pattern and set path test function
        $pattern = implode("/", $patterns);
        $this->testPathFunction = function(HttpRequest $request) use($pattern, $route) {
            $uri = $request->getResourcePath();

            // reject anything with a segment beginning in "."
            if (preg_match("@/\.@", $uri))
                return false;

            // if we have a match, copy matches into route
            if (preg_match("@^/$pattern$@", $uri, $matches)) {
                // remove the whole string match
                array_shift($matches);

                // copy the rest
                foreach ($matches as $i => $value) {
                    $route[$i] = $value;
                }

                // successful match
                return true;
            }

            // no match
            return false;
        };
    }

    /**
     * Return the path rule.
     *
     * @return  string
     */
    public function getPathRule() {
        return $this->pathRule;
    }

    /**
     * Set the except rule.  To match an exception must extend from the class
     * specified.
     *
     * @param   string  $exceptRule
     */
    public function setExceptRule($exceptRule) {
        $this->exceptRule = (string)$exceptRule;
        $exceptClass = $this->exceptRule;
        $this->testExceptionFunction = function(Exception $e) use($exceptClass) {
            return $e instanceof $exceptClass;
        };
    }

    /**
     * Return the except rule.
     *
     * @return  string
     */
    public function getExceptRule() {
        return $this->exceptRule;
    }

    /**
     * Set the error rule.  To match, an error status must match the one
     * specified.
     *
     * @param   int     $errorRule
     */
    public function setErrorRule($errorRule) {
        $this->errorRule = (int)$errorRule;
        $errorRule = $this->errorRule;
        $this->testErrorFunction = function($status) use($errorRule) {
            return $status == $errorRule;
        };
    }

    /**
     * Return the error rule.
     *
     * @return  int
     */
    public function getErrorRule() {
        return $this->errorRule;
    }

    /**
     * Set the handler for this route.
     *
     * @param   callable    $handler
     */
    public function setHandler(callable $handler) {
        $this->handler = $handler;
    }

    /**
     * Return the handler for this route.
     *
     * @return  callable
     */
    public function getHandler() {
        return $this->handler;
    }

    /**
     * Set the forwarder for this route.  Return this instance for chaining.
     *
     * @param   \Http\Routing\HttpRoute $forwarder
     * @return  \Http\Routing\HttpRoute
     */
    public function setForwarder(HttpRoute $forwarder) {
        $this->forwarder = $forwarder;
        return $this;
    }

    /**
     * Return the forwarder for this route.
     *
     * @return  \Http\Routing\HttpRoute
     */
    public function getForwarder() {
        return $this->forwarder;
    }

}
