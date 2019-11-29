<?php

namespace Zaeder\MultiDb\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use \Doctrine\ORM\Event\LoadClassMetadataEventArgs;

/**
 * Class TablePrefixEventSubscriber
 * @package Zaeder\MultiDb\EventSubscriber
 */
class TablePrefixEventSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * TablePrefixEventSubscriber constructor.
     * @param string $prefix
     */
    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return array|string[]
     */
    public function getSubscribedEvents()
    {
        return ['loadClassMetadata'];
    }

    /**
     * Add prefix if exists to dist tables
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (!$classMetadata->isInheritanceTypeSingleTable() || $classMetadata->getName() === $classMetadata->rootEntityName) {
            $classMetadata->setPrimaryTable([
                'name' => $this->prefix . $classMetadata->getTableName()
            ]);
        }

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if ($mapping['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY && $mapping['isOwningSide']) {
                $mappedTableName = $mapping['joinTable']['name'];
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix . $mappedTableName;
            }
        }
    }
}