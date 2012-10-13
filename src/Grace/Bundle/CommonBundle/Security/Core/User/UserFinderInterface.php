<?php
namespace Grace\Bundle\CommonBundle\Security\Core\User;

interface UserFinderInterface
{
    public function getByUsername($username);
}