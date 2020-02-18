<?php

namespace Zaeder\MultiDbBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository as DoctrineServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadata;

class ServiceEntityRepository extends DoctrineServiceEntityRepository
{
    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry, ClassMetadata $class)
    {
        parent::__construct($managerRegistry, $class);
    }

    public function getNewEntity()
    {
        return new $this->_entityName();
    }
}