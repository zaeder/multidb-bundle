<?php

namespace Zaeder\MultiDbBundle\Repository;

abstract class AbstractServerRepository extends ServiceEntityRepository
{
    abstract public function findByServerKey(string $serverKey);
}