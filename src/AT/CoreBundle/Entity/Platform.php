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
 * AT\CoreBundle\Entity\Platform
 *
 * @author Andrey Tkachenko <andrey.tkachenko@opensoftdev.ru>
 * @ORM\Entity(repositoryClass="AT\CoreBundle\Entity\Repository\PlatformRepository")
 *
 * @ORM\Table(name="platforms")
 */
class Platform
{
    const PHP  = 'php';
    const JAVASCRIPT = 'ecma262';
    const CSS2 = 'css2';
    const HTML = 'html';
    const JSON = 'json';
    const XML  = 'xml';
    const YAML = 'yaml';

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