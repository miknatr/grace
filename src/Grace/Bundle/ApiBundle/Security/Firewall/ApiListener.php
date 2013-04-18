<?php

namespace Grace\Bundle\ApiBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Grace\ORM\ORMManagerAbstract;

use Grace\Bundle\ApiBundle\Security\Authentication\Token\ApiUserToken;

use Grace\Bundle\ApiBundle\Security\Core\ApiUserInterface;
use Grace\Bundle\ApiBundle\Finder\UserApiFinderInterface;

class ApiListener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;
    private $checkToken;
    private $checkIp;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, $is_check_ip, $is_check_token)
    {
        $this->securityContext       = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->checkToken            = $is_check_token;
        $this->checkIp               = $is_check_ip;
    }

    /** @var UserApiFinderInterface[] */
    protected $finders = array();
    public function addUserFinder(UserApiFinderInterface $finder)
    {
        $this->finders[] = $finder;
    }

    public function handle(GetResponseEvent $event)
    {
        if ($this->securityContext->getToken() !== null) {
            throw new AuthenticationException('The Grace Simple API authentication failed - Token is already set.');
        }

        $tokenKey = $this->getTokenKeyFromRequest($event->getRequest());

        if ($tokenKey == '') {
            throw new AuthenticationException('The Grace Simple API authentication failed - Token key is not provided.');
        }



        /** @var ApiUserInterface|bool $user */
        $user = $this->getUserByToken($tokenKey);

        $token = new ApiUserToken($user->getRoles());
        $token->setUser($user);

        $returnValue = $this->authenticationManager->authenticate($token);

        if ($returnValue instanceof TokenInterface) {
            $this->securityContext->setToken($returnValue);
        } else {
            if ($returnValue instanceof Response) {
                $event->setResponse($returnValue);
            }
        }
    }
    protected function getTokenKeyFromRequest(Request $request)
    {
        if ($request->get('api_key') !== null) {
            $tokenKey = $request->get('api_key');

            return $tokenKey;
        } elseif ($request->headers->has('x-api-key')) {
            $tokenKey = $request->headers->get('x-api-key');

            return $tokenKey;
        } else {
            throw new AuthenticationException('The Grace Simple API authentication failed - Api key is not provided.');
        }
    }
    protected function getUserByToken($key)
    {
        foreach ($this->finders as $finder) {
            $user = $finder->getByToken($key);

            if (!$user) {
                continue;
            }

            $this->validateUser($user);

            return $user;
        }

        throw new AuthenticationException('The Grace Simple API authentication failed - User not found.');
    }
    protected function validateUser(ApiUserInterface $user)
    {
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

        if (!$user->isApiTokenNotExpired($this->checkToken, $this->checkIp)) {
            throw new AuthenticationException('The Grace Simple API authentication failed - Api token is expired.');
        }
    }
}
