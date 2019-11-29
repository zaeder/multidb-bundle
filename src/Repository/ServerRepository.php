<?php

namespace Zaeder\MultiDbBundle\Repository;

use Zaeder\MultiDbBundle\Entity\ServerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method ServerInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServerInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServerInterface[]    findAll()
 * @method ServerInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServerRepository extends EntityRepository
{
    /**
     * Add dist server info in local database
     * @param string $key
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $dbname
     * @param string $recoveryEmail
     * @param bool|null $isDist
     * @param int|null $port
     * @return ServerInterface
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function add(string $key, string $host, string $username, string $password, string $dbname, string $recoveryEmail, ?bool $isDist = true, ?int $port = 3306)
    {
        $server = new $this->_entityName();
        $server
            ->setKey($key)
            ->setHost($host)
            ->setUsername($username)
            ->setPassword($password)
            ->setSalt('')
            ->setDbname($dbname)
            ->setPort($port)
            ->setRecoveryEmail($recoveryEmail)
            ->setIsDist($isDist)
            ->setIsActive(true);

        $this->_em->persist($server);
        $this->_em->flush();

        return $server;
    }
}