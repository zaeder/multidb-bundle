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
        $container->setParameter('zaeder.multidb.local.server_repository.class', $config['local']['serverRepository']);
        $container->setParameter('zaeder.multidb.local.user_repository.class', $config['local']['userRepository']);
        $container->setParameter('zaeder.multidb.dist.connection.name', $config['dist']['connection']);
        $container->setParameter('zaeder.multidb.dist.entity_manager.name', $config['dist']['entityManager']);
        $container->setParameter('zaeder.multidb.dist.table_prefix', $config['dist']['tablePrefix']);
        $container->setParameter('zaeder.multidb.dist.user_repository.class', $config['dist']['userRepository']);
        $container->setParameter('zaeder.multidb.password_key', $config['passwordKey']);
        $container->setParameter('zaeder.multidb.login_redirect', $this->formatLoginRedirect($config['loginRedirect']));
        $container->setParameter('zaeder.multidb.entities.enable.password_encode', $config['entities']['enablePasswordEncode']);
        $container->setParameter('zaeder.multidb.login_check_encoded_password', $config['loginCheckEncodedPassword']);
        $container->setParameter('zaeder.multidb.login_route', $config['loginRoute']);
        $container->setParameter('zaeder.multidb.login_fields.serverkey', $config['loginFields']['serverKey']);
        $container->setParameter('zaeder.multidb.login_fields.username', $config['loginFields']['username']);
        $container->setParameter('zaeder.multidb.login_fields.password', $config['loginFields']['password']);
        $container->setParameter('zaeder.multidb.login_fields.csrf_token', $config['loginFields']['csrfToken']);

        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config/services');
        $phpLoader = new PhpFileLoader(
            $container,
            $fileLocator
        );
        $phpLoader->import('*.php');
    }

    protected function formatLoginRedirect(array $loginRedirectParams) : array
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