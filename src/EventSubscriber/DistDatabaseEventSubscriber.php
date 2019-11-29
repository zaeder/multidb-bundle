<?php

namespace Zaeder\MultiDbBundle\EventSubscriber;


use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Zaeder\MultiDbBundle\Entity\LocalUserInterface;
use Zaeder\MultiDbBundle\Entity\ServerInterface;
use Zaeder\MultiDbBundle\Event\DatabaseEvents;
use Zaeder\MultiDbBundle\Event\Event;
use Zaeder\MultiDbBundle\Event\SecurityEvents;
use Zaeder\MultiDbBundle\Security\PasswordEncoder;
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
    protected $registry;
    /**
     * @var string
     */
    protected $localEntityManagerName;
    /**
     * @var string
     */
    protected $distConnectionName;
    /**
     * @var string
     */
    protected $distEntityManagerName;
    /**
     * @var string
     */
    protected $localUserEntityClass;
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
     * @param ManagerRegistry $registry
     * @param string $localEntityManagerName
     * @param string $distConnectionName
     * @param string $distEntityManagerName
     * @param string $localUserEntityClass
     * @param PasswordEncoder $encoder
     * @param TokenStorageInterface $tokenStorage
     * @param EventDispatcherInterface $eventDispatcher
     * @param SessionInterface $session
     * @param RouterInterface $router
     */
    public function __construct(
        ManagerRegistry $registry,
        string $localEntityManagerName,
        string $distConnectionName,
        string $distEntityManagerName,
        string $localUserEntityClass,
        PasswordEncoder $encoder,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        SessionInterface $session,
        RouterInterface $router
    )
    {
        $this->registry = $registry;
        $this->localEntityManagerName = $localEntityManagerName;
        $this->distConnectionName = $distConnectionName;
        $this->distEntityManagerName = $distEntityManagerName;
        $this->localUserEntityClass = $localUserEntityClass;
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
        if (!$server instanceof ServerInterface) {
            throw new \Exception('server not valid for configuration');
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
            if ($user instanceof LocalUserInterface) {
                $username = $user->getUsername();
                $server = $user->getServer();
                // If user is attached to a server
                if ($server instanceof ServerInterface) {
                    // Configure dist connection
                    $this->doConfigure($server);
                    // Check user validity
                    $this->eventDispatcher->dispatch(new Event($user), SecurityEvents::SECURITY_VALIDATE_DIST_USER);
                    // Check if current user exists after check up; if no kill session and redirect to login page
                    $user = $this->registry->getManager($this->localEntityManagerName)->getRepository($this->localUserEntityClass)->findOneBy(['username' => $username, 'server' => $server]);
                    if (!$user instanceof LocalUserInterface) {
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
     * @param ServerInterface $server
     */
    private function doConfigure(ServerInterface $server)
    {
        //establish the connection
        $connection = $this->registry->getConnection($this->distConnectionName);

        $this->registry->getManager($this->distEntityManagerName)->flush();

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

        $this->registry->resetManager($this->distEntityManagerName);
    }
}