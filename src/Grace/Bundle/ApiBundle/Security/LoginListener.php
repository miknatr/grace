<?php

namespace Grace\Bundle\ApiBundle\Security;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\SecurityContext;

use Grace\Bundle\ApiBundle\Security\Core\ApiUserInterface;

class LoginListener
{
    /** @var \Symfony\Component\Security\Core\SecurityContext */
    private $context;
    public function __construct(SecurityContext $context)
    {
        $this->context = $context;
    }
    public function onSecurityInteractiveLogin(Event $event)
    {
        $user = $this->context->getToken()->getUser();
        if ($user instanceof ApiUserInterface) {
            $user->refreshApiTokenAndCommitOnLogin();
        }
    }
}