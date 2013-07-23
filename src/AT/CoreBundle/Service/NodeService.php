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
use AT\CoreBundle\Entity\Repository\OperatorParameterItemRepository;
use AT\CoreBundle\Entity\Repository\OperatorParameterRepository;
use AT\CoreBundle\Entity\Repository\OperatorRepository;
use AT\CoreBundle\Entity\Repository\ScalarRepository;
use AT\CoreBundle\Entity\Repository\StringRepository;
use AT\CoreBundle\Entity\Scalar;
use AT\CoreBundle\Entity\String;
use AT\CoreBundle\Model\Node;
use AT\CoreBundle\Model\NodeList;

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

    /**
     * @var OperatorRepository
     */
    public $operatorRepository;

    /**
     * @var OperatorParameterItemRepository
     */
    public $operatorParameterItemRepository;

    public function __construct(OperatorRepository $operatorRepository,
                                OperatorNameRepository $operatorNameRepository,
                                OperatorParameterRepository $operatorParameterRepository,
                                OperatorParameterItemRepository $operatorParameterItemRepository,
                                StringRepository $stringRepository,
                                ScalarRepository $scalarRepository)
    {
        $this->operatorRepository = $operatorRepository;
        $this->operatorNameRepository = $operatorNameRepository;
        $this->stringRepository = $stringRepository;
        $this->scalarRepository = $scalarRepository;
        $this->operatorParameterRepository = $operatorParameterRepository;
        $this->operatorParameterItemRepository = $operatorParameterItemRepository;
    }


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
            foreach($node as $order=>$item){
                $value = $this->traverse($item, $parent);
                $list[] = $this->createOperatorParameterItem(null, $value, $order);
            }
            return $list;
        } else {
            $scalar = new Scalar($node);
            $scalar->setValue($node);
            return $scalar;
        }
    }


    /**
     * @param $name
     * @param \AT\CoreBundle\Entity\Operator|null $parent
     * @return Operator
     */
    private function createOperator($name, Operator $parent = null)
    {
        $operator = new Operator();
        $nameEntity = $this->operatorNameRepository->findOneBy(array('name' => $name));
        if(empty($nameEntity)){
            $nameEntity = $this->createOperatorName($name);
        }
        $operator->setName($nameEntity);

        if($parent){
            $operator->setParent($parent);
            $operator->setAncestors("{$parent->getAncestors()}{$parent->getId()}.");
        } else {
            $operator->setAncestors("");
        }
        $this->operatorRepository->store($operator, true);
        return $operator;
    }

    /**
     * @param $operatorParameter
     * @param $value
     * @param int $order
     * @return OperatorParameterItem
     */
    private function createOperatorParameterItem($operatorParameter, $value, $order, $flush = false)
    {
        $operatorParameterItem = new OperatorParameterItem();
        if ($value instanceof Operator){
            $operatorParameterItem->setOperator($value);
        } else if($value instanceof Scalar){
            $operatorParameterItem->setScalar($value);
        }

        $operatorParameterItem->setOperatorParameter($operatorParameter);
        $operatorParameterItem->setPosition($order);
        $this->operatorParameterItemRepository->store($operatorParameterItem, $flush);
        return $operatorParameterItem;
    }

    private function createOperatorParameter($operator, $attrName, $attrValue)
    {
        $param = new OperatorParameter();
        $param->setOperator($operator);
        $param->setName($attrName);
        $this->operatorParameterRepository->store($param, true);

        if(is_array($attrValue)){
            foreach($attrValue as $item){
                $item->setOperatorParameter($param);
            }
            $param->setItems($attrValue);
        } else if($attrValue instanceof Operator){
            $item = $this->createOperatorParameterItem($param, $attrValue, 0, true);
            $item->setOperatorParameter($param);
            $param->setItems(array(
                $item
            ));
        } else {
            $param->setScalar(
                $attrValue
            );
        }
        $this->operatorParameterRepository->store($param, true);
        return $param;
    }

    private function createOperatorName($name)
    {
        $nameEntity = new OperatorName();
        $nameEntity->setName($name);
        $this->operatorNameRepository->store($nameEntity, true);
        return $nameEntity;
    }

    /**
     * @param $attrValue
     * @param bool $andFlush
     * @return \AT\CoreBundle\Entity\String
     */
    private function createString($attrValue, $andFlush = false)
    {
        $string = new String($attrValue);
        $this->stringRepository->store($string, $andFlush);
        return $string;
    }
}