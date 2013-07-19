<?php

namespace AT\CoreBundle\Interfaces;

interface ParserInterface
{

    public function parse($code, $options = array());
    public function parseModule($code, $options = array());

}