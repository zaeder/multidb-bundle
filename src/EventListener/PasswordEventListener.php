<?php

namespace Zaeder\MultiDbBundle\EventListener;

use Zaeder\MultiDbBundle\Security\PasswordEncoder;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * Class PasswordEventListener
 * @package Zaeder\MultiDb\EventListener
 */
class PasswordEventListener
{
    /**
     * @var PasswordEncoder
     */
    private $encoder;

    /**
     * @var array
     */
    private $entitiesEnabled;

    /**
     * PasswordEventListener constructor.
     * @param PasswordEncoder $encoder
     */
    public function __construct(PasswordEncoder $encoder, array $entitiesEnabled)
    {
        $this->encoder = $encoder;
        $this->entitiesEnabled = $entitiesEnabled;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->encodePasswords($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
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

        if ($this->isEnabledEntity($entity)) {
            $entity->setPassword($this->encoder->encode($entity->getPassword()));
            $entity->setSalt($this->encoder->getIv());
        }
    }

    /**
     * Verify if the current entity is enabled to encode passwords
     * @param $entity
     * @return bool
     */
    private function isEnabledEntity($entity) : bool
    {
        foreach ($this->entitiesEnabled as $class) {
            if ($entity instanceof $class) {
                return true;
            }
        }
        return false;
    }
}