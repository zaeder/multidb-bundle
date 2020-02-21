<?php

namespace Zaeder\MultiDbBundle\EventSubscriber;

use Doctrine\Common\Persistence\ManagerRegistry;
use Zaeder\MultiDbBundle\Entity\DistUserInterface;
use Zaeder\MultiDbBundle\Entity\LocalUserInterface;
use Zaeder\MultiDbBundle\Entity\ServerInterface;
use Zaeder\MultiDbBundle\Event\MultiDbEvent;
use Zaeder\MultiDbBundle\Event\SecurityEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zaeder\MultiDbBundle\Repository\AbstractDistUserRepository;
use Zaeder\MultiDbBundle\Repository\AbstractLocalUserRepository;

/**
 * Class LocalUserEventSubcriber
 * @package Zaeder\MultiDb\EventSubscriber
 */
class LocalUserEventSubcriber implements EventSubscriberInterface
{
    /**
     * @var AbstractLocalUserRepository
     */
    protected $localUserRepository;
    /**
     * @var AbstractDistUserRepository
     */
    protected $distUserRepository;

    /**
     * LocalUserEventSubcriber constructor.
     * @param AbstractLocalUserRepository $localUserRepository
     * @param AbstractDistUserRepository $distUserRepository
     */
    public function __construct(
        AbstractLocalUserRepository $localUserRepository,
        AbstractDistUserRepository $distUserRepository
    )
    {
        $this->localUserRepository = $localUserRepository;
        $this->distUserRepository= $distUserRepository;
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
     * @param MultiDbEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function import(MultiDbEvent $event)
    {
        $data = $event->getData();
        if ($data instanceof \stdClass && isset($data->user) && $data->user instanceof DistUserInterface && isset($data->server) && $data->server instanceof ServerInterface) {
            // First remove user if exists in local database
            $this->doRemove($data->user->getUsername(), $data->server);
            // Import dist user in local database
            $this->localUserRepository->add($data->user, $data->server);
        }
    }

    /**
     * Check if local user already exists in dist database
     * @param MultiDbEvent $event
     */
    public function validate(MultiDbEvent $event)
    {
        $data = $event->getData();
        if ($data instanceof LocalUserInterface) {
            $distUser = $this->distUserRepository->findByUsername($data->getUsername());
            // Remove local user if not exists in dist database
            if (!$distUser instanceof DistUserInterface) {
                $this->localUserRepository->remove($data);
            }
        }
    }

    /**
     * Remove local user if it is the good event data
     * @param MultiDbEvent $event
     */
    public function remove(MultiDbEvent $event)
    {
        $data = $event->getData();
        if ($data instanceof \stdClass && isset($data->username) && !empty($data->username) && isset($data->server) && $data->server instanceof Server) {
            $this->doRemove($data->username, $data->server);
        }
    }

    /**
     * Remove local user
     * @param string $username
     * @param ServerInterface $server
     */
    protected function doRemove(string $username, ServerInterface $server)
    {
        $user = $this->localUserRepository->findByUsernameAndServer($username, $server);
        if ($user instanceof LocalUserInterface) {
            $this->localUserRepository->remove($user);
        }
    }
}