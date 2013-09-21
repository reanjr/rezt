<?php

namespace RezT\Template;

/**
 * Template value accumulator.  Intended to bridge together components from
 * multiple templates into a list, such as parts of a title or breadcrumbs.
 */
class Accumulator {

    protected $values = [];
    protected $delimiter = null;
    protected $template = null;
    protected $outerTemplate = null;

    /**
     * Set the delimiter to use between accumulated values when evaluated.
     *
     * @param   string  $delimiter
     */
    public function __construct($delimiter = " ") {
        $this->setDelimiter($delimiter);
    }

    /**
     * Add a value to the accumulator.
     *
     * @param   mixed   $value
     */
    public function __invoke($value) {
        $this->values[] = $value;
        return (string)$this;
    }

    /**
     * Evaluate the accumulator.  Return the result.
     *
     * @return  string
     */
    public function __toString() {
        $template = $this->getTemplate();
        if (!$template)
            $template = function($value) {return (string)$value;};

        $rendered = array_reverse(array_map($template, $this->values));
        return implode($this->getDelimiter(), $rendered);
    }

    /**
     * Set the template which will be used to render each value when the
     * accumulator is evaluated.  If no template is set, the values will be
     * evaluated as strings.
     *
     * @param   \RezT\Template\Template  $template
     */
    public function setTemplate(Template $template) {
        $this->template = $template;
    }

    /**
     * Return the template which will be used to render each value when the
     * accumulator is evaluated.
     *
     * @return  \RezT\Template\Template
     */
    public function getTemplate() {
        return $this->template;
    }

    /**
     * Set the delimiter to use between accumulated values when evaluated.
     *
     * @param   string  $delimiter
     */
    public function setDelimiter($delimiter) {
        $this->delimiter = (string)$delimiter;
    }

    /**
     * Return the delimiter to use between accumulated values when evaluated.
     *
     * @return  string
     */
    public function getDelimiter() {
        return $this->delimiter;
    }

    /**
     * Return the first item in the accumulator.
     *
     * @return  mixed
     */
    public function first() {
        return $this->values[0];
    }

    /**
     * Return the last item in the accumulator.
     *
     * @return  mixed
     */
    public function last() {
        return end($this->values);
    }

}