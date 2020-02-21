<?php

namespace Zaeder\MultiDbBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository as DoctrineServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Zaeder\MultiDbBundle\Exception\EntityIdentifierException;
use Zaeder\MultiDbBundle\Exception\EntityViolationException;

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

    public function save($entity)
    {
        $this->checkValidEntity($entity);
        if ($this->getEntityId($entity) === null) {
            $this->_em->persist($entity);
        }
        $this->_em->flush();
    }

    public function remove($entity)
    {
        $this->checkValidEntity($entity);
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    protected function checkValidEntity($entity)
    {
        if (!$entity instanceof $this->_entityName) {
            throw new EntityViolationException('Invalid entity "'.get_class($entity).'", expected "'.$this->_entityName.'"');
        }
    }

    protected function getEntityId($entity)
    {
        $meta = $this->_em->getClassMetadata(get_class($entity));
        $identifier = $meta->getSingleIdentifierFieldName();
        $getter = 'get'.ucfirst($identifier);
        if (method_exists($entity, $getter)) {
            return $entity->$getter();
        }
        throw new EntityIdentifierException('Can\'t find identifier for entity "'.$this->_entityName.'"');
    }
}