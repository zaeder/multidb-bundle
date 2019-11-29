<?php

namespace Zaeder\MultiDbBundle\Repository;

use Zaeder\MultiDbBundle\Entity\DistUserInterface;
use Zaeder\MultiDbBundle\Entity\LocalUserInterface;
use Zaeder\MultiDbBundle\Entity\ServerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @method LocalUserInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method LocalUserInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method LocalUserInterface[]    findAll()
 * @method LocalUserInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocalUserRepository extends EntityRepository implements UserLoaderInterface
{

    /**
     * Load user by username or email
     * @param string $usernameOrEmail
     * @return mixed|null|\Symfony\Component\Security\Core\User\UserInterface
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loadUserByUsername($usernameOrEmail)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :query OR u.email = :query')
            ->setParameter('query', $usernameOrEmail)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Add Dist user in local database for authentication
     * @param DistUserInterface $distUser
     * @param ServerInterface $server
     * @return LocalUserInterface
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function add(DistUserInterface $distUser, ServerInterface $server)
    {
        $user = new $this->_entityName();
        $user
            ->setUsername($distUser->getUsername())
            ->setPassword($distUser->getPassword())
            ->setSalt($distUser->getSalt())
            ->setEmail($distUser->getEmail())
            ->setServer($server);

        $this->_em->persist($user);
        $this->_em->flush();

        return $user;
    }
}