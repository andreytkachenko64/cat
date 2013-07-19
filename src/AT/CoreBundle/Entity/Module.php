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
 * AT\CoreBundle\Entity\Module
 *
 * @author Andrey Tkachenko <andrey.tkachenko@opensoftdev.ru>
 * @ORM\Entity(repositoryClass="AT\CoreBundle\Entity\Repository\ModuleRepository")
 *
 * @ORM\Table(name="modules")
 */
class Module
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
    protected $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    protected $version;

    /**
     * @ORM\ManyToOne(targetEntity="AT\CoreBundle\Entity\Project", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * @var Project
     */
    protected $project;

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
     * @param \AT\CoreBundle\Entity\Project $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * @return \AT\CoreBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
}