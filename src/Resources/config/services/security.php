<?php

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Zaeder\MultiDbBundle\Security\Authentication\AuthenticationUtils;
use Zaeder\MultiDbBundle\Security\Authentication\LoginFormAuthenticator;
use Zaeder\MultiDbBundle\Security\PasswordEncoder;
use Zaeder\MultiDbBundle\Security\Security;

if ($container instanceof Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationContainerBuilder) {
    $authenticationUtilsDefinition = new Definition(AuthenticationUtils::class);
    $authenticationUtilsDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
    ;
    $container->setDefinition(AuthenticationUtils::class, $authenticationUtilsDefinition);

    $loginFormAuthenticatorDefinition = new Definition(LoginFormAuthenticator::class);
    $loginFormAuthenticatorDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
        ->setArgument('$distUserRepository', new Reference($container->getParameter('zaeder.multidb.dist.user_repository.class')))
        ->setArgument('$localUserRepository', new Reference($container->getParameter('zaeder.multidb.local.user_repository.class')))
        ->setArgument('$serverRepository',new Reference($container->getParameter('zaeder.multidb.local.server_repository.class')))
        ->setArgument('$loginRedirect', $container->getParameter('zaeder.multidb.login_redirect'))
        ->setArgument('$loginCheckEncodedPassword', $container->getParameter('zaeder.multidb.login_check_encoded_password'))
        ->setArgument('$loginRoute', $container->getParameter('zaeder.multidb.login_route'))
        ->setArgument('$serverKeyField', $container->getParameter('zaeder.multidb.login_fields.serverkey'))
        ->setArgument('$usernameField', $container->getParameter('zaeder.multidb.login_fields.username'))
        ->setArgument('$passwordField', $container->getParameter('zaeder.multidb.login_fields.password'))
        ->setArgument('$csrfTokenField', $container->getParameter('zaeder.multidb.login_fields.csrf_token'))
    ;
    $container->setDefinition(LoginFormAuthenticator::class, $loginFormAuthenticatorDefinition);
    
    $passwordEncoderDefinition = new Definition(PasswordEncoder::class);
    $passwordEncoderDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
        ->setArgument('$passwordKey', $container->getParameter('zaeder.multidb.password_key'))
    ;
    $container->setDefinition(PasswordEncoder::class, $passwordEncoderDefinition);

    $securityDefinition = new Definition(Security::class);
    $securityDefinition
        ->setAutowired(true)
        ->setAutoconfigured(true)
    ;
    $container->setDefinition(Security::class, $securityDefinition);
}