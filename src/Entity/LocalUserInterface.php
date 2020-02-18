<?php

namespace Zaeder\MultiDbBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface LocalUserInterface extends UserInterface, \Serializable
{
    public function getServer();
    public function setServer(?ServerInterface $server);
}