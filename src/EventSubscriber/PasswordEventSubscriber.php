<?php

namespace Zaeder\MultiDbBundle\EventSubscriber;

use Zaeder\MultiDbBundle\Entity\DistUserInterface;
use Zaeder\MultiDbBundle\Entity\ServerInterface;
use Zaeder\MultiDbBundle\Security\PasswordEncoder;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PasswordEventSubscriber
 * @package Zaeder\MultiDb\EventSubscriber
 */
class PasswordEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var PasswordEncoder
     */
    private $encoder;

    /**
     * PasswordEventSubscriber constructor.
     * @param PasswordEncoder $encoder
     */
    public function __construct(PasswordEncoder $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::prePersist => ['onPrePersist'],
            Events::preUpdate => ['onPreUpdate'],
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function onPrePersist(LifecycleEventArgs $args)
    {
        $this->encodePasswords($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function onPreUpdate(LifecycleEventArgs $args)
    {
        $this->encodePasswords($args);
    }

    /**
     * Encode dist user's password et server's password
     * @param LifecycleEventArgs $args
     */
    private function encodePasswords(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof DistUserInterface || $entity instanceof ServerInterface) {
            $entity->setPassword($this->encoder->encode($entity->getPassword()));
            $entity->setSalt($this->encoder->getIv());
        }
    }
}