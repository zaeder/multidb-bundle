<?php

namespace Zaeder\MultiDbBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface LocalUserInterface extends UserInterface, \Serializable
{
    public function getId();
    public function setUsername(string $username);
    public function setPassword(string $password);
    public function setSalt(string $salt);
    public function getEmail();
    public function setEmail(string $email);
    public function getServer();
    public function setServer(?ServerInterface $server);
}