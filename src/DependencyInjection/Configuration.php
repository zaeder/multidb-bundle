<?php

namespace Zaeder\MultiDbBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Zaeder\MultiDbBundle\Entity\DistUserInterface;
use Zaeder\MultiDbBundle\Entity\ServerInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('multi_db');

        if (method_exists($treeBuilder, 'getRootNode')) {
            /** @var ArrayNodeDefinition $rootNode */
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            /** @var ArrayNodeDefinition $rootNode */
            $rootNode = $treeBuilder->root('multi_db');
        }

        $rootNode
            ->children()
                ->append($this->getLocalConfig())
                ->append($this->getDistConfig())
                ->scalarNode('passwordKey')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
                ->arrayNode('loginRedirect')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('role')
                                ->cannotBeEmpty()
                                ->isRequired()
                            ->end()
                            ->scalarNode('route')
                                ->cannotBeEmpty()
                                ->isRequired()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->append($this->getEntitiesEnablePasswordEncode())
                ->booleanNode('loginCheckEncodedPassword')
                    ->defaultValue(false)
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }


    public function getLocalConfig()
    {
        $treeBuilder = new TreeBuilder('local');

        if (method_exists($treeBuilder, 'getRootNode')) {
            /** @var ArrayNodeDefinition $rootNode */
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            /** @var ArrayNodeDefinition $rootNode */
            $rootNode = $treeBuilder->root('local');
        }

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('connection')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
                ->scalarNode('entityManager')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
                ->scalarNode('tablePrefix')
                    ->defaultNull()
                ->end()
                ->scalarNode('serverEntity')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
                ->scalarNode('userEntity')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
            ->end()
        ->end();

        return $rootNode;
    }


    public function getDistConfig()
    {
        $treeBuilder = new TreeBuilder('dist');

        if (method_exists($treeBuilder, 'getRootNode')) {
            /** @var ArrayNodeDefinition $rootNode */
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            /** @var ArrayNodeDefinition $rootNode */
            $rootNode = $treeBuilder->root('dist');
        }

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('connection')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
                ->scalarNode('entityManager')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
                ->scalarNode('tablePrefix')
                    ->defaultNull()
                ->end()
                ->scalarNode('userEntity')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
            ->end()
        ->end();

        return $rootNode;
    }

    public function getEntitiesEnablePasswordEncode()
    {
        $treeBuilder = new TreeBuilder('entities');

        if (method_exists($treeBuilder, 'getRootNode')) {
            /** @var ArrayNodeDefinition $rootNode */
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            /** @var ArrayNodeDefinition $rootNode */
            $rootNode = $treeBuilder->root('entities');
        }

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('enablePasswordEncode')
                    ->prototype('scalar')->end()
                    ->defaultValue([ServerInterface::class, DistUserInterface::class])
                ->end()
            ->end();

        return $rootNode;
    }
}