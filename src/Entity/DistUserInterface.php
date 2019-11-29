<?php

namespace Zaeder\MultiDb\Entity;

interface DistUserInterface
{
    public function getId() : int;
    public function getUsername() : string;
    public function setUsername(string $username);
    public function getPassword(): string;
    public function setPassword(string $password);
    public function getSalt(): string;
    public function setSalt(string $salt);
    public function getEmail(): string;
    public function setEmail(string $email);
    public function getIsActive(): bool;
    public function setIsActive(bool $isActive);
}