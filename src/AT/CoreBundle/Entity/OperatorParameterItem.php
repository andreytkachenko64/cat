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

namespace AT\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AT\CoreBundle\Entity\OperatorParameter
 *
 * @author Andrey Tkachenko <andrey.tkachenko@opensoftdev.ru>
 * @ORM\Entity(repositoryClass="AT\CoreBundle\Entity\Repository\OperatorParameterItemRepository")
 *
 * @ORM\Table(name="operator_parameter_items")
 */
class OperatorParameterItem
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AT\CoreBundle\Entity\OperatorParameter", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(name="operator_param_id", referencedColumnName="id")
     * @var OperatorParameter
     */
    protected $operatorParameter;

    /**
     * @ORM\ManyToOne(targetEntity="AT\CoreBundle\Entity\Operator", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(name="operator_id", referencedColumnName="id", nullable=true)
     * @var Operator
     */
    protected $operator;

    /**
     * @ORM\ManyToOne(targetEntity="AT\CoreBundle\Entity\Scalar", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(name="scalar_id", referencedColumnName="id", nullable=true)
     * @var Scalar
     */
    protected $scalar;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $order;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \AT\CoreBundle\Entity\Operator $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    /**
     * @return \AT\CoreBundle\Entity\Operator
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param \AT\CoreBundle\Entity\Scalar $scalar
     */
    public function setScalar($scalar)
    {
        $this->scalar = $scalar;
    }

    /**
     * @return \AT\CoreBundle\Entity\Scalar
     */
    public function getScalar()
    {
        return $this->scalar;
    }

    /**
     * @param \AT\CoreBundle\Entity\OperatorParameter $operatorParameter
     */
    public function setOperatorParameter($operatorParameter)
    {
        $this->operatorParameter = $operatorParameter;
    }

    /**
     * @return \AT\CoreBundle\Entity\OperatorParameter
     */
    public function getOperatorParameter()
    {
        return $this->operatorParameter;
    }

    /**
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

}