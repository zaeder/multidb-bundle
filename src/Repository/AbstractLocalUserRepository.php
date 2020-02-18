<?php

namespace Zaeder\MultiDbBundle\Repository;

use Zaeder\MultiDbBundle\Entity\DistUserInterface;
use Zaeder\MultiDbBundle\Entity\ServerInterface;

abstract class AbstractLocalUserRepository extends ServiceEntityRepository
{
    abstract public function findByUsername(string $username);
    abstract public function findByUsernameAndServer(string $username, $server);
    abstract public function add(DistUserInterface $distUser, ?ServerInterface $server);
}