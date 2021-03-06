<?php

namespace Zaeder\MultiDbBundle\Security\Authentication;

use Doctrine\Common\Persistence\ManagerRegistry;
use Zaeder\MultiDbBundle\Entity\ServerInterface;
use Zaeder\MultiDbBundle\Event\DatabaseEvents;
use Zaeder\MultiDbBundle\Event\MultiDbEvent;
use Zaeder\MultiDbBundle\Event\SecurityEvents;
use Zaeder\MultiDbBundle\Repository\AbstractDistUserRepository;
use Zaeder\MultiDbBundle\Repository\AbstractLocalUserRepository;
use Zaeder\MultiDbBundle\Repository\AbstractServerRepository;
use Zaeder\MultiDbBundle\Security\PasswordEncoder;
use Zaeder\MultiDbBundle\Security\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
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
     * @var AbstractDistUserRepository
     */
    protected $distUserRepository;
    /**
     * @var AbstractLocalUserRepository
     */
    protected $localUserRepository;
    /**
     * @var AbstractServerRepository
     */
    protected $serverRepository;
    /**
     * @var array
     */
    protected $loginRedirect;
    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;
    /**
     * @var CsrfTokenManagerInterface
     */
    protected $csrfTokenManager;
    /**
     * @var PasswordEncoder
     */
    protected $passwordEncoder;
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var bool
     */
    protected $loginCheckEncodedPassword;
    /**
     * @var string
     */
    protected $loginRoute;
    /**
     * @var string
     */
    protected $serverKeyField;
    /**
     * @var string
     */
    protected $usernameField;
    /**
     * @var string
     */
    protected $passwordField;
    /**
     * @var string
     */
    protected $csrfTokenField;
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * LoginFormAuthenticator constructor.
     * @param AbstractDistUserRepository $distUserRepository
     * @param AbstractLocalUserRepository $localUserRepository
     * @param AbstractServerRepository $serverRepository
     * @param UrlGeneratorInterface $urlGenerator
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @param PasswordEncoder $passwordEncoder
     * @param EventDispatcherInterface $eventDispatcher
     * @param string $loginRoute
     * @param string $serverKeyField
     * @param string $usernameField
     * @param string $passwordField
     * @param string $csrfTokenField
     */
    public function __construct(
        AbstractDistUserRepository $distUserRepository,
        AbstractLocalUserRepository $localUserRepository,
        AbstractServerRepository $serverRepository,
        array $loginRedirect,
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        PasswordEncoder $passwordEncoder,
        EventDispatcherInterface $eventDispatcher,
        bool $loginCheckEncodedPassword,
        string $loginRoute,
        string $serverKeyField,
        string $usernameField,
        string $passwordField,
        string $csrfTokenField,
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        $this->distUserRepository = $distUserRepository;
        $this->localUserRepository = $localUserRepository;
        $this->serverRepository = $serverRepository;
        $this->loginRedirect = $loginRedirect;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->eventDispatcher = $eventDispatcher;
        $this->loginCheckEncodedPassword = $loginCheckEncodedPassword;
        $this->loginRoute = $loginRoute;
        $this->serverKeyField = $serverKeyField;
        $this->usernameField = $usernameField;
        $this->passwordField = $passwordField;
        $this->csrfTokenField = $csrfTokenField;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        return $this->loginRoute === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    /**
     * @param Request $request
     * @return array|mixed
     */
    public function getCredentials(Request $request)
    {
        $credentials = [
            'serverkey' => $request->request->get($this->serverKeyField),
            'username' => $request->request->get($this->usernameField),
            'password' => $request->request->get($this->passwordField),
            'csrf_token' => $request->request->get($this->csrfTokenField),
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

        $server = null;
        if (!empty($credentials['serverkey'])) {
            $server = $this->serverRepository->findByServerKey($credentials['serverkey']);

            if (!$server) {
                throw new CustomUserMessageAuthenticationException('Server key could not be found.');
            }

            $this->eventDispatcher->dispatch(new MultiDbEvent($server), DatabaseEvents::DIST_EM_CONFIG);

            $distUser = $this->distUserRepository->findByUsername($credentials['username']);

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

        $user = $this->localUserRepository->findByUsernameAndServer($credentials['username'], $server);

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
        foreach ($this->loginRedirect as $role => $route) {
            if ($this->authorizationChecker->isGranted($role)) {
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
        return $this->urlGenerator->generate($this->loginRoute);
    }
}
