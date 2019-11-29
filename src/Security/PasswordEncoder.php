<?php

namespace Zaeder\MultiDbBundle\Security;

/**
 * Class PasswordEncoder
 * @package Zaeder\MultiDb\Security
 */
class PasswordEncoder
{
    /**
     *
     */
    const ENCRYPT_METHOD = 'AES-256-CBC';
    /**
     *
     */
    const ENCRYPT_ALGO = 'sha256';

    /**
     * @var string
     */
    private $passworkKey;
    /**
     * @var string|null
     */
    private $iv;

    /**
     * PasswordEncoder constructor.
     * @param string $passwordKey
     */
    public function __construct(string $passwordKey)
    {
        $this->passworkKey = hash(self::ENCRYPT_ALGO, $passwordKey);
    }

    /**
     * Encode password
     * @param string $password
     * @return string
     */
    public function encode(string $password) : string
    {
        $this->iv = substr(hash(self::ENCRYPT_ALGO, $this->getSecret()), 0, 16);

        $encrypted = openssl_encrypt(
            $password,
            self::ENCRYPT_METHOD,
            $this->passworkKey,
            0,
            $this->iv
        );

        return base64_encode($encrypted);
    }

    /**
     * Decode password
     * @param string $password
     * @param string $secret
     * @return string
     */
    public function decode(string $password, string $secret) : string
    {
        return openssl_decrypt(
            base64_decode($password),
            self::ENCRYPT_METHOD,
            $this->passworkKey,
            0,
            $secret
        );
    }

    /**
     * Check if is valid password
     * @param string $encodedPassword
     * @param string $secret
     * @param string $plainPassword
     * @return bool
     */
    public function isEquals(string $encodedPassword, string $secret, string $plainPassword) : bool
    {
        return ($plainPassword === $this->decode($encodedPassword, $secret));
    }

    /**
     * Get password salt
     * @return string|null
     */
    public function getIv() : ?string
    {
        return $this->iv;
    }

    /**
     * Get a random secret string
     * @return string
     */
    private function getSecret() : string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$*!;?&,:/.=+-%';
        return substr(str_shuffle($chars), 0, 20);
    }
}