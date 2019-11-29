<?php

namespace Zaeder\MultiDb\Security\Authentication;

use Zaeder\MultiDb\Entity\Dist\DistUser as DistUser;
use Zaeder\MultiDb\Entity\Local\Server;
use Zaeder\MultiDb\Entity\Local\User;
use Zaeder\MultiDb\Event\DatabaseEvents;
use Zaeder\MultiDb\Event\Event;
use Zaeder\MultiDb\Event\SecurityEvents;
use Zaeder\MultiDb\Security\PasswordEncoder;
use Zaeder\MultiDb\Security\Security;
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
     * LoginFormAuthenticator constructor.
     * @param EntityManagerInterface $localEntityManager
     * @param EntityManagerInterface $distEntityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @param PasswordEncoder $passwordEncoder
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EntityManagerInterface $localEntityManager,
        EntityManagerInterface $distEntityManager,
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        PasswordEncoder $passwordEncoder,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->localEntityManager = $localEntityManager;
        $this->distEntityManager = $distEntityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->eventDispatcher = $eventDispatcher;
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
            $server = $this->localEntityManager->getRepository(Server::class)->findOneBy(['key' => $credentials['serverkey'], 'isActive' => true]);

            if (!$server instanceof Server) {
                throw new CustomUserMessageAuthenticationException('Server key could not be found.');
            }

            $this->eventDispatcher->dispatch(new Event($server), DatabaseEvents::DIST_EM_CONFIG);

            $distUser = $this->distEntityManager->getRepository(DistUser::class)->findOneBy(['username' => $credentials['username'], 'isActive' => true]);

            if (!$distUser) {
                // fail authentication with a custom error
                $data = new \stdClass();
                $data->username = $credentials['username'];
                $data->server = $server;
                $this->eventDispatcher->dispatch(new Event($data), SecurityEvents::SECURITY_REMOVE_DIST_USER);
                throw new CustomUserMessageAuthenticationException('Username could not be found.');
            }

            $data = new \stdClass();
            $data->user = $distUser;
            $data->server = $server;
            $this->eventDispatcher->dispatch(new Event($data), SecurityEvents::SECURITY_IMPORT_DIST_USER);
        }

        $user = $this->localEntityManager->getRepository(User::class)->findOneBy(['username' => $credentials['username']]);

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
        return $this->passwordEncoder->isEquals($user->getPassword(), $user->getSalt(), $credentials['password']);
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // Redirect to administration panel for admin user
        if (in_array('ROLE_ADMIN', $token->getUser()->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('home_admin'));
        }

        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    /**
     * @return string
     */
    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('login');
    }
}
