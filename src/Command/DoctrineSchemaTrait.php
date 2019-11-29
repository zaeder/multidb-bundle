<?php

namespace Zaeder\MultiDb\Command;

use Zaeder\MultiDb\Entity\Local\Server;
use Zaeder\MultiDb\Event\DatabaseEvents;
use Zaeder\MultiDb\Event\Event;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

trait DoctrineSchemaTrait
{
    private $managerRegistry;
    private $eventDispatcher;

    public function init(ManagerRegistry $managerRegistry, EventDispatcherInterface $eventDispatcher)
    {
        $this->managerRegistry = $managerRegistry;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function getEntityManagerName(?string $serverKey = null)
    {
        $name = 'local';
        if (!empty($serverKey)) {
            $em = $this->managerRegistry->getManager($name);
            $conn = $this->managerRegistry->getConnection($name);
            if ($conn instanceof \Doctrine\DBAL\Connection) {
                $conn->connect();
            }
            $server = $em->getRepository(Server::class)->findOneBy(['key' => $serverKey, 'isActive' => true]);
            if (!$server instanceof Server) {
                throw new \Exception('Server can not be found');
            }
            $name = 'dist';
            $this->eventDispatcher->dispatch(new Event($server), DatabaseEvents::DIST_EM_CONFIG);
        }
        return $name;
    }
}