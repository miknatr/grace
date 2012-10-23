<?php

namespace Grace\Bundle\ApiBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\Role\Role;

use Grace\Bundle\ApiBundle\Security\Core\ApiUserInterface;

abstract class User extends ResourceAbstract implements ApiUserInterface, EquatableInterface
{
    protected function initCreationFieldsOnCreateByUser(User $user, array $fields)
    {
        $this->setCreatedAt(dt());
    }

    /** @var array ROLE => ROLE_TITLE. Если ROLE_TITLE начинается с @, она не будет отдаваться через getRoleList */
    //как то бы перегнать под roleHierarchy
    public static $roles = array();
    static public function getRoleList()
    {
        $roles = static::$roles;
        foreach($roles as $role => $roleRA) {
            if($roleRA[0] == '@') {
                unset($roles[$role]);
            }
        }
        return $roles;
    }
    public function isRole($role)
    {
        $userRoleString = $this->getRole();
        if ($role == $userRoleString) {
            return true;
        }
        $userRole  = new Role($userRoleString);
        $roles = $this->getContainer()->getRoleHierarchy()->getReachableRoles(array($userRole));
        return in_array(new Role($role), $roles);
    }

    public function isType($type)
    {
        return $this->getType() == $type;
    }
    public function getType()
    {
        return strtolower($this->getClassNameProvider()->getBaseClass(get_class($this)));
    }

    //хак для генератора, не помню почему, но генерация отваливается
    //вообще по хорошему здесь бы тащить из секюрити механизм соления
    public function setPassword($password)
    {
        $this->fields['password'] = md5($password);

        $this->markAsChanged();

        return $this;
    }


    //AdvancedUserInterface
    public function getPassword()
    {
        //генератор считает что метод определено, поскольку он есть в интерфейсе и не переопределяет его
        return $this->fields['password'];
    }
    public function getRoles()
    {
        return array($this->getRole());
    }
    public function getSalt()
    {
        return '';
    }
    const USERNAME_FIELD = 'phone';
    public function getUsername()
    {
        $baseClass = $this->getClassNameProvider()->getBaseClass(get_class($this));
        $finderClass = $this->getClassNameProvider()->getFinderClass($baseClass);
        $usernameGetter = 'get' . ucfirst(static::USERNAME_FIELD);
        return constant($finderClass . '::USERNAME_PREFIX') . $this->$usernameGetter();
    }
    public function eraseCredentials()
    {
    }
    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->getPassword() !== $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        if ($this->getUsername() !== $user->getUsername()) {
            return false;
        }

        return true;
    }
    public function isAccountNonExpired()
    {
        return true;
    }
    public function isAccountNonLocked()
    {
        return !$this->getIsBlocked();
    }
    public function isCredentialsNonExpired()
    {
        return true;
    }
    public function isEnabled()
    {
        return true;
    }


    //ApiUserInterface
    const SPECIAL_TEST_IP = 'special_test_ip';
    public function refreshApiTokenOnLogin()
    {
        $this->setTokenCreatedAt(dt());
        $this->setTokenIp(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');


        //TODO если не происходит никаких действий, не происходит и комита, поэтому для setLastVisitAt нужен другой механизм апдейста (лисенеры реквеста возможно)
        $onlineTime = 10 * 60;

        if (dt2ts($this->getLastVisitAt()) + $onlineTime < time()) {
            $this->setLastVisitAt(dt());
            $this->setLastIp(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
        }
    }

    public function isApiTokenNotExpired($checkToken, $checkIp)
    {
        $isSameIp = true;
        $isTokenNotExpired = true;
        if ($isSameIp) {
            $currentIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : static::SPECIAL_TEST_IP;
            $isSameIp = $this->getTokenIp() == $currentIp;
        }

        if ($checkToken) {
            $isTokenNotExpired = (time() - dt2ts($this->getTokenCreatedAt()) <= 3600 * 12);
        }
        return $isSameIp && $isTokenNotExpired;
    }
    static public function trimUsername()
    {

    }
    const CACHE_PREFIX_USERNAME = 'user_by_username_';
    const CACHE_PREFIX_TOKEN = 'user_by_token_';
    public function onCommitChange()
    {
        $this->cleanRelatedCaches();
    }
    public function onCommitDelete()
    {
        $this->cleanRelatedCaches();
    }
    private function cleanRelatedCaches()
    {
        $this->getContainer()->getCache()->remove(self::CACHE_PREFIX_TOKEN . $this->getToken());
        $this->getContainer()->getCache()->remove(self::CACHE_PREFIX_USERNAME . $this->getUsername());
    }
}