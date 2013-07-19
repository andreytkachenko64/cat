<?php
/*
 * This file is part of ONP.
 *
 * Copyright (c) 2013 Opensoft (http://opensoftdev.com)
 *
 * The unauthorized use of this code outside the boundaries of
 * Opensoft is prohibited.
 *
 */

namespace AT\CoreBundle\Model;



class Node implements \ArrayAccess
{
    const META_LINE    = 'line';
    const META_COMMENT = 'comment';

    const TYPE_INCLUDE      = 1;
    const TYPE_INCLUDE_ONCE = 2;
    const TYPE_REQUIRE      = 3;
    const TYPE_REQUIRE_ONCE = 4;

    const MODIFIER_PUBLIC    =  1;
    const MODIFIER_PROTECTED =  2;
    const MODIFIER_PRIVATE   =  4;
    const MODIFIER_STATIC    =  8;
    const MODIFIER_ABSTRACT  = 16;
    const MODIFIER_FINAL     = 32;

    protected $name;

    protected $attributes;

    protected $platform;

    protected $module;

    protected $meta;

    public function __construct($name, $attributes = array(), $meta = array())
    {
        $this->name       = $name;
        $this->attributes = $attributes;
        $this->meta       = $meta;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setMeta($key, $value)
    {
        $this->meta[$key] = $value;
    }

    public function getMeta($key, $default = null)
    {
        return isset($this->meta[$key]) ? $this->meta[$key] : $default;
    }

    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->attributes[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}