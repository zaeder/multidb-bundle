<?php

namespace Zaeder\MultiDbBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository as DoctrineServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ServiceEntityRepository extends DoctrineServiceEntityRepository
{
    /**
     * @param ManagerRegistry $managerRegistry
     * @param string $className
     */
    public function __construct(ManagerRegistry $managerRegistry, string $className)
    {
        parent::__construct($managerRegistry, $className);
    }

    public function getNewEntity()
    {
        return new $this->_entityName();
    }
}