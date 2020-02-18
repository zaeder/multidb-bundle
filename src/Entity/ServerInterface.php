<?php

namespace Zaeder\MultiDbBundle\Entity;

interface ServerInterface
{
    public function getId();
    public function getKey();
    public function setKey(string $key);
    public function getHost();
    public function setHost(string $host);
    public function getPort();
    public function setPort(int $port);
    public function getDbname();
    public function setDbname(string $dbname);
    public function getUsername();
    public function setUsername(string $username);
    public function getPassword();
    public function setPassword(string $password);
    public function getSalt();
    public function setSalt(string $salt);
    public function getIsActive();
    public function setIsActive(bool $isActive);
}