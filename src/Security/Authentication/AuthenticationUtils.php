<?php

namespace Zaeder\MultiDb\Security\Authentication;

use Zaeder\MultiDb\Security\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils as SymfonyAuthenticationUtils;

/**
 * Class AuthenticationUtils
 * @package Zaeder\MultiDb\Security\Authentication
 */
class AuthenticationUtils extends SymfonyAuthenticationUtils
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * AuthenticationUtils constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        parent::__construct($requestStack);
    }

    /**
     * Get last server key for authentication
     * @return string
     */
    public function getLastServerkey()
    {
        $request = $this->getRequest();

        if ($request->attributes->has(Security::LAST_SERVERKEY)) {
            return $request->attributes->get(Security::LAST_SERVERKEY, '');
        }

        $session = $request->getSession();

        return null === $session ? '' : $session->get(Security::LAST_SERVERKEY, '');
    }

    /**
     * @throws \LogicException
     */
    private function getRequest(): Request
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new \LogicException('Request should exist so it can be processed for error.');
        }

        return $request;
    }
}