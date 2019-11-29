<?php

namespace Zaeder\MultiDbBundle\Repository;

use Zaeder\MultiDbBundle\Entity\DistUserInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method DistUserInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method DistUserInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method DistUserInterface[]    findAll()
 * @method DistUserInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DistUserRepository extends EntityRepository
{
    /**
     * Add user in dist database
     * @param string $username
     * @param string $password
     * @param string $email
     * @param bool|null $isActive
     * @return DistUserInterface
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function add(string $username, string $password, string $email, ?bool $isActive = false)
    {
        $user = new $this->_entityName();
        $user
            ->setUsername($username)
            ->setPassword($password)
            ->setSalt('')
            ->setEmail($email)
            ->setIsActive($isActive);

        $this->_em->persist($user);
        $this->_em->flush();

        return $user;
    }
}