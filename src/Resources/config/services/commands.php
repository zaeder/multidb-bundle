<?php

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Zaeder\MultiDbBundle\Command\DatabaseCreateCommand;
use Zaeder\MultiDbBundle\Command\DatabaseDropCommand;
use Zaeder\MultiDbBundle\Command\DatabaseUpdateCommand;

if ($container instanceof Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationContainerBuilder) {
    $localEntityManagerName = $container->getParameter('zaeder.multidb.local.entity_manager.name');
    $localConnectionName = $container->getParameter('zaeder.multidb.local.connection.name');
    $distEntityManagerName = $container->getParameter('zaeder.multidb.dist.entity_manager.name');
    $serverRepository = new Reference($container->getParameter('zaeder.multidb.local.server_repository.class'));

    $databaseCreateCommandDefinition = new Definition(DatabaseCreateCommand::class);
    $databaseCreateCommandDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
        ->setArgument('$localEntityManagerName', $localEntityManagerName)
        ->setArgument('$localConnectionName', $localConnectionName)
        ->setArgument('$distEntityManagerName', $distEntityManagerName)
        ->setArgument('$serverRepository', $serverRepository)
        ->addTag('console.command')
    ;
    $container->setDefinition(DatabaseCreateCommand::class, $databaseCreateCommandDefinition);

    $databaseDropCommandDefinition = new Definition(DatabaseDropCommand::class);
    $databaseDropCommandDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
        ->setArgument('$localEntityManagerName', $localEntityManagerName)
        ->setArgument('$localConnectionName', $localConnectionName)
        ->setArgument('$distEntityManagerName', $distEntityManagerName)
        ->setArgument('$serverRepository', $serverRepository)
        ->addTag('console.command')
    ;
    $container->setDefinition(DatabaseDropCommand::class, $databaseDropCommandDefinition);

    $databaseUpdateCommandDefinition = new Definition(DatabaseUpdateCommand::class);
    $databaseUpdateCommandDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
        ->setArgument('$localEntityManagerName', $localEntityManagerName)
        ->setArgument('$localConnectionName', $localConnectionName)
        ->setArgument('$distEntityManagerName', $distEntityManagerName)
        ->setArgument('$serverRepository', $serverRepository)
        ->addTag('console.command')
    ;
    $container->setDefinition(DatabaseUpdateCommand::class, $databaseUpdateCommandDefinition);
}