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

namespace AT\CoreBundle\Parser;

use AT\CoreBundle\Interfaces\ParserInterface;
use AT\CoreBundle\Model\Node;
use AT\CoreBundle\Model\NodeList;
use AT\CoreBundle\Model\NodeString;
use PHPParser_Error;
use PHPParser_Lexer;
use PHPParser_Node;
use PHPParser_Node_Scalar_String;
use PHPParser_NodeTraverser;
use PHPParser_NodeVisitorAbstract;
use PHPParser_Parser;

require dirname(__FILE__).'/../../../../vendor/nikic/php-parser/lib/bootstrap.php';

class PHPParser implements ParserInterface
{
    public function __construct()
    {

    }

    /**
     * @param $code
     * @param array $options
     * @return Node|NodeList|null
     */
    public function parse($code, $options = array())
    {
        $parser     = new PHPParser_Parser(new PHPParser_Lexer);

        try {
            $stmts = $parser->parse($code);
            $stmts = $this->traverse($stmts);

        } catch (PHPParser_Error $e) {
            $stmts = null;
            echo 'Parse Error: ', $e->getMessage();
        }
        return $stmts;
    }

    public function parseModule($code, $options = array())
    {
        $result = $this->parse($code, $options);
        if($result instanceof NodeList){
            return new Node('Module', array(
                'stmts' => $result
            ));
        }
    }

    private function getNodeName(PHPParser_Node $node)
    {
        return $node->getType();
    }

    private function getNodeChildren(PHPParser_Node $node)
    {
        $nodes = array();
        foreach($node->getSubNodeNames() as $name){
            $nodes[$name] = $this->traverse($node->$name);
        }
        return $nodes;
    }

    private function traverse($stmts)
    {
        if(is_array($stmts)){
            $items = new NodeList();
            foreach($stmts as $node){
                $items->append($this->traverse($node));
            }
            return $items;
        } else if ($stmts instanceof PHPParser_Node) {
            return new Node($this->getNodeName($stmts), $this->getNodeChildren($stmts), $stmts->getAttributes());
        } else {
            return $stmts;
        }
    }
}