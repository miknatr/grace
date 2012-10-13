<?php

namespace Grace\Bundle\ApiBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class ApiUserToken extends AbstractToken
{
    public function getCredentials()
    {
        return '';
    }
}