<?php

namespace Zaeder\MultiDbBundle\Security;

use Symfony\Component\Security\Core\Security as SymfonySecurity;

class Security extends SymfonySecurity
{
    const LAST_SERVERKEY = '_security.last_serverkey';
}