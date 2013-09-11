<?php

namespace RezT;

use RezT\Http\HttpHost;

/**
 * Base class for application host.
 */
abstract class Host {

    protected static $instance = null;

    /**
     * Create and return a default host based on the global PHP environment.
     *
     * @return  \RezT\Host;
     */
    public static function fromGlobal() {
        if (php_sapi_name() == "cli") {
            throw new RuntimeException("CLI host is unsupported");
        } else {
            return HttpHost::fromGlobal();
        }
    }

    /**
     * Return the current active host instance.
     *
     * @return  \RezT\Host
     */
    public static function current() {
        if (!self::$instance)
            self::activate(self::fromGlobal());
        return self::$instance;
    }

    /**
     * Activate the provided host instance and return the host instance that was
     * active before this method was called.
     *
     * @param   \RezT\Host  $host
     */
    public static function activate(Host $host) {
        self::$instance = $host;
    }

}