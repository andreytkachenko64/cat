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
 * AT\CoreBundle\Entity\Repository\OperatorRepository
 *
 * @author Andrey Tkachenko <andrey.tkachenko@opensoftdev.ru>
 */
class OperatorRepository extends EntityRepository
{
    public function store($nameEntity, $flush)
    {
        $this->getEntityManager()->persist($nameEntity);
        if($flush){
            $this->getEntityManager()->flush($nameEntity);
        }
    }
}