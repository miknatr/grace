<?php

namespace Grace\Bundle\ApiBundle\Security\Core;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

interface ApiUserInterface extends AdvancedUserInterface
{
    public function refreshApiTokenOnLogin();
    public function isApiTokenNotExpired($checkToken, $checkIp);
}