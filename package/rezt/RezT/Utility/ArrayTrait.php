<?php

namespace RezT\Utility;

use ArrayIterator;

/**
 * Use this trait to provide array-like functionality to a class.  This trait
 * implements IteratorAggregate and ArrayAccess.  Override the $dataMember
 * property to control which class member should be exposed by the object when
 * accessed as an array.
 */
trait ArrayTrait {

    protected $dataMember = "data";

    public function offsetSet($name, $value) {
        if (is_null($value))
            $this->offsetUnset($name);
        else {
            $member = $this->dataMember;
            $this->{$member}[$name] = $value;
        }
    }

    public function offsetUnset($name) {
        $member = $this->dataMember;
        unset($this->{$member}[$name]);
    }

    public function offsetGet($name) {
        $member = $this->dataMember;
        return @$this->{$member}[$name];
    }

    public function offsetExists($name) {
        $member = $this->dataMember;
        return isset($this->{$member}[$name]);
    }

    public function getIterator() {
        $member = $this->dataMember;
        return new ArrayIterator($this->$member);
    }

}