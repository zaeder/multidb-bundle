<?php

use Symfony\Component\DependencyInjection\Definition;
use Zaeder\MultiDbBundle\ORM\QuoteStrategy;

if ($container instanceof Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationContainerBuilder) {
    $securtiryDefinition = new Definition(QuoteStrategy::class);
    $securtiryDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
    ;
    $container->setDefinition(QuoteStrategy::class, $securtiryDefinition);
}