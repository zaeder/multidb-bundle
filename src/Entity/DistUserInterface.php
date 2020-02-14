<?php

namespace Zaeder\MultiDbBundle\Entity;

interface DistUserInterface
{
    public function getId();
    public function getUsername();
    public function setUsername(string $username);
    public function getPassword();
    public function setPassword(string $password);
    public function getSalt();
    public function setSalt(string $salt);
    public function getEmail();
    public function setEmail(string $email);
    public function getIsActive();
    public function setIsActive(bool $isActive);
}