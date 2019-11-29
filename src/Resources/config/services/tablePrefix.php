<?php

use Symfony\Component\DependencyInjection\Definition;
use Zaeder\MultiDbBundle\EventSubscriber\TablePrefixEventSubscriber;

if ($container instanceof Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationContainerBuilder) {
    // Add table prefix event subscriber for local connection
    $localTablePrefix = $container->getParameter('zaeder.multidb.local.table_prefix');
    $localConnectionName = $container->getParameter('zaeder.multidb.local.connection.name');
    $localTablePrefixDefinition = new Definition(TablePrefixEventSubscriber::class);
    $localTablePrefixDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
        ->setArgument('$prefix', $localTablePrefix)
        ->addTag('doctrine.event_subscriber', ['connection' => $localConnectionName]);
    $container->setDefinition('zaeder.multidb.local.table_prefix', $localTablePrefixDefinition);


    // Add table prefix event subscriber for dist connection
    $distTablePrefix = $container->getParameter('zaeder.multidb.dist.table_prefix');
    $distConnectionName = $container->getParameter('zaeder.multidb.dist.connection.name');
    $distTablePrefixDefinition = new Definition(TablePrefixEventSubscriber::class);
    $distTablePrefixDefinition
        ->setArgument('$prefix', $distTablePrefix)
        ->addTag('doctrine.event_subscriber', ['connection' => $distConnectionName]);
    $container->setDefinition('zaeder.multidb.dist.table_prefix', $distTablePrefixDefinition);

    //$test = $container->getDefinition('zaeder.multidb.dist.table_prefix2');

    //echo('<table><tr><td valign="top">');var_dump($test);echo('</td><td valign="top">');var_dump($distTablePrefixDefinition);echo('</td></tr></table>');die;
}