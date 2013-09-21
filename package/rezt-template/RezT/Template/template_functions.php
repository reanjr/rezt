<?php

use RezT\Template\Template;

/**
 * Begin buffering output.
 */
function buf() {
   Template::current()->buf();
}

/**
 * Stop buffering output, wrap any buffered content in a wrapper template, and
 * send the result.
 *
 * @param   string  $wrapper
 */
function wrap($wrapper, $params = []) {
   Template::current()->wrap($wrapper, $params);
}

/**
 * Inject the named template.  If the second argument is an array or object, the
 * array values or public object properties will also be imported as top-level
 * paramters.
 *
 * @param   string  $templateName
 * @param   mixed   $argument
 * @param   array   $params
 */
function inject($templateName, $argument = null, $params = []) {
    Template::current()->inject($templateName, $argument, $params);
}

/**
 * Inject the template once for each argument.
 *
 * @param   string  $templateName
 * @param   array   $arguments
 * @param   array   $params
 */
function loop($templateName, $arguments = [], $params = []) {
    Template::current()->loop($templateName, $arguments, $params);
}

/**
 * Generate a full URI from the provided relative URI and return the result
 * prepared for injection into HTML.
 *
 * @param   string  $relativeUri
 * @return  string
 */
function href($relativeUri) {
    return html(Template::current()->href($relativeUri));
}

/**
 * Escape for text for injection into HTML.
 *
 * @param   string  $text
 * @return  string
 */
function html($text) {
    return htmlspecialchars($text, ENT_QUOTES);
}

/**
 * Escape for text for injection into a URL.
 *
 * @param   string  $text
 * @return  string
 */
function url($text) {
    return urlencode($text);
}

/**
 * Format a timestamp in W3C format for UTC.
 *
 * @param   int     $timestamp
 * @return  string
 */
function utc($timestamp) {
    return date(DATE_W3C, $timestamp);
}

/**
 * Format a timestamp to display its age (5 days ago, yesterday, etc.).
 *
 * @param   int     $timestamp
 * @return  string
 */
function age($timestamp) {
    $age = time() - $timestamp;
    if ($age == 0)
        return "just now";
    $future = ($age < 0);
    $age = abs($age);

    $age = (int)($age / 60);        // minutes ago
    if ($age == 0) return $future ? "momentarily" : "just now";

    $scales = [
        ["minute", "minutes", 60],
        ["hour", "hours", 24],
        ["day", "days", 7],
        ["week", "weeks", 4.348214286],     // average with leap year every 4 years
        ["month", "months", 12],
        ["year", "years", 10],
        ["decade", "decades", 10],
        ["century", "centuries", 1000],
        ["millenium", "millenia", PHP_INT_MAX]
    ];

    foreach ($scales as $scale) {
        $singular = $scale[0];
        $plural = $scale[1];
        $factor = $scale[2];
        if ($age == 0)
            return $future
                ? "in less than 1 $singular"
                : "less than 1 $singular ago";
        if ($age == 1)
            return $future
                ? "in 1 $singular"
                : "1 $singular ago";
        if ($age < $factor)
            return $future
                ? "in $age $plural"
                : "$age $plural ago";
        $age = (int)($age / $factor);
    }
}
