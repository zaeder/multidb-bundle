<?php

namespace Zaeder\MultiDb\EventSubscriber;


use Zaeder\MultiDb\Entity\Local\Server;
use Zaeder\MultiDb\Entity\Local\User;
use Zaeder\MultiDb\Event\DatabaseEvents;
use Zaeder\MultiDb\Event\Event;
use Zaeder\MultiDb\Event\SecurityEvents;
use Zaeder\MultiDb\Security\PasswordEncoder;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class DistDatabaseEventSubscriber
 * @package Zaeder\MultiDb\EventSubscriber
 */
class DistDatabaseEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;
    /**
     * @var PasswordEncoder
     */
    protected $encoder;
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;
    /**
     * @var SessionInterface
     */
    protected $session;
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * DistDatabaseEventSubscriber constructor.
     * @param ManagerRegistry $managerRegistry
     * @param PasswordEncoder $encoder
     * @param TokenStorageInterface $tokenStorage
     * @param EventDispatcherInterface $eventDispatcher
     * @param SessionInterface $session
     * @param RouterInterface $router
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        PasswordEncoder $encoder,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        SessionInterface $session,
        RouterInterface $router
    )
    {
        $this->managerRegistry = $managerRegistry;
        $this->encoder = $encoder;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->session = $session;
        $this->router = $router;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['reconfigure'],
            DatabaseEvents::DIST_EM_CONFIG => ['configure'],
        ];
    }

    /**
     * Configure dist connection on authentication
     * @param Event $event
     */
    public function configure(Event $event)
    {
        $server = $event->getData();
        if (!$server instanceof Server) {
            return;
        }

        $this->doConfigure($server);
    }

    /**
     * Reconfigure dist connection if user is already authenticate
     * @param ControllerEvent $event
     */
    public function reconfigure(ControllerEvent $event)
    {
        $token = $this->tokenStorage->getToken();
        // If there is a token
        if ($token instanceof TokenInterface) {
            $user = $token->getUser();
            // If user exists
            if ($user instanceof User) {
                $username = $user->getUsername();
                $server = $user->getServer();
                // If user is attached to a server
                if ($server instanceof Server) {
                    // Configure dist connection
                    $this->doConfigure($server);
                    // Check user validity
                    $this->eventDispatcher->dispatch(new Event($user), SecurityEvents::SECURITY_VALIDATE_DIST_USER);
                    // Check if current user exists after check up; if no kill session and redirect to login page
                    $user = $this->managerRegistry->getManager('local')->getRepository(User::class)->findOneBy(['username' => $username, 'server' => $server]);
                    if (!$user instanceof User) {
                        $this->tokenStorage->setToken(null);
                        $this->session->invalidate();
                        header('location:'.$this->router->generate('login'));
                        die;
                    }
                }
            }
        }
    }

    /**
     * Configure dist connection using server info
     * @param Server $server
     */
    private function doConfigure(Server $server)
    {
        //establish the connection
        $connection = $this->managerRegistry->getConnection('dist');

        $this->managerRegistry->getManager('dist')->flush();

        if ($connection->isConnected()) {
            $connection->close();
        }

        $refConn = new \ReflectionObject($connection);
        $refParams = $refConn->getProperty('params');
        $refParams->setAccessible('public'); //we have to change it for a moment

        $params = $refParams->getValue($connection);
        $params['driver'] = 'pdo_mysql';
        $params['dbname'] = $server->getDbname();
        $params['user'] = $server->getUsername();
        $params['password'] = $this->encoder->decode($server->getPassword(), $server->getSalt());
        $params['host'] = $server->getHost();

        $refParams->setAccessible('private');
        $refParams->setValue($connection, $params);

        $this->managerRegistry->resetManager('dist');
    }
}