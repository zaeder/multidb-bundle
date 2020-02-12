<?php

namespace Zaeder\MultiDbBundle\Entity;

interface ServerInterface
{
    const DRIVER_PDO_CUBRID = 'pdo_cubrid';
    const DRIVER_PDO_DBLIB = 'pdo_dblib';
    const DRIVER_PDO_FIREBIRD = 'pdo_firebird';
    const DRIVER_PDO_IBM = 'pdo_ibm';
    const DRIVER_PDO_INFORMIX = 'pdo_informix';
    const DRIVER_PDO_MYSQL = 'pdo_mysql';
    const DRIVER_PDO_OCI = 'pdo_oci';
    const DRIVER_PDO_ODBC = 'pdo_odbc';
    const DRIVER_PDO_PGSQL = 'pdo_pgsql';
    const DRIVER_PDO_SQLITE = 'pdo_sqlite';
    const DRIVER_PDO_SQLSRV = 'pdo_sqlsrv';
    const DRIVER_PDO_4D = 'pdo_4d';
    const DRIVERS_PDO = [
        'CUBRID' => self::DRIVER_PDO_CUBRID,
        'DBLIB' => self::DRIVER_PDO_DBLIB,
        'FIREBIRD' => self::DRIVER_PDO_FIREBIRD,
        'IBM' => self::DRIVER_PDO_IBM,
        'INFORMIX' => self::DRIVER_PDO_INFORMIX,
        'MYSQL' => self::DRIVER_PDO_MYSQL,
        'OCI' => self::DRIVER_PDO_OCI,
        'ODBC' => self::DRIVER_PDO_ODBC,
        'PGSQL' => self::DRIVER_PDO_PGSQL,
        'SQLITE' => self::DRIVER_PDO_SQLITE,
        'SQLSRV' => self::DRIVER_PDO_SQLSRV,
        '4D' => self::DRIVER_PDO_4D,
    ];

    public function getId(): int;
    public function getKey(): string;
    public function setKey(string $key);
    public function getDriver(): string;
    public function setDriver(string $driver);
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