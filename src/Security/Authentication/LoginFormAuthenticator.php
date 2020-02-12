<?php

namespace Zaeder\MultiDbBundle\Security\Authentication;

use Doctrine\Common\Persistence\ManagerRegistry;
use Zaeder\MultiDbBundle\Entity\ServerInterface;
use Zaeder\MultiDbBundle\Event\DatabaseEvents;
use Zaeder\MultiDbBundle\Event\MultiDbEvent;
use Zaeder\MultiDbBundle\Event\SecurityEvents;
use Zaeder\MultiDbBundle\Security\PasswordEncoder;
use Zaeder\MultiDbBundle\Security\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Class LoginFormAuthenticator
 * @package Zaeder\MultiDb\Security\Authentication
 */
class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    /**
     * @var EntityManagerInterface
     */
    private $localEntityManager;
    /**
     * @var EntityManagerInterface
     */
    private $distEntityManager;
    /**
     * @var string
     */
    private $distUserEntityClass;
    /**
     * @var string
     */
    private $localUserEntityClass;
    /**
     * @var string
     */
    private $serverEntityClass;
    /**
     * @var array
     */
    private $loginRedirect;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;
    /**
     * @var PasswordEncoder
     */
    private $passwordEncoder;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var bool
     */
    private $loginCheckEncodedPassword;

    /**
     * LoginFormAuthenticator constructor.
     * @param ManagerRegistry $registry
     * @param string $localEntityManagerName
     * @param string $distEntityManagerName
     * @param string $distUserEntityClass
     * @param string $serverEntityClass
     * @param string $localUserEntityClass
     * @param UrlGeneratorInterface $urlGenerator
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @param PasswordEncoder $passwordEncoder
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ManagerRegistry $registry,
        string $localEntityManagerName,
        string $distEntityManagerName,
        string $distUserEntityClass,
        string $localUserEntityClass,
        string $serverEntityClass,
        array $loginRedirect,
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        PasswordEncoder $passwordEncoder,
        EventDispatcherInterface $eventDispatcher,
        bool $loginCheckEncodedPassword
    )
    {
        $this->localEntityManager = $registry->getManager($localEntityManagerName);
        $this->distEntityManager = $registry->getManager($distEntityManagerName);
        $this->distUserEntityClass = $distUserEntityClass;
        $this->localUserEntityClass = $localUserEntityClass;
        $this->serverEntityClass = $serverEntityClass;
        $this->loginRedirect = $loginRedirect;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->eventDispatcher = $eventDispatcher;
        $this->loginCheckEncodedPassword = $loginCheckEncodedPassword;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        return 'login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    /**
     * @param Request $request
     * @return array|mixed
     */
    public function getCredentials(Request $request)
    {
        $credentials = [
            'serverkey' => $request->request->get('serverkey'),
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username']
        );
        $request->getSession()->set(
            Security::LAST_SERVERKEY,
            $credentials['serverkey']
        );

        return $credentials;
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User|null|object|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        if (!empty($credentials['serverkey'])) {
            $server = $this->localEntityManager->getRepository($this->serverEntityClass)->findOneBy(['key' => $credentials['serverkey'], 'isActive' => true]);

            if (!$server instanceof ServerInterface) {
                throw new CustomUserMessageAuthenticationException('Server key could not be found.');
            }

            $this->eventDispatcher->dispatch(new MultiDbEvent($server), DatabaseEvents::DIST_EM_CONFIG);

            $distUser = $this->distEntityManager->getRepository($this->distUserEntityClass)->findOneBy(['username' => $credentials['username'], 'isActive' => true]);

            if (!$distUser) {
                // fail authentication with a custom error
                $data = new \stdClass();
                $data->username = $credentials['username'];
                $data->server = $server;
                $this->eventDispatcher->dispatch(new MultiDbEvent($data), SecurityEvents::SECURITY_REMOVE_DIST_USER);
                throw new CustomUserMessageAuthenticationException('Username could not be found.');
            }

            $data = new \stdClass();
            $data->user = $distUser;
            $data->server = $server;
            $this->eventDispatcher->dispatch(new MultiDbEvent($data), SecurityEvents::SECURITY_IMPORT_DIST_USER);
        }

        $user = $this->localEntityManager->getRepository($this->localUserEntityClass)->findOneBy(['username' => $credentials['username']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Username could not be found.');
        }

        return $user;
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if ($this->loginCheckEncodedPassword) {
            $valid = $this->passwordEncoder->isEquals($user->getPassword(), $user->getSalt(), $credentials['password']);
        } else{
            $valid = $user->getPassword() === $credentials['password'];
        }
        return $valid;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return RedirectResponse
     * @throws \Exception
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $userRoles = $token->getUser()->getRoles();
        foreach ($this->loginRedirect as $role => $route) {
            if (in_array($role, $userRoles)) {
                return new RedirectResponse($this->urlGenerator->generate($route));
            }
        }

        throw new \Exception('loginRedirect option is not define for current user role');
    }

    /**
     * @return string
     */
    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('login');
    }
}
