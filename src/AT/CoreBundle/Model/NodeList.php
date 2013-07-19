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


class NodeList implements \Iterator
{
    protected $index = 0;
    protected $nodes;

    public function __construct($nodes = array())
    {
        $this->index = 0;
        if($nodes){
            foreach ($nodes as $node){
                $this->nodes = $node;
            }
        }
    }

    public function append($node)
    {
        $this->nodes[] = $node;
    }

    public function toArray()
    {
        return $this->nodes;
    }

    public function current()
    {
        return $this->nodes[$this->index];
    }

    public function next()
    {
        ++$this->index;
    }

    public function key()
    {
        return $this->index;
    }

    public function valid()
    {
        return isset($this->nodes[$this->index]);
    }

    public function rewind()
    {
        $this->index = 0;
    }
}