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

namespace AT\CoreBundle\Service;
use AT\CoreBundle\Entity\Operator;
use AT\CoreBundle\Entity\OperatorName;
use AT\CoreBundle\Entity\OperatorParameter;
use AT\CoreBundle\Entity\OperatorParameterItem;
use AT\CoreBundle\Entity\Repository\OperatorNameRepository;
use AT\CoreBundle\Entity\Scalar;
use AT\CoreBundle\Entity\String;
use AT\CoreBundle\Model\Node;
use AT\CoreBundle\Model\NodeList;
use Doctrine\ORM\EntityRepository;

/**
 * AT\CoreBundle\Service\NodeService
 *
 * @author Andrey Tkachenko <andrey.tkachenko@opensoftdev.ru>
 */
class NodeService
{
    /**
     * @var OperatorNameRepository
     */
    public $operatorNameRepository;
    /**;
     * @var StringRepository
     */
    public $stringRepository;

    public function retrieveNode($nodeId)
    {

    }

    public function storeNode(Node $node)
    {
        return $this->traverse($node);
    }

    private function traverse($node, $parent = null)
    {
        if($node instanceof Node){
            $operator = $this->createOperator($node->getName(), $parent);
            foreach($node->getAttributes() as $attrName => $attrValue){
                $parameter = $this->createOperatorParameter(
                    $operator, $attrName, $this->traverse($attrValue, $operator));
                $operator->addParameter($parameter);
            }
            return $operator;
        } else if ($node instanceof NodeList) {
            $list = array();
            foreach($node as $item){
                $operatorItem = new OperatorParameterItem();
                $s = $this->traverse($item, $parent);
                if ($s instanceof Operator){
                    $operatorItem->setOperator($s);
                } else if ($s instanceof Scalar){
                    $operatorItem->setScalar($s);
                }
                $list[] = $operatorItem;
            }
            return $list;
        } else {
            $scalar = new Scalar($node);
            return $scalar;
        }
    }


    /**
     * @param $name
     * @return Operator
     */
    private function createOperator($name, Operator $parent = null)
    {
        $operator = new Operator();
        $nameEntity = null;//$this->operatorNameRepository->findBy(array('name' => $name));
        if(empty($nameEntity)){
            $nameEntity = $this->createOperatorName($name);
        }
        $operator->setName($nameEntity);

        if($parent){
            $operator->setParent($parent);
            $operator->setAncestors("{$parent->getAncestors()}{$parent->getId()}.");
        }

        return $operator;
    }

    /**
     * @param $operatorParameter
     * @param $value
     * @param int $order
     * @return OperatorParameterItem
     */
    private function createOperatorParameterItem($operatorParameter, $value, $order = 0)
    {
        $operatorParameterItem = new OperatorParameterItem();
        if ($value instanceof Operator){
            $operatorParameterItem->setOperator($value);
        } else if($value instanceof Scalar){
            $operatorParameterItem->setScalar($value);
        }

        $operatorParameterItem->setOperatorParameter($operatorParameter);
        $operatorParameterItem->setOrder($order);
        return $operatorParameterItem;
    }

    private function createOperatorParameter($operator, $attrName, $attrValue)
    {
        $param = new OperatorParameter();
        $param->setOperator($operator);
        $param->setName($attrName);
        if(is_array($attrValue)){
            $param->setItems($attrValue);
        } else if($attrValue instanceof Operator){
            $param->setItems(array(
                $this->createOperatorParameterItem($param, $attrValue)
            ));
        } else {
            $param->setString(
                $this->createString($attrValue)
            );
        }

        return $param;
    }

    private function createOperatorName($name)
    {
        $nameEntity = new OperatorName();
        $nameEntity->setName($name);
        //$this->operatorNameRepository->store($nameEntity, true);
        return $nameEntity;
    }

    /**
     * @param $attrValue
     * @return \AT\CoreBundle\Entity\String
     */
    private function createString($attrValue)
    {
        $string = new String($attrValue);
        //$this->stringRepository->store($string, true);
        return $string;
    }
}