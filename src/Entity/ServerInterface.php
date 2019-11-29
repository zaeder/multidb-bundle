<?php

namespace Zaeder\MultiDb\Entity;

interface ServerInterface
{
    public function getId(): int;
    public function getKey(): string;
    public function setKey(string $key);
    public function getHost(): string;
    public function setHost(string $host);
    public function getPort(): int;
    public function setPort(int $port);
    public function getDbname(): string;
    public function setDbname(string $dbname);
    public function getUsername(): string;
    public function setUsername(string $username);
    public function getPassword(): string;
    public function setPassword(string $password);
    public function getSalt(): string;
    public function setSalt(string $salt);
    public function getRecoveryEmail(): string;
    public function setRecoveryEmail(string $recoveryEmail);
    public function getIsDist(): bool;
    public function setIsDist(bool $isDist);
    public function getIsActive(): bool;
    public function setIsActive(bool $isActive);
}