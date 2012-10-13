<?php
namespace Grace\Bundle\CommonBundle\Security\Core\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Grace\ORM\ManagerAbstract;
use Grace\ORM\ExceptionNoResult;
use Grace\ORM\ClassNameProviderInterface;

class UserProvider implements UserProviderInterface
{
    private $baseModelName;
    /** @var UserFinderInterface */
    private $userFinder;
    /** @var ClassNameProviderInterface */
    private $classNameProvider;

    public function __construct(UserFinderInterface $userFinder, $baseModelName, ClassNameProviderInterface $classNameProvider)
    {
        $this->userFinder        = $userFinder;
        $this->baseModelName     = $baseModelName;
        $this->classNameProvider = $classNameProvider;
    }
    public function loadUserByUsername($username)
    {
        try {
            $user = $this->userFinder->getByUsername($username);

            if (!$user) {
                throw new UsernameNotFoundException(sprintf($this->baseModelName . ' with phone "%s" does not exist.', $username));
            }

            return $user;
        } catch (ExceptionNoResult $e) {
            throw new UsernameNotFoundException(sprintf($this->baseModelName . ' with phone "%s" does not exist.', $username));
        }
    }
    public function refreshUser(UserInterface $user)
    {
        if(!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException('Unsupported user type');
        }
        return $this->loadUserByUsername($user->getUsername());
    }
    public function supportsClass($class)
    {
        return '\\' . $class === $this->classNameProvider->getModelClass($this->baseModelName);
    }
}