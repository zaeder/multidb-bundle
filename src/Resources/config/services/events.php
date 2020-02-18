<?php

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Zaeder\MultiDbBundle\EventListener\PasswordEventListener;
use Zaeder\MultiDbBundle\EventSubscriber\DistDatabaseEventSubscriber;
use Zaeder\MultiDbBundle\EventSubscriber\LocalUserEventSubcriber;
use Zaeder\MultiDbBundle\EventSubscriber\TablePrefixEventSubscriber;

if ($container instanceof Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationContainerBuilder) {
    $localTablePrefix = $container->getParameter('zaeder.multidb.local.table_prefix');
    $localConnectionName = $container->getParameter('zaeder.multidb.local.connection.name');
    $localEntityManagerName = $container->getParameter('zaeder.multidb.local.entity_manager.name');
    $localUserRepository = new Reference($container->getParameter('zaeder.multidb.local.user_repository.class'));
    $distTablePrefix = $container->getParameter('zaeder.multidb.dist.table_prefix');
    $distConnectionName = $container->getParameter('zaeder.multidb.dist.connection.name');
    $distEntityManagerName = $container->getParameter('zaeder.multidb.dist.entity_manager.name');
    $distUserRepository = new Reference($container->getParameter('zaeder.multidb.dist.user_repository.class'));
    $entitiesEnabled = $container->getParameter('zaeder.multidb.entities.enable.password_encode');
    $loginRoute = $container->getParameter('zaeder.multidb.login_route');

    $passwordEventListenerDefinition = new Definition(PasswordEventListener::class);
    $passwordEventListenerDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
        ->setArgument('$entitiesEnabled', $entitiesEnabled)
        ->addTag('doctrine.event_listener', ['event' => 'prePersist'])
        ->addTag('doctrine.event_listener', ['event' => 'preUpdate'])
    ;
    $container->setDefinition(PasswordEventListener::class, $passwordEventListenerDefinition);

    $distDatabaseEventSubscriberDefinition = new Definition(DistDatabaseEventSubscriber::class);
    $distDatabaseEventSubscriberDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
        ->setArgument('$distConnectionName', $distConnectionName)
        ->setArgument('$distEntityManagerName', $distEntityManagerName)
        ->setArgument('$localUserRepository', $localUserRepository)
        ->setArgument('$loginRoute', $loginRoute)
    ;
    $container->setDefinition(DistDatabaseEventSubscriber::class, $distDatabaseEventSubscriberDefinition);

    $localUserEventSubcriberDefinition = new Definition(LocalUserEventSubcriber::class);
    $localUserEventSubcriberDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
        ->setArgument('$localUserRepository', $localUserRepository)
        ->setArgument('$distUserRepository', $distUserRepository)
    ;
    $container->setDefinition(LocalUserEventSubcriber::class, $localUserEventSubcriberDefinition);

    // Add table prefix event subscriber for local connection
    $localTablePrefixDefinition = new Definition(TablePrefixEventSubscriber::class);
    $localTablePrefixDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
        ->setArgument('$prefix', $localTablePrefix)
        ->addTag('doctrine.event_subscriber', ['connection' => $localConnectionName])
    ;
    $container->setDefinition('zaeder.multidb.local.table_prefix', $localTablePrefixDefinition);


    // Add table prefix event subscriber for dist connection
    $distTablePrefixDefinition = new Definition(TablePrefixEventSubscriber::class);
    $distTablePrefixDefinition
        ->setArgument('$prefix', $distTablePrefix)
        ->addTag('doctrine.event_subscriber', ['connection' => $distConnectionName])
    ;
    $container->setDefinition('zaeder.multidb.dist.table_prefix', $distTablePrefixDefinition);
}