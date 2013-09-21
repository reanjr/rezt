<?php

namespace RezT\Utility;

use InvalidArgumentException;

/**
 * Use this trait to support a registered singleton instance of your class.  It
 * is up to the class to ensure its constructor is private if required.
 */
trait SingletonTrait {

    protected static $instance = null;

    /**
     * Return the current singleton instance.  If one does not exist, attempt to
     * create it.
     */
    public static function current() {
        $class = get_called_class();
        if (!$class::$instance) {
            $class::$instance = new $class();
        }
        return $class::$instance;
    }

    /**
     * Swap out the current singleton instance.  Return the previous instance if
     * one is set.
     */
    public static function swap($instance) {
        $class = get_called_class();
        if (!$instance instanceof $class)
            throw new InvalidArgumentException("argument must be instance of $class");

        $old = $class::$instance;
        $class::$instance = $instance;
        return $old;
    }

}