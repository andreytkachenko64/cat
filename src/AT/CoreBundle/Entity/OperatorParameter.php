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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * AT\CoreBundle\Entity\OperatorParameter
 *
 * @author Andrey Tkachenko <andrey.tkachenko@opensoftdev.ru>
 * @ORM\Entity(repositoryClass="AT\CoreBundle\Entity\Repository\OperatorParameterRepository")
 *
 * @ORM\Table(name="operator_params")
 */
class OperatorParameter
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AT\CoreBundle\Entity\Operator", fetch="EAGER")
     * @ORM\JoinColumn(name="operator_id", referencedColumnName="id")
     * @var Operator
     */
    protected $operator;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="AT\CoreBundle\Entity\OperatorParameterItem", cascade={"persist", "remove"}, mappedBy="operatorParameter")
     * @var OperatorParameterItem[]
     */
    protected $items;

    /**
     * @ORM\ManyToOne(targetEntity="AT\CoreBundle\Entity\Scalar", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinColumn(name="scalar_id", referencedColumnName="id", nullable=true)
     * @var Scalar
     */
    protected $scalar;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

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
     * @param mixed $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param \AT\CoreBundle\Entity\Scalar $scalar
     */
    public function setScalar(Scalar $scalar = null)
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

}