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
 * AT\CoreBundle\Entity\String
 *
 * @author Andrey Tkachenko <andrey.tkachenko@opensoftdev.ru>
 * @ORM\Entity(repositoryClass="AT\CoreBundle\Entity\Repository\ScalarRepository")
 *
 * @ORM\Table(name="scalars")
 */
class Scalar
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    protected $value;

    /**
     * @ORM\ManyToOne(targetEntity="AT\CoreBundle\Entity\ScalarType", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=true)
     * @var ScalarType
     */
    protected $type;

    public function __construct($value)
    {
        $this->value = $value;
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
     * @param \AT\CoreBundle\Entity\ScalarType $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return \AT\CoreBundle\Entity\ScalarType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}