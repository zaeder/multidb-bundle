<?php

use Symfony\Component\DependencyInjection\Definition;
use Zaeder\MultiDbBundle\Command\DatabaseCreateCommand;
use Zaeder\MultiDbBundle\Command\DatabaseDropCommand;
use Zaeder\MultiDbBundle\Command\DatabaseUpdateCommand;

if ($container instanceof Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationContainerBuilder) {
    $localEntityManagerName = $container->getParameter('zaeder.multidb.local.entity_manager.name');
    $localConnectionName = $container->getParameter('zaeder.multidb.local.connection.name');
    $distEntityManagerName = $container->getParameter('zaeder.multidb.dist.entity_manager.name');
    $serverRepositoryClass = $container->getParameter('zaeder.multidb.local.server_repository.class');

    $loginFormAuthenticatorDefinition = new Definition(DatabaseCreateCommand::class);
    $loginFormAuthenticatorDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
        ->setArgument('$localEntityManagerName', $localEntityManagerName)
        ->setArgument('$localConnectionName', $localConnectionName)
        ->setArgument('$distEntityManagerName', $distEntityManagerName)
        ->setArgument('$serverRepository', $serverRepositoryClass)
        ->addTag('console.command')
    ;
    $container->setDefinition(DatabaseCreateCommand::class, $loginFormAuthenticatorDefinition);

    $loginFormAuthenticatorDefinition = new Definition(DatabaseDropCommand::class);
    $loginFormAuthenticatorDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
        ->setArgument('$localEntityManagerName', $localEntityManagerName)
        ->setArgument('$localConnectionName', $localConnectionName)
        ->setArgument('$distEntityManagerName', $distEntityManagerName)
        ->setArgument('$serverRepository', $serverRepositoryClass)
        ->addTag('console.command')
    ;
    $container->setDefinition(DatabaseDropCommand::class, $loginFormAuthenticatorDefinition);

    $loginFormAuthenticatorDefinition = new Definition(DatabaseUpdateCommand::class);
    $loginFormAuthenticatorDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
        ->setArgument('$localEntityManagerName', $localEntityManagerName)
        ->setArgument('$localConnectionName', $localConnectionName)
        ->setArgument('$distEntityManagerName', $distEntityManagerName)
        ->setArgument('$serverRepository', $serverRepositoryClass)
        ->addTag('console.command')
    ;
    $container->setDefinition(DatabaseUpdateCommand::class, $loginFormAuthenticatorDefinition);
}