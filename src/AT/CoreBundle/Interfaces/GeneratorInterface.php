<?php

namespace AT\CoreBundle\Interfaces;

use AT\CoreBundle\Model\NodeList;

interface GeneratorInterface
{

    public function generate(NodeList $statements, array $options = array());

}