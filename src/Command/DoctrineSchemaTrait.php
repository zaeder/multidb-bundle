<?php

namespace Zaeder\MultiDbBundle\Command;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zaeder\MultiDbBundle\Entity\ServerInterface;
use Zaeder\MultiDbBundle\Event\DatabaseEvents;
use Zaeder\MultiDbBundle\Event\MultiDbEvent;

trait DoctrineSchemaTrait
{
    private $managerRegistry;
    private $localEntityManagerName;
    private $localConnectionName;
    private $distEntityManagerName;
    private $serverEntityClass;
    private $eventDispatcher;

    public function init(
        ManagerRegistry $managerRegistry,
        string $localEntityManagerName,
        string $localConnectionName,
        string $distEntityManagerName,
        string $serverEntityClass,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->managerRegistry = $managerRegistry;
        $this->localEntityManagerName = $localEntityManagerName;
        $this->localConnectionName = $localConnectionName;
        $this->distEntityManagerName = $distEntityManagerName;
        $this->serverEntityClass = $serverEntityClass;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function getEntityManagerName(?string $serverKey = null)
    {
        $name = $this->localEntityManagerName;
        if (!empty($serverKey)) {
            $em = $this->managerRegistry->getManager($this->localEntityManagerName);
            $conn = $this->managerRegistry->getConnection($this->localConnectionName);
            if ($conn instanceof \Doctrine\DBAL\Connection) {
                $conn->connect();
            }
            $server = $em->getRepository($this->serverEntityClass)->findOneBy(['key' => $serverKey, 'isActive' => true]);
            if (!$server instanceof ServerInterface) {
                throw new \Exception('Server can not be found');
            }
            $name = $this->distEntityManagerName;
            $this->eventDispatcher->dispatch(new MultiDbEvent($server), DatabaseEvents::DIST_EM_CONFIG);
        }
        return $name;
    }
}