<?php

namespace Grace\Bundle\ApiBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

use Grace\Bundle\ApiBundle\Security\Authentication\Token\ApiUserToken;


class ApiProvider implements AuthenticationProviderInterface
{

    private $userProvider;
    private $cacheDir;

    public function __construct(UserProviderInterface $userProvider, $cacheDir)
    {
        $this->userProvider = $userProvider;
        $this->cacheDir     = $cacheDir;
    }
    public function authenticate(TokenInterface $token)
    {
        /** @var $user AdvancedUserInterface */
        $user = $token->getUser();

        if (!$user->isEnabled()) {
            throw new AuthenticationException('The Grace Simple API authentication failed - User is not enabled.');
        }

        if (!$user->isAccountNonExpired()) {
            throw new AuthenticationException('The Grace Simple API authentication failed - Account is expired.');
        }

        if (!$user->isAccountNonLocked()) {
            throw new AuthenticationException('The Grace Simple API authentication failed - Account is locked.');
        }

        if (!$user->isCredentialsNonExpired()) {
            throw new AuthenticationException('The Grace Simple API authentication failed - Credentials is expired.');
        }

        return $token;
    }
    public function supports(TokenInterface $token)
    {
        return $token instanceof ApiUserToken;
    }
}