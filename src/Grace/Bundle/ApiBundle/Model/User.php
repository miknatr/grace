<?php

namespace Grace\Bundle\ApiBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\Role\Role;

use Grace\Bundle\ApiBundle\Security\Core\ApiUserInterface;

/**
 * Class User
 * @package Grace\Bundle\ApiBundle\Model
 */
abstract class User extends ResourceAbstract implements ApiUserInterface, EquatableInterface
{
    abstract public function getRole();

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
        $roles = $this->getOrm()->getRoleHierarchy()->getReachableRoles(array($userRole));
        return in_array(new Role($role), $roles);
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
        $usernameGetter = 'get' . ucfirst(static::USERNAME_FIELD);
        return $this->$usernameGetter();
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
    public function refreshApiTokenAndCommitOnLogin()
    {
        $this->setToken(md5(microtime() . rand()));
        $this->setTokenCreatedAt(dt());
        $this->setTokenIp(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
        $this->getOrm()->commit();
    }
    public function isApiTokenNotExpired($checkToken, $checkIp)
    {
        $isSameIp = true;
        if ($checkIp) {
            $currentIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : static::SPECIAL_TEST_IP;
            $isSameIp = $this->getTokenIp() == $currentIp;
        }

        $isTokenNotExpired = true;
        if ($checkToken) {
            $isTokenNotExpired = (time() - dt2ts($this->getTokenCreatedAt()) <= 3600 * 12);
        }

        return $isSameIp && $isTokenNotExpired;
    }

    //очистка кэша
    //STOPPER эти кэши чистить по эвентам бы теперь
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
        $this->getOrm()->getCache()->remove(self::CACHE_PREFIX_TOKEN . $this->getToken());
    }
}
