<?php

namespace Zaeder\MultiDbBundle\Repository;

abstract class AbstractDistUserRepository extends ServiceEntityRepository
{
    abstract public function findByUsername(string $username);
}