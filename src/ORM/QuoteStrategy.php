<?php

namespace Zaeder\MultiDbBundle\ORM;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\QuoteStrategy as QuoteStrategyInterface;

/**
 * Class QuoteStrategy
 * @package Zaeder\MultiDb\ORM
 */
class QuoteStrategy implements QuoteStrategyInterface
{
    /**
     * Add back-tick to field names
     * @param $token
     * @param AbstractPlatform $platform
     * @return string
     */
    protected function quote($token, AbstractPlatform $platform)
    {
        switch ($platform->getName()) {
            case 'mysql':
            default:
                return '`' . $token . '`';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnName($fieldName, ClassMetadata $class, AbstractPlatform $platform)
    {
        return $this->quote($class->fieldMappings[$fieldName]['columnName'], $platform);
    }

    /**
     * {@inheritdoc}
     *
     * @todo Table names should be computed in DBAL depending on the platform
     */
    public function getTableName(ClassMetadata $class, AbstractPlatform $platform)
    {
        $tableName = $class->table['name'];

        if ( ! empty($class->table['schema'])) {
            $tableName = $class->table['schema'] . '.' . $class->table['name'];

            if ( ! $platform->supportsSchemas() && $platform->canEmulateSchemas()) {
                $tableName = $class->table['schema'] . '__' . $class->table['name'];
            }
        }

        return isset($class->table['quoted'])
            ? $platform->quoteIdentifier($tableName)
            : $tableName;
    }

    /**
     * {@inheritdoc}
     */
    public function getSequenceName(array $definition, ClassMetadata $class, AbstractPlatform $platform)
    {
        return isset($definition['quoted'])
            ? $platform->quoteIdentifier($definition['sequenceName'])
            : $definition['sequenceName'];
    }

    /**
     * {@inheritdoc}
     */
    public function getJoinColumnName(array $joinColumn, ClassMetadata $class, AbstractPlatform $platform)
    {
        return isset($joinColumn['quoted'])
            ? $platform->quoteIdentifier($joinColumn['name'])
            : $joinColumn['name'];
    }

    /**
     * {@inheritdoc}
     */
    public function getReferencedJoinColumnName(array $joinColumn, ClassMetadata $class, AbstractPlatform $platform)
    {
        return isset($joinColumn['quoted'])
            ? $platform->quoteIdentifier($joinColumn['referencedColumnName'])
            : $joinColumn['referencedColumnName'];
    }

    /**
     * {@inheritdoc}
     */
    public function getJoinTableName(array $association, ClassMetadata $class, AbstractPlatform $platform)
    {
        $schema = '';

        if (isset($association['joinTable']['schema'])) {
            $schema = $association['joinTable']['schema'] . '.';
        }

        $tableName = $association['joinTable']['name'];

        if (isset($association['joinTable']['quoted'])) {
            $tableName = $platform->quoteIdentifier($tableName);
        }

        return $schema . $tableName;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierColumnNames(ClassMetadata $class, AbstractPlatform $platform)
    {
        $quotedColumnNames = [];

        foreach ($class->identifier as $fieldName) {
            if (isset($class->fieldMappings[$fieldName])) {
                $quotedColumnNames[] = $this->getColumnName($fieldName, $class, $platform);

                continue;
            }

            // Association defined as Id field
            $joinColumns            = $class->associationMappings[$fieldName]['joinColumns'];
            $assocQuotedColumnNames = array_map(
                function ($joinColumn) use ($platform)
                {
                    return isset($joinColumn['quoted'])
                        ? $platform->quoteIdentifier($joinColumn['name'])
                        : $joinColumn['name'];
                },
                $joinColumns
            );

            $quotedColumnNames = array_merge($quotedColumnNames, $assocQuotedColumnNames);
        }

        return $quotedColumnNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnAlias($columnName, $counter, AbstractPlatform $platform, ClassMetadata $class = null)
    {
        // 1 ) Concatenate column name and counter
        // 2 ) Trim the column alias to the maximum identifier length of the platform.
        //     If the alias is to long, characters are cut off from the beginning.
        // 3 ) Strip non alphanumeric characters
        // 4 ) Prefix with "_" if the result its numeric
        $columnName = $columnName . '_' . $counter;
        $columnName = substr($columnName, -$platform->getMaxIdentifierLength());
        $columnName = preg_replace('/[^A-Za-z0-9_]/', '', $columnName);
        $columnName = is_numeric($columnName) ? '_' . $columnName : $columnName;

        return $platform->getSQLResultCasing($columnName);
    }
}