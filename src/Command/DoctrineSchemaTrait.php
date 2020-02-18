<?php

namespace Zaeder\MultiDbBundle\Command;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zaeder\MultiDbBundle\Entity\ServerInterface;
use Zaeder\MultiDbBundle\Event\DatabaseEvents;
use Zaeder\MultiDbBundle\Event\MultiDbEvent;
use Zaeder\MultiDbBundle\Repository\AbstractServerRepository;

trait DoctrineSchemaTrait
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;
    /**
     * @var string
     */
    protected $localEntityManagerName;
    /**
     * @var string
     */
    protected $localConnectionName;
    /**
     * @var string
     */
    protected $distEntityManagerName;
    /**
     * @var AbstractServerRepository
     */
    protected $serverRepository;
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function init(
        ManagerRegistry $managerRegistry,
        string $localEntityManagerName,
        string $localConnectionName,
        string $distEntityManagerName,
        AbstractServerRepository $serverRepository,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->managerRegistry = $managerRegistry;
        $this->localEntityManagerName = $localEntityManagerName;
        $this->localConnectionName = $localConnectionName;
        $this->distEntityManagerName = $distEntityManagerName;
        $this->serverRepository = $serverRepository;
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
            $server = $this->serverRepository->findByServerKey($serverKey);
            if (!$server instanceof ServerInterface) {
                throw new \Exception('Server can not be found');
            }
            $name = $this->distEntityManagerName;
            $this->eventDispatcher->dispatch(new MultiDbEvent($server), DatabaseEvents::DIST_EM_CONFIG);
        }
        return $name;
    }
}