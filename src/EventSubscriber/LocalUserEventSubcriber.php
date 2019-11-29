<?php

namespace Zaeder\MultiDb\EventSubscriber;

use Zaeder\MultiDb\Entity\Dist\DistUser as DistUser;
use Zaeder\MultiDb\Entity\Local\Server;
use Zaeder\MultiDb\Entity\Local\User;
use Zaeder\MultiDb\Event\Event;
use Zaeder\MultiDb\Event\SecurityEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class LocalUserEventSubcriber
 * @package Zaeder\MultiDb\EventSubscriber
 */
class LocalUserEventSubcriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $localEntityManager;
    /**
     * @var EntityManagerInterface
     */
    private $distEntityManager;

    /**
     * LocalUserEventSubcriber constructor.
     * @param EntityManagerInterface $localEntityManager
     * @param EntityManagerInterface $distEntityManager
     */
    public function __construct(EntityManagerInterface $localEntityManager, EntityManagerInterface $distEntityManager)
    {
        $this->localEntityManager = $localEntityManager;
        $this->distEntityManager = $distEntityManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::SECURITY_IMPORT_DIST_USER => ['import'],
            SecurityEvents::SECURITY_VALIDATE_DIST_USER => ['validate'],
            SecurityEvents::SECURITY_REMOVE_DIST_USER => ['remove'],
        ];
    }

    /**
     * Import dist user in local database
     * @param Event $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function import(Event $event)
    {
        $data = $event->getData();
        if ($data instanceof \stdClass && isset($data->user) && $data->user instanceof DistUser && isset($data->server) && $data->server instanceof Server) {
            // First remove user if exists in local database
            $this->doRemove($data->user->getUsername(), $data->server);
            // Import dist user in local database
            $this->localEntityManager->getRepository(User::class)->add($data->user, $data->server);
        }
    }

    /**
     * Check if local user already exists in dist database
     * @param Event $event
     */
    public function validate(Event $event)
    {
        $data = $event->getData();
        if ($data instanceof User) {
            $distUser = $this->distEntityManager->getRepository(DistUser::class)->findOneBy(['username' => $data->getUsername(), 'isActive' => true]);
            // Remove local user if not exists in dist database
            if (!$distUser instanceof DistUser) {
                $this->localEntityManager->remove($data);
                $this->localEntityManager->flush();
            }
        }
    }

    /**
     * Remove local user if it is the good event data
     * @param Event $event
     */
    public function remove(Event $event)
    {
        $data = $event->getData();
        if ($data instanceof \stdClass && isset($data->username) && !empty($data->username) && isset($data->server) && $data->server instanceof Server) {
            $this->doRemove($data->username, $data->server);
        }
    }

    /**
     * Remove local user
     * @param string $username
     * @param Server $server
     */
    private function doRemove(string $username, Server $server)
    {
        $user = $this->localEntityManager->getRepository(User::class)->findOneBy(['username' => $username, 'server' => $server]);
        if ($user instanceof User) {
            $this->localEntityManager->remove($user);
            $this->localEntityManager->flush();
        }
    }
}