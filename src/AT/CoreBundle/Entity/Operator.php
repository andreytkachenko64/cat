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
 * AT\CoreBundle\Entity\Operator
 *
 * @author Andrey Tkachenko <andrey.tkachenko@opensoftdev.ru>
 * @ORM\Entity(repositoryClass="AT\CoreBundle\Entity\Repository\OperatorRepository")
 *
 * @ORM\Table(name="operators")
 */
class Operator
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AT\CoreBundle\Entity\File", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(name="file_id", referencedColumnName="id", nullable=false)
     * @var File
     */
    protected $file;


    /**
     * @ORM\ManyToOne(targetEntity="AT\CoreBundle\Entity\Operator", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * @var Operator
     */
    protected $parent = null;

    /**
     * @ORM\ManyToOne(targetEntity="AT\CoreBundle\Entity\OperatorName", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(name="name_id", referencedColumnName="id", nullable=false)
     * @var OperatorName
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @var string
     */
    protected $ancestors;


    /**
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    protected $begin;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    protected $end;

    /**
     * @ORM\OneToMany(targetEntity="OperatorParameter", mappedBy="operator", cascade={"persist", "remove", "merge"})
     * @var ArrayCollection|OperatorParameter[]
     */
    public $parameters;

    public function __construct()
    {
        $this->parameters = new ArrayCollection();
    }

    /**
     * @param string $ancestors
     */
    public function setAncestors($ancestors)
    {
        $this->ancestors = $ancestors;
    }

    /**
     * @return string
     */
    public function getAncestors()
    {
        return $this->ancestors;
    }

    /**
     * @param int $begin
     */
    public function setBegin($begin)
    {
        $this->begin = $begin;
    }

    /**
     * @return int
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @param int $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @return int
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param \AT\CoreBundle\Entity\File $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return \AT\CoreBundle\Entity\File
     */
    public function getFile()
    {
        return $this->file;
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
     * @param \AT\CoreBundle\Entity\OperatorName $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return \AT\CoreBundle\Entity\OperatorName
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param \AT\CoreBundle\Entity\Operator $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return \AT\CoreBundle\Entity\Operator
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function addParameter(OperatorParameter $parameter)
    {
        $this->parameters->add($parameter);
    }
}