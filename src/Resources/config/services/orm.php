<?php

use Symfony\Component\DependencyInjection\Definition;
use Zaeder\MultiDbBundle\ORM\QuoteStrategy;

if ($container instanceof Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationContainerBuilder) {
    $quoteStrategyDefinition = new Definition(QuoteStrategy::class);
    $quoteStrategyDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
    ;
    $container->setDefinition(QuoteStrategy::class, $quoteStrategyDefinition);
}