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
                ->scalarNode('loginRoute')
                    ->defaultValue('login')
                ->end()
                ->append($this->getEntitiesEnablePasswordEncode())
                ->booleanNode('loginCheckEncodedPassword')
                    ->defaultValue(true)
                ->end()
            ->append($this->getloginFields())
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
                ->scalarNode('serverRepository')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
                ->scalarNode('userRepository')
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
                ->scalarNode('userRepository')
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
                    ->defaultValue([])
                ->end()
            ->end();

        return $rootNode;
    }

    public function getloginFields()
    {
        $treeBuilder = new TreeBuilder('loginFields');

        if (method_exists($treeBuilder, 'getRootNode')) {
            /** @var ArrayNodeDefinition $rootNode */
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            /** @var ArrayNodeDefinition $rootNode */
            $rootNode = $treeBuilder->root('loginFields');
        }

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('serverKey')
                    ->defaultValue('serverkey')
                ->end()
                ->scalarNode('username')
                    ->defaultValue('username')
                ->end()
                ->scalarNode('password')
                    ->defaultValue('password')
                ->end()
                ->scalarNode('csrfToken')
                    ->defaultValue('_csrf_token')
                ->end()
            ->end();

        return $rootNode;
    }
}