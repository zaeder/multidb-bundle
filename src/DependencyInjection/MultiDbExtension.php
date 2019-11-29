<?php

namespace Zaeder\MultiDbBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class MultiDbExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('zaeder.multidb.local.connection.name', $config['local']['connection']);
        $container->setParameter('zaeder.multidb.local.entity_manager.name', $config['local']['entityManager']);
        $container->setParameter('zaeder.multidb.local.table_prefix', $config['local']['tablePrefix']);
        $container->setParameter('zaeder.multidb.local.server_entity.class', $config['local']['serverEntity']);
        $container->setParameter('zaeder.multidb.local.user_entity.class', $config['local']['userEntity']);
        $container->setParameter('zaeder.multidb.dist.connection.name', $config['dist']['connection']);
        $container->setParameter('zaeder.multidb.dist.entity_manager.name', $config['dist']['entityManager']);
        $container->setParameter('zaeder.multidb.dist.table_prefix', $config['dist']['tablePrefix']);
        $container->setParameter('zaeder.multidb.dist.user_entity.class', $config['dist']['userEntity']);
        $container->setParameter('zaeder.multidb.password_key', $config['passwordKey']);
        $container->setParameter('zaeder.multidb.login_redirect', $this->formatLoginRedirect($config['loginRedirect']));

        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config/services');
        $yamlLoader = new YamlFileLoader(
            $container,
            $fileLocator
        );
        $yamlLoader->import('*.yaml');
        $phpLoader = new PhpFileLoader(
            $container,
            $fileLocator
        );
        $phpLoader->import('*.php');
    }

    private function formatLoginRedirect(array $loginRedirectParams) : array
    {
        $loginRedirect = [];
        foreach ($loginRedirectParams as $loginRedirectParam) {
            if (array_key_exists($loginRedirectParam['role'], $loginRedirect)) {
                throw new \Exception('loginRedirect option is already defined for '.$loginRedirectParam['role']);
            }
            $loginRedirect[$loginRedirectParam['role']] = $loginRedirectParam['route'];
        }
        return $loginRedirect;
    }
}