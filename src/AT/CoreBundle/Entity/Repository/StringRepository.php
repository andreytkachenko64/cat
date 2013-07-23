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

namespace AT\CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * AT\CoreBundle\Entity\Repository\StringRepository
 *
 * @author Andrey Tkachenko <andrey.tkachenko@opensoftdev.ru>
 */
class StringRepository extends EntityRepository
{
    public function store($nameEntity, $flush)
    {
        $this->getEntityManager()->persist($nameEntity);
        if($flush){
            $this->getEntityManager()->flush($nameEntity);
        }
    }
}